<?php

/**
 * Sets twitter configuration in the SiteConfig
 * 
 * @author Damian Mooyman
 * 
 * @package twitter
 */
class HailLoginExtension extends DataExtension {
	
	private static $db = array(
		'HailClientID' => 'Varchar(255)',
		'HailClientSecret' => 'Varchar(255)',
		'HailAccessToken' => 'Varchar(255)',
		'HailAccessTokenExpire' => 'Int',
		'HailRefreshToken' => 'Varchar(255)',
		'HailRedirectCode' => 'Varchar(255)',
		'HailUserID' => 'Varchar(255)',
		'HailOrgID' => 'Varchar(255)',
	);
	
	public function updateCMSFields(FieldList $fields) {
		$redirectUrl = HailProvider::getRedirectUri();
		
		$redirectField = new TextField('RedirectURL', 'Redirect URL', $redirectUrl);
		$redirectField->setReadonly(true);
		
		
		// Twitter setup
		$fields->addFieldsToTab('Root.HailLoginDetails', array(
			new TextField('HailClientID', 'Client ID', null, 255),
			new TextField('HailClientSecret', 'Client Secret', null, 255),
			$redirectField
		));
		
		$siteconfig = SiteConfig::current_site_config();
		
		if(HailProvider::isReadyToAuthorised()) {
			$provider = new HailProvider();

			$link = HailProvider::isAuthorised() ?				
				'Reauthorise SilverStripe to Access Hail':
				'Authorise SilverStripe to Access Hail';

			$auth = $provider->getAuthorizationUrl();
			$fields->addFieldsToTab('Root.HailLoginDetails', new LiteralField('Go', "<a href='$auth'>$link</a>"));
		}
		try {
		if(HailProvider::isAuthorised()) {
			$orgs = HailApi::getOrganisationList();
			$orgs[''] = '';
			$orgField = DropdownField::create('HailOrgID', 'Hail Organisation', $orgs);
			$fields->addFieldsToTab('Root.HailLoginDetails', $orgField);
		}
		} catch(HailApiException $ex) {
			$fields->addFieldsToTab('Root.HailLoginDetails', new LiteralField('Retry', 'You Have to Re-Authorise SilverStripe to Access Hail'));
		}
		
	}
}
