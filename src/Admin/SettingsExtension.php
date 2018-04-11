<?php

namespace Firebrand\Hail\Admin;

use Firebrand\Hail\Api\Provider;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LabelField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\MultiSelectField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

class SettingsExtension extends DataExtension
{
    private static $db = [
        "HailClientID" => "Varchar(255)",
        "HailClientSecret" => "Varchar(255)",
        "HailAccessToken" => "Varchar(255)",
        "HailAccessTokenExpire" => "Varchar(255)",
        "HailRefreshToken" => "Varchar(255)",
        "HailRedirectCode" => "Varchar(255)",
        "HailUserID" => "Varchar(255)",
        "HailOrgsIDs" => "Varchar(255)",
    ];

    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);
        //Create Hail tab
        $fields->insertAfter(TabSet::create('Hail', 'Hail'), 'Root');

        $hail_provider = new Provider();
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();

        //Error display from Callback Controller
        if ($session->get('notice') === true && !empty($session->get('noticeText'))) {
            $label = "";
            $class = "notice-good";
            if (!empty($session->get('noticeText'))) {
                if ($session->get('noticeType') === "bad") {
                    $label = "Error: ";
                    $class = "notice-bad";

                } else {
                    $label = "Success: ";
                }
            }
            $notice = TextField::create("notice", "", $label . $session->get('noticeText'));
            $notice->setReadonly(true);
            $notice->addExtraClass($class);
            $fields->addFieldToTab("Root.Hail", $notice);
            //remove notice from the session so it's not displayed on next page load
            $session->clear('notice');
            $session->clear('noticeType');
            $session->clear('noticeText');
        }
        //Hail settings fields
        $client_id = TextField::create("HailClientID", "Hail Client ID");
        $client_secret = TextField::create("HailClientSecret", "Hail Client Secret");
        $fields->addFieldsToTab('Root.Hail', [
            $client_id,
            $client_secret
        ]);
        if ($hail_provider->isReadyToAuthorised()) {

            $link = $hail_provider->isAuthorised() ?
                'Reauthorise SilverStripe to Access Hail' :
                'Authorise SilverStripe to Access Hail';

            $auth = $hail_provider->getAuthorizationURL();
            $fields->addFieldToTab('Root.Hail', new LiteralField('Go', "<div class='form-group form__field-label'><a class='btn btn-primary' href='$auth'>$link</a></div>"));
        }
        if($hail_provider->isAuthorised()) {
            //Organisations list
            $organisations = $hail_provider->getAvailableOrganisations(true);
            $org_selector = ListboxField::create("HailOrgsIDs","Hail organisations", $organisations);
            $fields->addFieldToTab('Root.Hail',$org_selector);
        }

    }

}