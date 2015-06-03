<?php

class HailCallbackController extends Controller {
	/**
	* Default URL handlers - (Action)/(ID)/(OtherID)
	*/
	
	private static $allowed_actions = array(
		'index',
	);
	
	public function index() {
		$siteconfig = SiteConfig::current_site_config();
		
		if($siteconfig->canEdit()) {

			$siteconfig->HailRedirectCode = $_GET['code'];			
			
			
			$provider = new HailProvider();
			
			try {
				$token = $provider->getAccessToken('authorization_code', [
					'code' => $siteconfig->HailRedirectCode,
				]);
				
			} catch (Exception $ex) {
				die($ex->getMessage());
			}
			
			$siteconfig->HailAccessToken = $token->accessToken;
			$siteconfig->HailAccessTokenExpire = $token->expires;
			$siteconfig->HailRefreshToken = $token->refreshToken;
			
			$siteconfig->write();
			
			// Refresh site config and save the user id
			$user = HailApi::getUser();
			$siteconfig = SiteConfig::current_site_config();
			$siteconfig->HailUserID = $user->id;
			$siteconfig->write();
		}
		
		$this->redirect('admin/settings');
		
	}
}
