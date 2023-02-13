<?php

class HailOrganisation extends DataObject
{

    public static $db = [
        'Title' => 'Varchar',
        'HailClientID' => 'Varchar(255)',
        'HailClientSecret' => 'Varchar(255)',
        'HailAccessToken' => 'Text',
        'HailAccessTokenExpire' => 'Int',
        'HailRefreshToken' => 'Text',
        'HailRedirectCode' => 'Varchar(255)',
        'HailUserID' => 'Varchar(255)',
        'HailOrgID' => 'Varchar(255)',
        'HailTimeout' => 'Int',
        'LastFetched_articles' => 'Datetime',
        'LastFetched_publications' => 'Datetime',
        'LastFetched_tags' => 'Datetime',
        'LastFetched_privatetags' => 'Datetime',
        'LastFetched_images' => 'Datetime',
        'LastFetched_videos' => 'Datetime',
        'LastFetched_attachments' => 'Datetime',
    ];

    private static $has_one = [
        'PrimaryHailHolder' => 'HailHolder',
        'SecondaryHailHolder' => 'HailHolder',
        'SecondaryHailTag' => 'HailTag',
    ];

    private static $summary_fields = [
        'Title' => 'Hail Organisation',
        'PrimaryHailHolder.Title' => 'Primary Hail Holder',
        'HailClientID' => 'Client ID',
        'RedirectURL' => 'Redirect URL'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'HailAccessToken',
            'HailAccessTokenExpire',
            'HailRefreshToken',
            'HailRedirectCode',
            'HailUserID',
            'HailOrgID',
            'LastFetched_articles',
            'LastFetched_publications',
            'LastFetched_tags',
            'LastFetched_privatetags',
            'LastFetched_images',
            'LastFetched_videos',
            'LastFetched_attachments',
        ]);

        $redirectUrl = HailProvider::getRedirectUri($this);

        // Twitter setup
        $fields->addFieldsToTab('Root.Main', [
            new TextField('HailClientID', 'Client ID', null, 255),
            new TextField('HailClientSecret', 'Client Secret', null, 255),
        ]);

        if ($this->ID) {
            $fields->addFieldToTab('Root.Main', new ReadonlyField('RedirectURL', 'Redirect URL', $redirectUrl));
        }

        if (HailProvider::isReadyToAuthorised($this)) {
            $provider = new HailProvider($this);

            $link = HailProvider::isAuthorised($this) ?
                'Reauthorise SilverStripe to Access Hail' :
                'Authorise SilverStripe to Access Hail';

            $auth = $provider->getAuthorizationUrl();
            $fields->addFieldsToTab('Root.Main', new LiteralField('Go', "<a href='$auth'>$link</a>"));
        }
        try {
            if (HailProvider::isAuthorised($this)) {
                $orgs = HailApi::getOrganisationList($this);
                $orgs[''] = '';
                $orgField = DropdownField::create('HailOrgID', 'Hail Organisation', $orgs);
                $fields->addFieldsToTab('Root.Main', $orgField);
            }
        } catch (HailApiException $ex) {
            $fields->addFieldsToTab('Root.Hail', new LiteralField('Retry', 'You Have to Re-Authorise SilverStripe to Access Hail'));
        }

        $holderField = DropdownField::create('PrimaryHailHolderID', 'Primary Hail Holder', HailHolder::get()->filter("ID:not", $this->SecondaryHailHolderID)->map('ID', 'Title'));
        $holderField->setEmptyString('(None)');
        $fields->addFieldToTab('Root.Main', $holderField, 'RedirectURL');

        $secHolderField = DropdownField::create('SecondaryHailHolderID', 'Secondary Hail Holder', HailHolder::get()->filter("ID:not", $this->PrimaryHailHolderID)->map('ID', 'Title'));
        $secHolderField->setEmptyString('(None)');
        $secHolderField->setRightTitle('All articles that have the "Secondary Hail Tag" (set below) will use the Secondary Holder');
        $fields->addFieldToTab('Root.Main', $secHolderField, 'RedirectURL');

        $secHailTag = DropdownField::create('SecondaryHailTagID', 'Secondary Hail Tag', HailTag::get()->map('ID', 'Name'));
        $secHailTag->setEmptyString('(None)');
        $fields->addFieldToTab('Root.Main', $secHailTag, 'RedirectURL');

        $fields->addFieldToTab('Root.Main', NumericField::create('HailTimeout', 'Hail Timeout'));

        return $fields;
    }

    public function getRedirectURL()
    {
        return HailProvider::getRedirectUri($this);
    }

}
