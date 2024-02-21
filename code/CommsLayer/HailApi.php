<?php
/**
 *
 */


use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;

/**
 * Communication layer around the Hail Api. It uses Httpful to communicate with
 * with Hail.
 *
 * Function on this class will throw {@link HailApiException} if there's a communication
 * error with the Hail API.
 *
 * An AccessKey and a OrganisationId must be provided in your config file for
 * HailAPI or all calls will result in an {@link HailApiException}.
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 * @const string TAGS What a tag is called in Hail
 * @const string ARTICLES What an article is called in Hail
 * @const string ORGS What an organisation is called in Hail
 * @const string IMAGES What an image is called in Hail
 */
class HailApi extends SS_Object
{

    const TAGS = 'tags';
    const ARTICLES = 'articles';
    const ORGS = 'organisations';
    const IMAGES = 'images';
    const VIDEOS = 'videos';
    const PUBLICATIONS = 'publications';
    const ATTACHMENTS = 'attachments';
    const PRIVATE_TAGS = 'private-tags';

    const HAIL_LIMIT = 500;

    /**
     * Send a GET request to the Hail API for a specific URI and returns
     * the results. Extra paremeters can be specified via $request.
     *
     * @param string $uri ressource to get
     * @param StdClass $request Body of the request to send to the Hail API.
     * @return StdClass Reply from Hail
     * @throws HailApiException
     */
    protected static function get($uri, HailOrganisation $org, $request = false, $update_last_fetched = false, $last_fetched_column = null)
    {
        // Initialise request
        $response = Request::get(static::config()->Url . $uri)
            ->addHeader('Authorization', 'Bearer ' . HailProvider::getHailAccessToken($org))
            ->timeoutIn(static::config()->Timeout);

        // If we have a request body
        if ($request) {
            $response
                ->sendsJson()
                ->body(json_encode($request));
        }

        // Send the request and catch any Comms Exception
        try {
            $reply = $response->send();
        } catch (ConnectionErrorException $ex) {
            throw new HailApiException($ex->getMessage(), 0, $ex);
        }

        // Validate the response we get from Hail
        if (!empty($reply->body->error->message)) {
            throw new HailApiException($reply->body->error->message, $reply->code);
        }

        if($update_last_fetched && !empty($last_fetched_column)) {
            $now = new DateTime("now", new DateTimeZone('UTC'));
            $org->{$last_fetched_column} = $now->format('Y-m-d H:i:s');
            $org->write();
        }

        return $reply->body;
    }

