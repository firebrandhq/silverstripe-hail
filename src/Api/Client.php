<?php

namespace Firebrand\Hail\Api;

use Firebrand\Hail\Models\Article;
use Firebrand\Hail\Models\Image;
use Firebrand\Hail\Models\Organisation;
use Firebrand\Hail\Models\Video;
use GuzzleHttp\Client as HTTPClient;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * API client for the Hail Api. It uses Guzzle HTTP Client to communicate with
 * with Hail.
 * *
 * An Client ID and a Client Secret must be provided in your .env file for
 * HailAPI
 *
 * Errors are shown in the CMS via session variables and logged to file
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 */
class Client
{
    use Configurable;

    private $client_id;
    private $client_secret;
    private $access_token;
    private $access_token_expire;
    private $refresh_token;
    private $user_id;
    private $orgs_ids;
    private $scopes = "user.basic content.read";

    public function __construct()
    {
        //Get Client ID and Secret from env file
        $this->client_id = Environment::getEnv('HAIL_CLIENT_ID');;
        $this->client_secret = Environment::getEnv('HAIL_CLIENT_SECRET');;

        //Get api settings from site config
        $config = SiteConfig::current_site_config();
        $this->access_token = $config->HailAccessToken;
        $this->access_token_expire = $config->HailAccessTokenExpire;
        $this->refresh_token = $config->HailRefreshToken;
        $this->user_id = $config->HailUserID;
        $this->orgs_ids = $config->HailOrgsIDs;
    }

    /**
     * Send a GET request to the Hail API for a specific URI and returns
     * the results. Extra parameters can be passed with the $body variable.
     *
     * @param string $uri Resource to get
     * @param array $params Query params of the request to send to the Hail API.
     * @param boolean $throw_errors IF false, will not throw errors on HTTP Exception but will add a session message instead
     *
     * @return array Reply from Hail API
     * @throws
     */
    public function get($uri, $params = null, $throw_errors = false)
    {
        $options = [];
        $http = $this->getHTTPClient();
        $options['headers'] = [
            "Authorization" => "Bearer " . $this->getAccessToken()
        ];

        //Pass the body if needed
        if ($params) {
            $options['query'] = $params;
        }

        // Request
        try {
            $response = $http->request('GET', $uri, $options);
            $responseBody = $response->getBody();
            $responseArr = json_decode($responseBody, true);
        } catch (\Exception  $exception) {
            if ($throw_errors === true) {
                throw $exception;
            } else {
                $this->handleException($exception);
                //Send empty array so the app doesn't crash
                $responseArr = [];
            }
        }

        return $responseArr;
    }

    /**
     * Get one Hail object from the API
     *
     * @param mixed $hail_object Object to retrieve
     *
     * @return array Reply from Hail
     * @throws
     */
    public function getOne($hail_object)
    {
        $uri = $hail_object::$object_endpoint . '/' . $hail_object->HailID;

        return $this->get($uri);
    }

