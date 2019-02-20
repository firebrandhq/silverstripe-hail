<?php

/**
 * Abtract representation of an Hail Api object.
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 * @property string HailID Unique identifier from Hail
 * @property Datetime Fetched Last time we got a copy of this object from Hail
 */

class HailApiObject extends DataObject
{

    // Make HailApiObject accessible via SilverStripe Restful API
    private static $api_access = true;

    // Make sure we index our HailID to make search quicker
    private static $indexes = [
        'HailID' => true
    ];


    private static $db = [
        'HailID' => 'Varchar',
        'Fetched' => 'Datetime'
    ];

    private static $has_one = [
        'Organisation' => 'HailOrganisation'
    ];

    private static $summary_fields = [
        'HailID',
        'Fetched'
    ];

    /**
     * Retrieves all Hail Api Object of a specific type
     *
     * @param HailOrganisation The Hail organisation
     * @return void
     */
    public static function fetch(HailOrganisation $org, $only_recent = false)
    {
        try {
            $list = HailApi::getList(static::getObjectType(), $org, $only_recent);
        } catch (HailApiException $ex) {
            Debug::warningHandler(E_WARNING, $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTrace());
            die($ex->getMessage());
        }

        //Console display
        $is_cli = php_sapi_name() == "cli";
        if ($is_cli) {
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            if (count($list) > 0) {
                $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, count($list));
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | elapsed: %elapsed:6s% | remaining: %estimated:-6s%');
                $progressBar->start();
            } else {
                $output->write("Nothing to fetch.", true);
            }
        }

        $idList = [];

        foreach ($list as $hailData) {
            // Check if we can find an existing item.
            $hailObj = static::get()->filter(['HailID' => $hailData->id])->First();
            if (!$hailObj) {
                $hailObj = new static();
            }
            $hailObj->OrganisationID = $org->ID;
            $hailObj->write();

            $result = $hailObj->importHailData($hailData);
            if ($result) {
                //Build up Hail ID list
                $idList[] = $hailObj->ID;
            } else {
                //Remove object when it's excluded
                $hailObj->delete();
            }

            if (isset($progressBar)) {
                $progressBar->advance();
            }
        }

        //No cleanup when only fetching recent articles
        if (!$only_recent && count($idList) > 0) {
            //Clean up for deleted items, in raw query to avoid looping using removeAll()
            $classes = ClassInfo::subclassesFor(static::class);
            if (is_array($classes) && isset($classes[static::class])) {
                $table_name = $classes[static::class];
                //Select ids that are not in the list but beong to the current organisation
                $invalid_ids = DB::query("SELECT ID FROM HailApiObject WHERE ID NOT IN(" . implode(',',
                        $idList) . ") AND ClassName = '" . static::class . "' AND OrganisationID = " . $org->ID . " ")->column('ID');
                if (count($invalid_ids) > 0) {
                    //Remove items from child table
                    DB::query("DELETE FROM $table_name WHERE ID IN(" . implode(',', $invalid_ids) . ")");
                    //Remove items from base table
                    DB::query("DELETE FROM HailApiObject WHERE ID IN(" . implode(',', $invalid_ids) . ") AND ClassName = '" . static::class . "'");
                }
            }
        }

        if (isset($progressBar)) {
            $progressBar->finish();
            echo PHP_EOL;
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

    /**
     * Returns the name of the HailApiObject this class represents.
     *
     * This method needs to be overwritten on children of HailApiObject.
     *
     * It should never be called directly on HailApiObject. It will throw a
     * {@link HailApiException} if called directly.
     *
     * @return string Name of the hail object
     */
    protected static function getObjectType()
    {
        throw new HailApiException('getObjectType() must be redefined in overwritten in children class of HailApiObject.');
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
            static::apiMap()
        );

        foreach ($dataMap as $ssKey => $hailKey) {
            $value = empty($data->$hailKey) ? '' : $data->$hailKey;
            //Remove Non UTF8
            $value = preg_replace('/[^(\x20-\x7F)]*/', '', $value);
            //Decode HTML encoded
            $value = html_entity_decode($value);

            $this->$ssKey = $value;
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
     * Can be extened from to exclude tags as required
     *
     * @param StdClass $data JSON data from Hail
     * @return boolean
     */
    protected function excluded($data)
    {
        $results = $this->extend('excluded', $data);
        if ($results && is_array($results)) {
            if (max($results)) return true;
            else return false;
        }

        return false;
    }

    /**
     * Allows children class to provide an import map to import scalar values
     * from hail. Must return an array where the keys are the name of the
     * property on the HailApiObject and the value is the name of the value on
     * the Hail JSON response.
     *
     * * @return array
     */
    protected static function apiMap()
    {
        return [];
    }

    /**
     * Retrieve the latest version of this object if it's outdated.
     *
     * @return HailApiObject
     */
    public function softRefresh()
    {
        if ($this->outdated() && $this->HailID) {
            $this->refresh();
        }
        return $this;
    }

    /**
     * Determines if a fetched date is outdated.
     *
     * @param mixed $fetched Date to evaluate.
     * @return boolean
     */
    public static function isOutdated($fetched)
    {
        if ($fetched) {
            // Check if $fetched is an object
            if (!is_object($fetched)) {
                $dateime = new SS_Datetime();
                $dateime->setValue($fetched);
                $fetched = $dateime;
            }

            if ($fetched->TimeDiff() > HailApi::getRefreshRate()) {
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Determines if this object is outdated and needs to be refreshed.
     *
     * @return boolean
     */
    public function outdated()
    {
        return $this->isOutdated($this->Fetched);
    }

    /**
     * Retrieves the latest version of this object whatever it's outdated or not.
     *
     * @return HailApiObject
     */
    public function refresh()
    {
        if ($this->ID && $this->HailID) {
            try {
                $data = HailApi::getOne(static::getObjectType(), $this->HailID, HailOrganisation::get()->byID($this->OrganisationID));
            } catch (HailApiException $ex) {
                Debug::warningHandler(E_WARNING, $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTrace());
                return $this;
            }

            $this->importHailData($data);
            $this->refreshing();
        }

        return $this;
    }

    /**
     * Is called by {@link refresh()} to allow children classes to perform extra action.
     *
     * @return void
     */
    protected function refreshing()
    {
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
    function canCreate($member = false)
    {
        return false;
    }

    // Make all fields readonly
    // We don't want to overrite canEdit to always return false, otherwise our
    // Record viewer will look ugly.
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $this->makeFieldReadonly($fields);
        return $fields;
    }

    public function validate()
    {
        $results = parent::validate();

        if (!$this->OrganisationID) {
            $results->error('Hail organisation ID is required');
        }

        return $results;
    }

    // Recursively go through all our fields and turn them off.
    private function makeFieldReadonly($fields)
    {
        if ($fields->children) {
            $fields = $fields->children;
        }

        foreach ($fields as $i => $item) {
            if ($item->isComposite()) {
                $this->makeFieldReadonly($item);
            } else {
                $fields->replaceField($item->getName(), $item->transform(new ReadonlyTransformation()));
            }
        }
        $fields->sequentialSet = null;
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
    protected function makeRecordViewer($fields, $name, $relation, $viewComponent = 'GridFieldHailViewButton')
    {

        $config = GridFieldConfig_RecordViewer::create();

        // Remove the standard GridFieldView button and replace it with our
        // custom button that will link to our the right action in our HailModelAdmin
        $config->removeComponentsByType('GridFieldViewButton');
        $config->addComponents(new $viewComponent());

        $grid = new GridField($name, $name, $relation, $config);
        $fields->addFieldToTab('Root.' . $name, $grid);
    }

}
