<?php

namespace Firebrand\Hail\Models;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Forms\GridFieldForReadonly;
use Firebrand\Hail\Jobs\FetchJob;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Abstract representation of an Hail Api object.
 *
 * Will never be used directly apart from the static methods
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $HailID
 * @property string $HailOrgID Hail ID of this object's organisation
 * @property string $HailOrgName Name of this object's organisation
 * @property string $Fetched Date and Time of the last time the object was fetched
 *
 * @method Organisation Organisation()
 */
class ApiObject extends DataObject
{
    /**
     * List of all the subclasses of Hail ApiObject that can be fetched from Hail
     * @var array
     */
    public static $fetchables = [
        'Firebrand\Hail\Models\Article',
        'Firebrand\Hail\Models\Publication',
        'Firebrand\Hail\Models\PublicTag',
        'Firebrand\Hail\Models\PrivateTag'
    ];
    /**
     * Hail API endpoint name for this object
     * @var string
     */
    public static $object_endpoint;
    /**
     * Map the fields returned by the Hail API with the SilverStripe DB Fields
     * @var array
     */
    protected static $api_map;
    /**
     * Hail Organisation endpoint
     * @var string
     */
    private static $organisations_endpoint = "organisations";
    private static $table_name = "HailApiObject";
    private static $api_access = false;
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

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        //Add an update button to fetchable objects
        if (ApiObject::isFetchable($this->getClassName())) {
            $fetch_one = LiteralField::create('FetchOneButton',
                '<div class="form-group">
                    <div class="form__field-holder">
                        <button class="btn btn-primary hail-fetch-one" 
                                data-tofetch="' . str_replace('\\', '-', $this->getClassName()) . '" 
                                data-hailid="' . $this->HailID . '"
                        >
                            Update this ' . strtolower($this->singular_name()) . '
                        </button>
                        <div class="hail-fetch-loading hide"></div>
                    </div>
                </div>');

            $fields->addFieldToTab('Root.Main', $fetch_one, 'HailID');
        }

        return $fields;
    }

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
     * Determines if the object is fetchable
     *
     * @param string $class_name
     * @return boolean
     */
    public static function isFetchable($class_name)
    {
        return in_array($class_name, self::$fetchables);
    }

