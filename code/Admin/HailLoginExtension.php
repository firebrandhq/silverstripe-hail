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
		'HailOrgID' => 'Varchar(255)'
	);

	private static $has_one = array(
		'PrimaryHailHolder' => 'HailHolder'
	);

	public function updateCMSFields(FieldList $fields) {
		$redirectUrl = HailProvider::getRedirectUri();

		$redirectField = new ReadonlyField('RedirectURL', 'Redirect URL', $redirectUrl);

		// Twitter setup
		$fields->addFieldsToTab('Root.Hail', array(
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
			$fields->addFieldsToTab('Root.Hail', new LiteralField('Go', "<a href='$auth'>$link</a>"));
		}
		try {
		if(HailProvider::isAuthorised()) {
			$orgs = HailApi::getOrganisationList();
			$orgs[''] = '';
			$orgField = DropdownField::create('HailOrgID', 'Hail Organisation', $orgs);
			$fields->addFieldsToTab('Root.Hail', $orgField);
		}
		} catch(HailApiException $ex) {
			$fields->addFieldsToTab('Root.Hail', new LiteralField('Retry', 'You Have to Re-Authorise SilverStripe to Access Hail'));
		}

		$holderField = DropdownField::create('PrimaryHailHolderID', 'Primary Hail Holder', HailHolder::get()->map('ID', 'Title'));
		$holderField->setEmptyString('(None)');
		$fields->addFieldsToTab('Root.Hail', $holderField);
	}

}
