<?php

class HailOrganisation extends DataObject {

	public static $db = array(
		'HailClientID' => 'Varchar(255)',
		'HailClientSecret' => 'Varchar(255)',
		'HailAccessToken' => 'Varchar(255)',
		'HailAccessTokenExpire' => 'Int',
		'HailRefreshToken' => 'Varchar(255)',
		'HailRedirectCode' => 'Varchar(255)',
		'HailUserID' => 'Varchar(255)',
		'HailOrgID' => 'Varchar(255)',
		'HailTimeout' => 'Int'
	);

	private static $has_one = array(
		'PrimaryHailHolder' => 'HailHolder'
	);

	private static $summary_fields = array(
		'Title' => 'Hail Organisation',
		'PrimaryHailHolder.Title' => 'Primary Hail Holder',
		'HailClientID' => 'Client ID',
		'RedirectURL' => 'Redirect URL'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName(array('HailAccessToken', 'HailAccessTokenExpire', 'HailRefreshToken', 'HailRedirectCode', 'HailUserID', 'HailOrgID'));

		$redirectUrl = HailProvider::getRedirectUri($this);

		$redirectField = new ReadonlyField('RedirectURL', 'Redirect URL', $redirectUrl);

		// Twitter setup
		$fields->addFieldsToTab('Root.Main', array(
			new TextField('HailClientID', 'Client ID', null, 255),
			new TextField('HailClientSecret', 'Client Secret', null, 255),
			$redirectField
		));

		if(HailProvider::isReadyToAuthorised($this)) {
			$provider = new HailProvider($this);

			$link = HailProvider::isAuthorised($this) ?
				'Reauthorise SilverStripe to Access Hail':
				'Authorise SilverStripe to Access Hail';

			$auth = $provider->getAuthorizationUrl();
			$fields->addFieldsToTab('Root.Main', new LiteralField('Go', "<a href='$auth'>$link</a>"));
		}
		try {
			if(HailProvider::isAuthorised($this)) {
				$orgs = HailApi::getOrganisationList($this);
				$orgs[''] = '';
				$orgField = DropdownField::create('HailOrgID', 'Hail Organisation', $orgs);
				$fields->addFieldsToTab('Root.Main', $orgField);
			}
		}
		catch(HailApiException $ex) {
			$fields->addFieldsToTab('Root.Hail', new LiteralField('Retry', 'You Have to Re-Authorise SilverStripe to Access Hail'));
		}

		$holderField = DropdownField::create('PrimaryHailHolderID', 'Primary Hail Holder', HailHolder::get()->map('ID', 'Title'));
		$holderField->setEmptyString('(None)');
		$fields->addFieldsToTab('Root.Main', $holderField);

		$fields->addFieldToTab('Root.Main', NumericField::create('HailTimeout', 'Hail Timeout'));

		return $fields;
	}

	public function getRedirectURL() {
		return HailProvider::getRedirectUri($this);
	}

	public function getTitle() {
		$orgs = HailApi::getOrganisationList($this);

		if($orgs && isset($orgs[$this->HailOrgID])) {
			return $orgs[$this->HailOrgID];
		} else if($this->ID) {
			return 'Hail organisation ' . $this->ID;
		} else {
			return 'Hail organisation';
		}
	}

}