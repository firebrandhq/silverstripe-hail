<?php

namespace Firebrand\Hail\Api;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\SiteConfig\SiteConfig;

class Provider
{
    private $client_id;
    private $client_secret;
    private $access_token;
    private $access_token_expire;
    private $refresh_token;
    private $redirect_code;
    private $user_id;
    private $orgs_ids;
    private $scopes = "user.basic content.read";

    public function __construct()
    {
        //Get all provider settings from global config
        $config = SiteConfig::current_site_config();
        $this->client_id = $config->HailClientID;
        $this->client_secret = $config->HailClientSecret;
        $this->access_token = $config->HailAccessToken;
        $this->access_token_expire = $config->HailAccessTokenExpire;
        $this->refresh_token = $config->HailRefreshToken;
        $this->redirect_code = $config->HailRedirectCode;
        $this->user_id = $config->HailUserID;
        $this->orgs_ids = $config->HailOrgsIDs;
    }

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

    public function get($uri, $body = null)
    {
        $options = [];
        $http = $this->getHTTPClient();
        $options['headers'] = [
            "Authorization" => "Bearer " . $this->getAccessToken()
        ];

        //Pass the body if needed
        if ($body) {
            $options['form_params'] = $body;
        }

        // Request access token
        try {
            $response = $http->request('GET', $uri, $options);
            $responseBody = $response->getBody();
            $responseArr = json_decode($responseBody, true);
        } catch (\Exception  $exception) {
            $this->handleException($exception);
            //Send empty array so the app doesnt crash
            $responseArr = [];
        }

        return $responseArr;
    }

    public function getHTTPClient()
    {
        return new Client(["base_uri" => $this->getApiBaseURL()]);
    }


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

    public function getRedirectURL()
    {
        return Director::absoluteURL('HailCallbackController', true);
    }

    public function getApiBaseURL()
    {
        return Config::inst()->get(get_class(new self), 'BaseApiUrl');
    }

    public function getAuthorizationURL()
    {
        $url = Config::inst()->get(get_class(new self), 'AuthorizationUrl');
        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $this->getRedirectURL(),
            'response_type' => "code",
            'scope' => $this->scopes,
        ];
        return $url . "?" . http_build_query($params);
    }

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

    public function setAccessToken($access_token)
    {
        $config = SiteConfig::current_site_config();
        $config->HailAccessToken = $access_token;
        $config->write();
        $this->access_token = $access_token;
    }

    public function setAccessTokenExpire($access_token_expire)
    {
        //Store expiry date as unix timestamp (now + expires in)
        $access_token_expire = time() + $access_token_expire;
        $config = SiteConfig::current_site_config();
        $config->HailAccessTokenExpire = $access_token_expire;
        $config->write();
        $this->access_token_expire = $access_token_expire;
    }

    public function setRefreshToken($refresh_token)
    {
        $config = SiteConfig::current_site_config();
        $config->HailrefreshToken = $refresh_token;
        $config->write();
        $this->refresh_token = $refresh_token;
    }

    public function setUserID()
    {
        $response = $this->get("me");
        $config = SiteConfig::current_site_config();
        $config->HailUserID = $response['id'];
        $config->write();
        $this->user_id = $response['id'];
    }

    public function isAuthorised()
    {
        return $this->access_token_expire && $this->access_token && $this->refresh_token;
    }

    public function isReadyToAuthorised()
    {
        return $this->client_id && $this->client_secret;
    }

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

}