<?php

namespace Firebrand\Hail\Models;

use SilverStripe\ORM\ManyManyList;

/**
 * Hail Public Tags DataObject
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $Name
 * @property string $Description
 *
 * @method ManyManyList Articles()
 * @method ManyManyList Images()
 * @method ManyManyList Videos()
 * @method ManyManyList HailPages()
 */
class PublicTag extends ApiObject
{
    /**
     * @inheritdoc
     */
    public static $object_endpoint = "tags";
    /**
     * @inheritdoc
     */
    protected static $api_map = [
        'Name' => 'name',
        'Description' => 'description',
    ];
    private static $table_name = "HailPublicTag";
    private static $db = [
        'Name' => 'Varchar',
        'Description' => 'Varchar',
    ];
    private static $many_many = [
        'Articles' => 'Firebrand\Hail\Models\Article',
        'Images' => 'Firebrand\Hail\Models\Image',
        'Videos' => 'Firebrand\Hail\Models\Video',
    ];
    private static $belongs_many_many = [
        'HailPages' => 'Firebrand\Hail\Pages\HailPage',
    ];
    private static $searchable_fields = [
        'Name',
        'Description'
    ];
    private static $summary_fields = [
        'Organisation.Title' => 'Hail Organisation',
        'HailID' => 'Hail ID',
        'Name' => 'Name',
        'Description' => 'Description',
        'Fetched' => 'Fetched'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $this->makeRecordViewer($fields, "Articles", $this->Articles());
        $this->makeRecordViewer($fields, "Images", $this->Images());
        $this->makeRecordViewer($fields, "Videos", $this->Videos());
        $fields->removeByName("HailPages");

        return $fields;
    }

    /**
     * Get Full name of the tag (Organisation prepended to tag name if exists)
     *
     * @return string
     */
    public function getFullName()
    {
        if (empty($this->HailOrgName)) {
            return $this->Name;
        }

        return $this->HailOrgName . " - " . $this->Name;
    }


}