    /**
     * Retrieve a list of Hail API objects.
     *
     * @param string $objectType Object type to retrieve
     * @param HailOrganisation $org The Hail organisation
     * @param bool $since_last_fetched only fetch new data
     * @return StdClass
     * @throws HailApiException
     */
    public static function getList($objectType, HailOrganisation $org, $only_recent = false, $offset = 0)
    {
        $uri = '';
        $request = false;

        switch ($objectType) {
            case self::TAGS:
                $uri =
                    self::ORGS . '/' .
                    static::getOrganisationId($org) . '/' .
                    self::TAGS;
                //$request = array('status' => 'published');
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'published'];
                }
                break;
            case self::PRIVATE_TAGS:
                $uri =
                    self::ORGS . '/' .
                    static::getOrganisationId($org) . '/' .
                    self::PRIVATE_TAGS;
                //$request = array('status' => 'published');
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'published'];
                }
                break;
            case self::ARTICLES:
                $uri =
                    self::ORGS . '/' .
                    static::getOrganisationId($org) . '/' .
                    self::ARTICLES;
                //$request = array('status' => 'published');
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'published', 'limit' => static::HAIL_LIMIT, 'offset' => $offset];
                }
                //Sort by date desc
                $request['order'] = 'date|desc';
                break;
            case self::IMAGES:
                $uri =
                    self::ORGS . '/' .
                    static::getOrganisationId($org) . '/' .
                    self::IMAGES;
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'approved', 'limit' => static::HAIL_LIMIT, 'offset' => $offset];
                }
                break;
            case self::VIDEOS:
                $uri =
                    self::ORGS . '/' .
                    static::getOrganisationId($org) . '/' .
                    self::VIDEOS;
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'approved'];
                }
                break;
            case self::PUBLICATIONS:
                $uri =
                    self::ORGS . '/' .
                    static::getOrganisationId($org) . '/' .
                    self::PUBLICATIONS;
                //$request = array('status' => 'published');
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'published', 'limit' => static::HAIL_LIMIT, 'offset' => $offset];
                }
                //Sort by date desc
                $request['order'] = 'due_date|desc';
                break;
            case self::ORGS:
                $uri =
                    'users/' . self::getUserId($org) . '/' . self::ORGS;
                break;
            default:
                throw new HailApiException('Invalid object type.');
                break;
        }

        if($only_recent) {
            $col = "LastFetched_" . preg_replace("[0-9a-zA-Z_]","", $objectType);
            $last_fetched = $org->{$col};
            if(!empty($last_fetched)){
                //Build request param
                $fetch_date = new \DateTime($last_fetched, new \DateTimeZone('UTC'));
                $now = new \DateTime("now", new \DateTimeZone("UTC"));
                //Make sure we respect the Hail API date format
                $request['updated_start'] = $fetch_date->format('Y-m-d H:i:s');
                $request['updated_end'] = $now->format('Y-m-d H:i:s');
            }
        }

        return self::get($uri, $org, $request, $only_recent, isset($col) ? $col : null);
    }


    /**
     * Retrieve a specific Hail API object from its ID in Hail.
     *
     * @param string $objectType Object type to retrieve
     * @param string $hailID ID of the object in Hail
     * @param HailOrganisation $org The Hail organisation
     * @return StdClass
     * @throws HailApiException
     */
    public static function getOne($objectType, $hailID, HailOrganisation $org)
    {
        $uri = '';
        $request = false;

        switch ($objectType) {
            case self::TAGS:
                $uri = self::TAGS . '/' . $hailID;
                break;
            case self::ARTICLES:
                $uri = self::ARTICLES . '/' . $hailID;
                //$request = array('status' => 'published');
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'published'];
                }
                break;
            case self::IMAGES:
                $uri = self::IMAGES . '/' . $hailID;
                //$request = array('status' => 'published');
                if (self::getDisplayUnpublished()) {
                    $request = [];
                } else {
                    $request = ['status' => 'published'];
                }
                break;
        }

        return self::get($uri, $org, $request);
    }

    /**
     * Retrieve a list of articles for a given tag.
     *
     * @param string $tagId ID of the Tag in Hail
     * @param HailOrganisation $org The Hail organisation
     * @return array
     * @throws HailApiException
     */
    public static function getArticlesByTag($tagId, HailOrganisation $org, $parameter = false)
    {
        $uri = self::TAGS . '/' . $tagId . '/' . self::ARTICLES;
        if (self::getDisplayUnpublished()) {
            $request = [];
        } else {
            $request = ['status' => 'published'];
        }
        if (is_array($parameter)) {
            $request = array_merge($request, $parameter);
        }
        return self::get($uri, $org, $request);
    }

    /**
     * Retrieve a list of images for a given article.
     *
     * @param string $id ID of the article in Hail
     * @param HailOrganisation $org The Hail organisation
     * @return array
     * @throws HailApiException
     */
    public static function getImagesByArticles($id, HailOrganisation $org, $parameter = false)
    {
        $uri = self::ARTICLES . '/' . $id . '/' . self::IMAGES;
        return self::get($uri, $org, $parameter);
    }

    /**
     * Retrieve a list of videos for a given article.
     *
     * @param string $id ID of the article in Hail
     * @param HailOrganisation $org The Hail organisation
     * @return array
     * @throws HailApiException
     */
    public static function getVideosByArticles($id, HailOrganisation $org, $parameter = false)
    {
        $uri = self::ARTICLES . '/' . $id . '/' . self::VIDEOS;
        return self::get($uri, $org, $parameter);
    }

    /**
     * Retrieve user details about the credentials we're using to access hail
     *
     * @param string $id ID of the article in Hail
     * @param HailOrganisation The Hail organisation
     * @return array
     * @throws HailApiException
     */
    public static function getUser($org)
    {
        return self::get('me', $org);
    }

    /**
     * Get the refresh rate in seconds for Hail Objects. Hail Object that have
     * not been retrieve for longer than the refresh rate, should be fetch
     * again.
     *
     * @return int
     */
    public static function getRefreshRate()
    {
        return static::config()->RefreshRate;
    }

    public static function getOrganisationList(HailOrganisation $org)
    {
        $lists = self::getList(self::ORGS, $org);
        $orgs = [];
        foreach ($lists as $org) {
            $orgs[$org->id] = $org->name;
        }
        return $orgs;
    }

    private static function getDisplayUnpublished()
    {
        return static::config()->DisplayUnpublished;
    }

    private static function getUserId(HailOrganisation $org)
    {
        $hailUserId = $org->HailUserID;

        if (!$hailUserId) {
            throw new HailApiException('You must reauthorize SilverStripe\'s access to Hail.');
        };

        return $hailUserId;
    }

    private static function getOrganisationId(HailOrganisation $org)
    {
        $hailOrgID = $org->HailOrgID;

        if (!$hailOrgID) {
            throw new HailApiException('You must choose what organisation SilverStripe will be fetching data from.');
        };

        return $hailOrgID;
    }

    public static function getPrivateTagList(HailOrganisation $org)
    {
        $lists = self::getList(self::PRIVATE_TAGS, $org);
        $ptags = [];
        foreach ($lists as $ptag) {
            $ptags[$ptag->id] = $org->title . ' - ' . $ptag->name;
        }
        return $ptags;
    }

}
