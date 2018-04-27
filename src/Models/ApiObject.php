<?php

namespace Firebrand\Hail\Models;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Forms\GridFieldForReadonly;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

class ApiObject extends DataObject
{
    public static $object_endpoint;
    protected static $api_map;
    private static $table_name = "HailApiObject";
    private static $api_access = true;
    private static $indexes = [
        'HailID' => true
    ];
    private static $db = [
        'HailID' => 'Varchar',
        'HailOrgID' => 'Varchar',
        'HailOrgName' => 'Varchar',
        'Fetched' => 'Datetime'
    ];
    private static $summary_fields = [
        'HailID',
        'Fetched'
    ];
    private static $has_one = [
        'Organisation' => 'Firebrand\Hail\Models\Organisation'
    ];
    private static $organisations_endpoint = "organisations";

    /**
     * Determines if the object is outdated
     *
     * @return boolean
     */
    public function isOutdated()
    {
        if ($this->Fetched) {
            $fetched = new \DateTime($this->Fetched);
            $now = new \DateTime("now");
            $diff = $now->getTimestamp() - $fetched->getTimestamp();
            if ($diff > Client::getRefreshRate()) {
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Retrieves the latest version of this object from the Hail API
     *
     * @return HailApiObject
     */
    public function refresh()
    {
        if ($this->ID && $this->HailID) {
            try {
                $api_client = new Client();
                $data = $api_client->getOne($this);
            } catch (\Exception $ex) {
                return $this;
            }

            $this->importHailData($data);
        }

        return $this;
    }

    /**
     * Imports JSON data retrieve from the hail API. Return true if the value
     * should be saved to the database. False if it has been excluded.
     *
     * @param StdClass $data JSON data from Hail
     * @return boolean
     */
    protected function importHailData($data)
    {

        if ($this->excluded($data)) {
            return false;
        }

        $dataMap = array_merge(
            ['HailID' => 'id'],
            static::$api_map
        );

        foreach ($dataMap as $ssKey => $hailKey) {
            $this->{$ssKey} = (isset($data[$hailKey]) && !empty($data[$hailKey])) ? $data[$hailKey] : '';
        }
        $this->Fetched = date("Y-m-d H:i:s");

        $this->write();

        $this->importing($data);

        $this->write();

        return true;
    }

    /**
     * Determine if this object is to be exlucded based on the provided data (private tags).
     *
     * @param StdClass $data JSON data from Hail
     * @return boolean
     */
    protected function excluded($data)
    {
        $results = $this->extend('excluded', $data);

        return false;
    }

    /**
     * Is called by {@link importHailData()} to allow children classes to perform additional data assignement
     *
     * @param StdClass $data JSON data from Hail
     * @return void
     */
    protected function importing($data)
    {
    }

    public static function fetchAll()
    {
        //Hail Api Client
        $hail_api_client = new Client();

        $config = SiteConfig::current_site_config();
        $orgs_ids = json_decode($config->HailOrgsIDs);
        if (!$orgs_ids) {
            //No organisations configured
            $hail_api_client->handleException("You need at least 1 Hail Organisation configured to be able to fetch");
            return false;
        }
        //Fetch objects for all configured Hail organisations
        foreach ($orgs_ids as $org_id) {
            //Get Org Name
            $org = DataObject::get_one(Organisation::class, ['HailID' => $org_id]);
            $org_name = $org ? $org->Title : "";

            //Get Objects
            $url = self::$organisations_endpoint . "/" . $org_id . "/" . static::$object_endpoint;
            $results = $hail_api_client->get($url);
            $hailIdList = [];
            foreach ($results as $result) {
                // Check if we can find an existing item.
                $hailObj = DataObject::get_one(static::class, ['HailID' => $result['id']]);
                if (!$hailObj) {
                    $hailObj = new static();
                }
                $hailObj->OrganisationID = $org ? $org->ID : 0;
                $hailObj->HailOrgID = $org_id;
                $hailObj->HailOrgName = $org_name;
                $hailObj->write();

                $imported = $hailObj->importHailData($result);

                if ($imported) {
                    //Build up Hail ID list
                    $hailIdList[] = $result['id'];
                }
            }
            if ($org) {
                //Remove all object for which we don't have reference
                if(count($hailIdList) > 0 ) {
                    static::get()->filter('OrganisationID', $org->ID)->exclude('HailID', $hailIdList)->removeAll();
                } else {
                    static::get()->filter('OrganisationID', $org->ID)->removeAll();
                }
            }
        }
    }

    /**
     * Return a list of all the subclasses of HailApiObject that can be fetch from Hail.
     * @return string[]
     */
    public static function fetchables()
    {
        return ['HailArticle', 'HailImage', 'HailPublication', 'HailTag', 'HailVideo'];
    }

    public function canView($member = false)
    {
        // Always allow users to view HailApiObject.
        // This needs to be true to allow HailApiObject to be return via the
        // SilverStripe Restful web service
        return true;
    }

    // We don't want smelly users to start deleting HailApiObjects
    // This is only allow to happen programmaticaly
    function canDelete($member = false)
    {
        return false;
    }

    // We don't want smelly users to start creating HailApiObjects
    // This is only allow to happen programmaticaly
    function canCreate($member = false, $context = [])
    {
        return false;
    }

    // We don't want smelly users to start creating HailApiObjects
    // This is only allow to happen programmaticaly
    function canEdit($member = false)
    {
        return false;
    }

    /**
     * Helper function to help children class add GridFieldView to display
     * their relations to other HailApiObject
     *
     * @param FieldList $fields
     * @param string $name Name that should be given to the GridFieldView
     * @param ManyManyList $relation Relation to display
     * @return void
     */
    protected function makeRecordViewer($fields, $name, $relation, $viewComponent = 'Firebrand\Hail\Forms\GridFieldViewButton')
    {
        $config = GridFieldConfig_RecordViewer::create();

        // Remove the standard GridFieldView button and replace it with our
        // custom button that will link to our the right action in our HailModelAdmin
        $config->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldViewButton');
        $config->addComponents(new $viewComponent());

        //Relation tab names don't have spaces in SS4
        $tab_name = str_replace(" ", "", $name);
        $grid = new GridFieldForReadonly($tab_name, $name, $relation, $config);
        $fields->addFieldToTab('Root.' . $tab_name, $grid);
    }

    // Go through the list of tags and assign them to this object.
    protected function processPublicTags($data)
    {
        $tagIdList = [];
        // Clean tags before importing the new ones
        // but have not been returned this time around
        $this->PublicTags()->removeAll();
        foreach ($data as $tagData) {
            $tagIdList[] = $tagData['id'];

            // Find a matching HailTag or create an new one
            $tag = PublicTag::get()->filter(['HailID' => $tagData['id']])->first();

            if (!$tag) {
                $tag = new PublicTag();
            }

            $tag->OrganisationID = $this->OrganisationID;
            $tag->HailOrgID = $this->HailOrgID;
            $tag->HailOrgName = $this->HailOrgName;

            // Update the PublicTags
            $tag->importHailData($tagData);
            if (!$this->PublicTags()->byID($tag->ID)) {
                $this->PublicTags()->add($tag);
            }
        }

    }

    // Go through the list of tags and assign them to this object.
    protected function processPrivateTags($data)
    {
        $tagIdList = [];
        $this->PrivateTags()->removeAll();
        foreach ($data as $tagData) {
            $tagIdList[] = $tagData['id'];

            // Find a matching PrivateTag or create an new one
            $tag = PrivateTag::get()->filter(['HailID' => $tagData['id']])->first();

            if (!$tag) {
                $tag = new PrivateTag();
            }

            $tag->OrganisationID = $this->OrganisationID;
            $tag->HailOrgID = $this->HailOrgID;
            $tag->HailOrgName = $this->HailOrgName;

            // Update the Hail Tag
            $tag->importHailData($tagData);
            if (!$this->PrivateTags()->byID($tag->ID)) {
                $this->PrivateTags()->add($tag);
            }
        }
    }

    // Match the hero image if there's one
    protected function processHeroImage($heroImgData)
    {
        if ($heroImgData) {
            $hero = Image::get()->filter(['HailID' => $heroImgData['id']])->first();
            if (!$hero) {
                $hero = new Image();
            }
            $hero->OrganisationID = $this->OrganisationID;
            $hero->HailOrgID = $this->HailOrgID;
            $hero->HailOrgName = $this->HailOrgName;

            $hero->importHailData($heroImgData);
            $hero = $hero->ID;
        } else {
            $hero = null;
        }

        $this->HeroImageID = $hero;
    }

    // Match the hero video if there's one
    protected function processHeroVideo($heroVidData)
    {
        if ($heroVidData) {
            $hero = Video::get()->filter(['HailID' => $heroVidData['id']])->first();
            if (!$hero) {
                $hero = new Video();
            }
            $hero->OrganisationID = $this->OrganisationID;
            $hero->HailOrgID = $this->HailOrgID;
            $hero->HailOrgName = $this->HailOrgName;

            $hero->importHailData($heroVidData);
            $hero = $hero->ID;
        } else {
            $hero = null;
        }

        $this->HeroVideoID = $hero;
    }

    // Go through the attachments and assign them to this object.
    protected function processAttachments($data)
    {
        $idList = [];
        foreach ($data as $attachmentData) {
            $idList[] = $attachmentData['id'];

            // Find a matching attachment or create it
            $attachment = Attachment::get()->filter(['HailID' => $attachmentData['id']])->first();

            if (!$attachment) {
                $attachment = new Attachment();
            }
            $attachment->OrganisationID = $this->OrganisationID;
            $attachment->HailOrgID = $this->HailOrgID;
            $attachment->HailOrgName = $this->HailOrgName;

            // Update the Hail Attachments
            $attachment->importHailData($attachmentData);

            if (!$this->Attachments()->byID($attachment->ID)) {
                $this->Attachments()->add($attachment);
            }
        }

        // Remove old attachments that are currently assigned to this article,
        // but have not been returned this time around
        if ($idList) {
            $this->Attachments()->exclude('HailID', $idList)->removeAll();
        } else {
            // If there's no attachements, just remove everything.
            $this->Attachments()->removeAll();
        }
    }
}