    /**
     * Retrieves the latest version of this object from the Hail API
     *
     * @return ApiObject
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
            if (count($data) > 0) {
                $this->importHailData($data);
            }
        }

        return $this;
    }

    /**
     * Process the json data from Hail API and writes to SS db
     *
     * Return true if the value should be saved to the database. False if it has been excluded.
     *
     * @param array $data JSON data from Hail
     * @return boolean
     * @throws
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
            $value = (isset($data[$hailKey]) && !empty($data[$hailKey])) ? $data[$hailKey] : '';
            //Remove all NON UTF8 if Emoji support is disabled
            if (!Config::inst()->get(Client::class, 'EnableEmojiSupport')) {
                $value = preg_replace('/[^(\x20-\x7F)]*/', '', $value);
            }
            $this->{$ssKey} = html_entity_decode($value);
        }
        $this->Fetched = date("Y-m-d H:i:s");

        $this->write();

        $this->importing($data);

        $this->write();

        return true;
    }

    /**
     * Is called by {@link importHailData()} to allow children classes to perform additional data assignment
     *
     * @param array $data JSON data from Hail
     * @return void
     */
    protected function importing($data)
    {
    }

    /**
     * Determine if this object is to be excluded based on the provided data (public and private tags).
     *
     * Can be extended using updateExcluded() to allow further customization of the exclusion list
     *
     * @param array $data JSON data from Hail
     * @return boolean
     */
    protected function excluded($data)
    {
        $isExcluded = false;
        //Check global exclusion in SiteConfig
        $config = SiteConfig::current_site_config();

        //IF private tags exclusion if configured and object has private tags
        if (!empty($config->HailExcludePrivateTagsIDs) && isset($data['private_tags']) && count($data['private_tags']) > 0) {
            $private_tags = json_decode($config->HailExcludePrivateTagsIDs);
            foreach ($data['private_tags'] as $tag) {
                if (in_array($tag['id'], $private_tags)) {
                    $isExcluded = true;
                    break;
                }
            }
        }
        //IF public tags exclusion if configured and object has public tags
        if (!empty($config->HailExcludePublicTagsIDs) && isset($data['tags']) && count($data['tags']) > 0) {
            $public_tags = json_decode($config->HailExcludePublicTagsIDs);
            foreach ($data['tags'] as $tag) {
                if (in_array($tag['id'], $public_tags)) {
                    $isExcluded = true;
                    break;
                }
            }
        }

        $this->extend('updateExcluded', $isExcluded, $data);

        return $isExcluded;
    }


    /**
     * Fetch from Hail API for a specified Organisation
     *
     * @param Client $hail_api_client Hail Api Client to use for the fetch
     * @param string $org_id Hail ID of the organisation to fetch from
     * @param FetchJob|null $job If job is passed, the progress of the fetch will be updated in it
     * @param string $request_params Additional request params to send to the Hail API
     * @param boolean $throw_errors Enables error throwing
     * @throws
     */
    public static function fetchForOrg($hail_api_client, $org_id, $job = null, $request_params = null, $throw_errors = false)
    {
        $is_cli = php_sapi_name() == "cli";

        //Get Org Name
        $org = DataObject::get_one(Organisation::class, ['HailID' => $org_id]);
        $org_name = $org ? $org->Title : "";

        //Do we want only published objects
        if (Config::inst()->get(Client::class, 'OnlyFetchPublishedObjects') === true) {
            $request_params['status'] = 'published';
        }

        //Fetch objects
        $url = self::$organisations_endpoint . "/" . $org_id . "/" . static::$object_endpoint;
        $results = $hail_api_client->get($url, $request_params, $throw_errors);

        //If this is launched from a queued job, update it to display realtime info on the frontend
        if (count($results) > 0 && $job) {
            $job->CurrentObject = $org_name . " - " . (new static)->plural_name();
            $job->CurrentDone = 0;
            $job->CurrentTotal = count($results);
            $job->write();
        }

        if($is_cli){
            $output = new ConsoleOutput();
            if(count($results) > 0) {
                $progressBar = new ProgressBar($output, count($results));
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | elapsed: %elapsed:6s% | remaining: %estimated:-6s%');
                $progressBar->start();
            } else {
                $output->write("Nothing to fetch.", true);
            }

        }

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
                //Update job count if necessary
                if ($job) {
                    $job->CurrentDone++;
                    $job->write();
                }
            } else {
                //$imported is false when object is excluded, remove it
                $hailObj->delete();
            }

            if(isset($progressBar)){
                $progressBar->advance();
            }
        }
        if ($org && $job) {
            //Remove all object for which we don't have reference
            if (count($hailIdList) > 0) {
                static::get()->filter('OrganisationID', $org->ID)->exclude('HailID', $hailIdList)->removeAll();
            } else {
                static::get()->filter('OrganisationID', $org->ID)->removeAll();
            }
        }

        if(isset($progressBar)){
            $progressBar->finish();
            echo PHP_EOL;
        }
    }

    /**
     * Fetch from Hail API for all configured organisations
     *
     * @throws
     */
    public static function fetchAll()
    {
        //Hail Api Client
        $hail_api_client = new Client();

        $config = SiteConfig::current_site_config();
        $orgs_ids = json_decode($config->HailOrgsIDs);
        if (!$orgs_ids) {
            //No organisations configured
            $hail_api_client->handleException(new \Exception("You need at least 1 Hail Organisation configured to be able to fetch"));

            return false;
        }
        //Fetch objects for all configured Hail organisations
        foreach ($orgs_ids as $org_id) {
            self::fetchForOrg($hail_api_client, $org_id);
        }
    }

    /**
     * View permission
     */
    public function canView($member = false)
    {
        return true;
    }

    /**
     * Delete permission
     *
     * Always false, all Hail objects are readonly
     */
    public function canDelete($member = false)
    {
        return false;
    }

    /**
     * Create permission
     *
     * Always false, all Hail objects are readonly
     */
    public function canCreate($member = false, $context = [])
    {
        return false;
    }

    /**
     * Edit permission
     *
     * Always false, all Hail objects are readonly
     */
    public function canEdit($member = false)
    {
        return false;
    }

    /**
     * Helper function to add a ReadOnly gridfield for a relation
     *
     * @param FieldList $fields
     * @param string $name Name that should be given to the GridField
     * @param ManyManyList $relation Relation to display
     * @param string $viewComponent Full class name of the view Component to add (button)
     *
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

    /**
     * Go through the list of public tags and assign them to this object.
     *
     * @param array $data JSON data from Hail
     */
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

    /**
     * Go through the list of private tags and assign them to this object.
     *
     * @param array $data JSON data from Hail
     */
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

    /**
     * Match the hero image if there's one and assign it to this object
     *
     * @param array $heroImgData JSON data from Hail
     */
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

    /**
     * Match the hero video if there's one and assign it to this object
     *
     * @param array $heroVidData JSON data from Hail
     */
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

    /**
     * Go through the attachments and assign them to this object.
     *
     * @param array $data JSON data from Hail
     */
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
