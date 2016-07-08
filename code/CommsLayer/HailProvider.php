<?php

class HailProvider extends League\OAuth2\Client\Provider\AbstractProvider {

	/**
	 * The timeout for checking the Hail API. Defaults to 10 minutes.
	 * @var int
	 */
	private static $hail_timeout = 10;

	public function __construct($options = []) {


		if (! array_key_exists('redirectUri', $options)) {
			$options['redirectUri'] = static::getRedirectUri();
		}
		
		$siteConfig = SiteConfig::current_site_config();
		
		if (!array_key_exists('clientId', $options)) {
			$options['clientId'] = $siteConfig->HailClientID;
		}
		
		if (!array_key_exists('clientSecret', $options)) {
			$options['clientSecret'] = $siteConfig->HailClientSecret;
		}
		
		if (!array_key_exists('scopes', $options)) {
			$options['scopes'] = ['user.basic content.read'];
		}
		
		parent::__construct($options);
	}
	
	public static function getRedirectUri() {
		return Director::absoluteURL('HailCallbackController', true);
	}
	
	public function urlAuthorize() {
		return 'https://hail.to/oauth/authorise';
	} 
	
	public function urlAccessToken() {		
		return 'https://hail.to/api/v1/oauth/access_token';
	}
	
	public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token) {
		return HailApi::config()->Url . 'me';
	}
	
	public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token) {
		return $reponse;
	}
	
	public static function getHailAccessToken() {
		$siteconfig = SiteConfig::current_site_config();
		
		if (! static::isAuthorised()) {
			throw new HailApiException('Need to reauthorize SilverStripe to access Hail.');
		}

		$hailTimeout = Config::inst()->get('HailProvider', 'hail_timeout');

		// Use a default timeout of 10 minutes if a timeout isn't specified in the SiteConfig
		if($siteconfig->HailTimeout !== "0") {
			$hailTimeout = intval($siteconfig->HailTimeout);
		};

		// If the Hail timeout is less before our Token expires
		if ( $siteconfig->HailAccessTokenExpire-60 * $hailTimeout < time() ) {
			try {
				$provider = new static();
				$grant = new \League\OAuth2\Client\Grant\RefreshToken();
				$token = $provider->getAccessToken(
					$grant, 
					['refresh_token' => $siteconfig->HailRefreshToken]
				);
			} catch (Exception $ex) {
				throw new HailApiException('Need to reauthorize SilverStripe to access Hail.');
			}
			
			$siteconfig->HailAccessToken = $token->accessToken;
			$siteconfig->HailAccessTokenExpire = $token->expires;
			$siteconfig->HailRefreshToken = $token->refreshToken;
			
			$siteconfig->write();
			
		}
		
		return $siteconfig->HailAccessToken;
	}
	
	public static function isAuthorised() {
		$siteconfig = SiteConfig::current_site_config();
		return $siteconfig->HailAccessTokenExpire && $siteconfig->HailAccessToken && $siteconfig->HailRefreshToken;
	}
	
	public static function isReadyToAuthorised() {
		$siteconfig = SiteConfig::current_site_config();
		return $siteconfig->HailClientID && $siteconfig->HailClientSecret;
	}
	
}
