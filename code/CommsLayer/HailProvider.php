<?php

class HailProvider extends League\OAuth2\Client\Provider\AbstractProvider {

	/**
	 * The timeout for checking the Hail API. Defaults to 10 minutes.
	 * @var int
	 */
	private static $hail_timeout = 10;

	public function __construct(HailOrganisation $org, $options = []) {


		if (! array_key_exists('redirectUri', $options)) {
			$options['redirectUri'] = static::getRedirectUri($org);
		}
		
		if (!array_key_exists('clientId', $options)) {
			$options['clientId'] = $org->HailClientID;
		}
		
		if (!array_key_exists('clientSecret', $options)) {
			$options['clientSecret'] = $org->HailClientSecret;
		}
		
		if (!array_key_exists('scopes', $options)) {
			$options['scopes'] = ['user.basic content.read'];
		}
		
		parent::__construct($options);
	}
	
	/**
	 * Gets the redirect callback for Hail
	 * @param HailOrganisation $org The Hail organisation 
	 * @return string
	 */
	public static function getRedirectUri($org) {
		return Director::absoluteURL('HailCallbackController', true) . '?org=' . $org->ID;
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
		return $response;
	}
	
	public static function getHailAccessToken(HailOrganisation $org) {
		$siteconfig = SiteConfig::current_site_config();
		
		if (! static::isAuthorised($org)) {
			throw new HailApiException('Need to reauthorize SilverStripe to access Hail.');
		}

		$hailTimeout = Config::inst()->get('HailProvider', 'hail_timeout');

		// Use a default timeout of 10 minutes if a timeout isn't specified in the SiteConfig
		if($siteconfig->HailTimeout !== "0") {
			$hailTimeout = intval($siteconfig->HailTimeout);
		};

		// If the Hail timeout is less before our Token expires
		if ( $org->HailAccessTokenExpire-60 * $hailTimeout < time() ) {
			try {
				$provider = new static($org);
				$grant = new \League\OAuth2\Client\Grant\RefreshToken();
				$token = $provider->getAccessToken(
					$grant, 
					['refresh_token' => $org->HailRefreshToken]
				);
			} catch (Exception $ex) {
				throw new HailApiException('Need to reauthorize SilverStripe to access Hail.');
			}
			
			$org->HailAccessToken = $token->accessToken;
			$org->HailAccessTokenExpire = $token->expires;
			$org->HailRefreshToken = $token->refreshToken;
			
			$org->write();
			
		}
		
		return $org->HailAccessToken;
	}
	
	public static function isAuthorised(HailOrganisation $org) {
		return $org->HailAccessTokenExpire && $org->HailAccessToken && $org->HailRefreshToken;
	}
	
	public static function isReadyToAuthorised(HailOrganisation $org) {
		return $org->HailClientID && $org->HailClientSecret;
	}
	
}