    /**
     * Fetch OAuth Access token from the HAil API
     *
     * @param string $redirect_code
     *
     * @throws
     */
    public function fetchAccessToken($redirect_code)
    {
        $http = $this->getHTTPClient();
        $post_data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'code' => $redirect_code,
            'redirect_uri' => $this->getRedirectURL(),
        ];
        // Request access token
        try {
            $response = $http->request('POST', 'oauth/access_token', [
                'form_params' => $post_data
            ]);

            $responseBody = $response->getBody();
            $responseArr = json_decode($responseBody, true);

            //Set new data into the config and update the current instance
            $this->setAccessToken($responseArr['access_token']);
            $this->setAccessTokenExpire($responseArr['expires_in']);
            $this->setRefreshToken($responseArr['refresh_token']);
        } catch (\Exception  $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * Create a new Guzzle HTTP Client
     *
     * return HTTPClient
     */
    public function getHTTPClient()
    {
        return new HTTPClient(["base_uri" => $this->getApiBaseURL()]);
    }

    /**
     * Get Hail API base URL from yml config
     *
     * return string
     */
    public function getApiBaseURL()
    {
        return Config::inst()->get(self::class, 'BaseApiUrl');
    }

    /**
     * Get Redirect URL Hail OAuth uses after authorization
     *
     * return string
     */
    public function getRedirectURL()
    {
        return Director::absoluteURL('HailCallbackController', true);
    }

    /**
     * Set Hail API OAuth access token expiry time in current SiteConfig
     *
     * @param string $access_token_expire
     * @throws
     */
    public function setAccessTokenExpire($access_token_expire)
    {
        //Store expiry date as unix timestamp (now + expires in)
        $access_token_expire = time() + $access_token_expire;
        $this->access_token_expire = $access_token_expire;
        $config = SiteConfig::current_site_config();
        $config->HailAccessTokenExpire = $access_token_expire;
        $config->write();
    }

    /**
     * Set Hail API OAuth refresh token in current SiteConfig
     * @param string $refresh_token
     * @throws
     */
    public function setRefreshToken($refresh_token)
    {
        $config = SiteConfig::current_site_config();
        $config->HailRefreshToken = $refresh_token;
        $this->refresh_token = $refresh_token;
        $config->write();
    }

    /**
     * Silently handle Hail API HTTP Exception to avoid CMS crashes
     * Stores error message in a session variable for CMS display
     *
     * @param \Exception $exception
     */
    public function handleException($exception)
    {
        //Log the error
        Injector::inst()->get(LoggerInterface::class)->debug($exception->getMessage());
        $request = Injector::inst()->get(HTTPRequest::class);
        $request->getSession()->set('notice', true);
        $request->getSession()->set('noticeType', 'bad');
        $message = $exception->getMessage();
        if ($exception->hasResponse()) {
            $response = json_decode($exception->getResponse()->getBody(), true);
            if (isset($response['error']) && isset($response['error']['message'])) {
                $message = $response['error']['message'];
            }
        }
        $request->getSession()->set('noticeText', $message);
    }

    /**
     * Build Hail API Authorization URL
     *
     * @return string
     */
    public function getAuthorizationURL()
    {
        $url = Config::inst()->get(self::class, 'AuthorizationUrl');
        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $this->getRedirectURL(),
            'response_type' => "code",
            'scope' => $this->scopes,
        ];

        return $url . "?" . http_build_query($params);
    }

    /**
     * Get Hail User ID from the API and set it in the current SiteConfig
     *
     * @throws
     */
    public function setUserID()
    {
        $response = $this->get("me");
        $config = SiteConfig::current_site_config();
        $config->HailUserID = $response['id'];
        $this->user_id = $response['id'];
        $config->write();
    }

    /**
     * Get current Hail API OAuth Access token
     * Will refresh the token from the API if it has expired
     *
     * @return string
     */
    public function getAccessToken()
    {
        //Check if AccessToken needs to be refreshed
        $now = time();
        $time = time();
        $difference = $this->access_token_expire - $time;
        if ($difference < strtotime('15 minutes', 0)) {
            $this->refreshAccessToken();
        }

        return $this->access_token;
    }

    /**
     * Set Hail API OAuth token in the current SiteConfig
     *
     * @throws
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
        $config = SiteConfig::current_site_config();
        $config->HailAccessToken = $access_token;
        $config->write();
    }

    /**
     * Refresh Hail API OAuth Access token from the API
     *
     * @throws
     */
    public function refreshAccessToken()
    {
        $http = $this->getHTTPClient();
        $post_data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refresh_token,
        ];

