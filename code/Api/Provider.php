<?php

namespace Firebrand\Hail\Api;

use GuzzleHttp\Client;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
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

    public function get()
    {

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

    }

    public function isAuthorised()
    {
        return $this->access_token_expire && $this->access_token && $this->refresh_token;
    }

    public function isReadyToAuthorised()
    {
        return $this->client_id && $this->client_secret;
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
        $response = $http->request('POST', 'oauth/access_token', [
            'form_params' => $post_data
        ]);

        $responseBody = $response->getBody();
        $responseArr = json_decode($responseBody, true);

        //Set new data into the config and update the current instance
        $this->setAccessToken($responseArr['access_token']);
        $this->setAccessTokenExpire($responseArr['expires_in']);
        $this->setRefreshToken($responseArr['refresh_token']);
    }
}