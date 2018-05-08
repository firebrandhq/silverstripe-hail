<?php

namespace Firebrand\Hail\Admin;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Models\Organisation;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

class SettingsExtension extends DataExtension
{
    private static $db = [
        "HailAccessToken" => "Varchar(255)",
        "HailAccessTokenExpire" => "Varchar(255)",
        "HailRefreshToken" => "Varchar(255)",
        "HailUserID" => "Varchar(255)",
        "HailOrgsIDs" => "Varchar(255)",
        "HailExcludePrivateTagsIDs" => "Text",
        "HailExcludePublicTagsIDs" => "Text",
        "HailAPIStatusCurrent" => "Varchar",
        "HailAPIStatusLastChecked" => "Datetime",
    ];

    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);
        //Create Hail tab
        $fields->insertAfter(TabSet::create('Hail', 'Hail'), 'Root');

        $hail_api_client = new Client();
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
        if ($hail_api_client->isReadyToAuthorised()) {
            //Add a reaonly field to display the CallBack URL
            $callback = ReadonlyField::create('CallBackURL', 'Callback URL',
                $hail_api_client->getRedirectURL())->setDescription("Please add the following callback URL in Hail before starting the authorization process.");
            $fields->addFieldToTab('Root.Hail', $callback);

            $link = $hail_api_client->isAuthorised() ?
                'Reauthorise SilverStripe to Access Hail' :
                'Authorise SilverStripe to Access Hail';

            $auth = $hail_api_client->getAuthorizationURL();
            $fields->addFieldToTab('Root.Hail', new LiteralField('Go', "<div class='form-group form__field-label'><a class='btn btn-primary' href='$auth'>$link</a></div>"));
        } else {
            //Display message if client id or client secret is not configured
            $client_message = ReadonlyField::create('ClientErrorMessage', 'Configuration error ', "Please add your Hail Client ID and Secret to your .env file, see documentation.")
                ->addExtraClass("notice-bad");
            $fields->addFieldToTab('Root.Hail', $client_message);
        }
        if ($hail_api_client->isAuthorised()) {
            //Organisations list
            $organisations = $hail_api_client->getAvailableOrganisations(true);
            $org_selector = ListboxField::create("HailOrgsIDs", "Hail organisations", $organisations)
                ->setDescription("Please refresh this page after saving to access tag exclusion lists");
            $fields->addFieldToTab('Root.Hail', $org_selector);

            //Only show exclude tag lists if organisations are setup
            if (!empty($this->getOwner()->HailOrgsIDs)) {
                //Private Tags List
                $private_tags = $hail_api_client->getAvailablePrivateTags(true);
                $private_tag_selector = ListboxField::create("HailExcludePrivateTagsIDs", "Globally excluded private tags", $private_tags)
                    ->setDescription("Articles and publications with those private tags will never be fetched");
                $fields->addFieldToTab('Root.Hail', $private_tag_selector);

                //Public Tags list
                $public_tags = $hail_api_client->getAvailablePublicTags(true);
                $public_tag_selector = ListboxField::create("HailExcludePublicTagsIDs", "Globally excluded public tags", $public_tags)
                    ->setDescription("Articles and publications with those public tags will never be fetched");
                $fields->addFieldToTab('Root.Hail', $public_tag_selector);
            }
        }

    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        //Populate the Organisations table depending on what is configured for the Hail module
        $prev_config = DataObject::get_one(SiteConfig::class);
        if ($prev_config->HailOrgsIDs !== $this->getOwner()->HailOrgsIDs) {
            $orgs = json_decode($this->getOwner()->HailOrgsIDs);
            if ($orgs) {
                $hail_api_client = new Client();
                $organisations = $hail_api_client->getAvailableOrganisations(true);
                foreach ($orgs as $id) {
                    $organisation = DataObject::get_one(Organisation::class, ['HailID' => $id]);
                    //Create or udpate
                    if ($organisation) {
                        $organisation->Title = $organisations[$id];
                    } else {
                        $organisation = Organisation::create(['HailID' => $id, "Title" => $organisations[$id]]);
                    }
                    $organisation->write();
                }
            }
        }
    }
}