        // Refresh access token
        try {
            $response = $http->request('POST', 'oauth/access_token', [
                'form_params' => $post_data
            ]);

            $responseBody = $response->getBody();
            $responseArr = json_decode($responseBody, true);

            //Set new data into the config and update the current instance
            $this->setAccessToken($responseArr['access_token']);
            $this->setAccessTokenExpire($responseArr['expires_in']);
            $this->setRefreshToken($responseArr['refresh_token']);
        } catch (\Exception  $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * Check if the Hail module is Authorized with the Hail API
     *
     * @return boolean
     */
    public function isAuthorised()
    {
        return $this->access_token_expire && $this->access_token && $this->refresh_token;
    }

    /**
     * Check if the Hail module is ready to be authorized with the Hail API
     *
     * @return boolean
     */
    public function isReadyToAuthorised()
    {
        return $this->client_id && $this->client_secret;
    }

    /**
     * Get all available Hail Organisation the configured client has access to
     *
     * @param boolean $as_simple_array Return a simple associative array (ID => Tile )instead of the full objects
     *
     * @return array
     * @throws
     */
    public function getAvailableOrganisations($as_simple_array = false)
    {
        $organisations = $this->get('users/' . $this->user_id . '/organisations');
        //If simple array is true, we send back an array with [id] => [name] instead of the full list
        if ($as_simple_array) {
            $temp = [];
            foreach ($organisations as $org) {
                $temp[$org['id']] = $org['name'];
            }
            $organisations = $temp;
        }
        asort($organisations);

        return $organisations;
    }

    /**
     * Get all available Private Tags from the Hail API
     * Will get all tags from all configured organisations unless specified otherwise
     *
     * @param array|null $organisations Pass an array of organisations (HailID => Organisation Name) to get the tag for, if null will get all configured organisation
     * @param boolean $as_simple_array Return a simple associative array (ID => Tile )instead of the full objects
     *
     * @return array|boolean
     * @throws
     */
    public function getAvailablePrivateTags($organisations = null, $as_simple_array = false)
    {
        $orgs_ids = $organisations ? array_keys($organisations) : json_decode($this->orgs_ids);
        if (!$orgs_ids) {
            //No organisations configured
            $this->handleException(new \Exception("You need at least 1 Hail Organisation configured to be able to fetch private tags"));
            return false;
        }
        $tag_list = [];
        foreach ($orgs_ids as $org_id) {
            //Get Org Name
            if ($organisations) {
                $org_name = isset($organisations[$org_id]) ? $organisations[$org_id] : '';
            } else {
                $org = DataObject::get_one(Organisation::class, ['HailID' => $org_id]);
                $org_name = $org ? $org->Title : "";
            }

            $results = $this->get('organisations/' . $org_id . '/private-tags');
            //If simple array is true, we send back an array with [id] => [name] instead of the full list
            if ($as_simple_array) {
                foreach ($results as $result) {
                    $tag_title = $result['name'];
                    //Add organisation name on tag title if more than 1 org
                    if (count($orgs_ids) > 1) {
                        $tag_title = $org_name . " - " . $tag_title;
                    }
                    $tag_list[$result['id']] = $tag_title;
                }
            } else {
                $tag_list = array_merge($results, $tag_list);
            }
        }
        asort($tag_list);

        return $tag_list;
    }

    /**
     * Get all available Public Tags from the Hail API
     * Will get all tags from all configured organisations unless specified otherwise
     *
     * @param array|null $organisations Pass an array of organisations (HailID => Organisation Name) to get the tag for, if null will get all configured organisation
     * @param boolean $as_simple_array Return a simple associative array (ID => Tile )instead of the full objects
     *
     * @return array|boolean
     * @throws
     */
    public function getAvailablePublicTags($organisations = null, $as_simple_array = false)
    {
        $orgs_ids = $organisations ? array_keys($organisations) : json_decode($this->orgs_ids);
        if (!$orgs_ids) {
            //No organisations configured
            $this->handleException(new \Exception("You need at least 1 Hail Organisation configured to be able to fetch public tags"));
            return false;
        }
        $tag_list = [];
        foreach ($orgs_ids as $org_id) {
            //Get Org Name
            if ($organisations) {
                $org_name = isset($organisations[$org_id]) ? $organisations[$org_id] : '';
            } else {
                $org = DataObject::get_one(Organisation::class, ['HailID' => $org_id]);
                $org_name = $org ? $org->Title : "";
            }

            $results = $this->get('organisations/' . $org_id . '/tags');
            //If simple array is true, we send back an array with [id] => [name] instead of the full list
            if ($as_simple_array) {
                foreach ($results as $result) {
                    $tag_title = $result['name'];
                    //Add organisation name on tag title if more than 1 org
                    if (count($orgs_ids) > 1) {
                        $tag_title = $org_name . " - " . $tag_title;
                    }
                    $tag_list[$result['id']] = $tag_title;
                }
            } else {
                $tag_list = array_merge($results, $tag_list);
            }
        }
        asort($tag_list);

        return $tag_list;
    }

    /**
     * Get the refresh rate in seconds for Hail Objects. Hail Object that have
     * not been retrieve for longer than the refresh rate, should be fetched
     * again.
     *
     * @return int
     */
    public static function getRefreshRate()
    {
        return Config::inst()->get(self::class, 'RefreshRate');
    }

    /**
     * Retrieve a list of images for a given article.
     *
     * @param string $id ID of the article in Hail
     *
     * @return array
     * @throws
     */
    public function getImagesByArticles($id)
    {
        $uri = Article::$object_endpoint . '/' . $id . '/' . Image::$object_endpoint;
        return $this->get($uri);
    }

    /**
     * Retrieve a list of videos for a given article.
     *
     * @param string $id ID of the article in Hail
     *
     * @return array
     * @throws
     */
    public function getVideosByArticles($id)
    {
        $uri = Article::$object_endpoint . '/' . $id . '/' . Video::$object_endpoint;
        return $this->get($uri);
    }
}