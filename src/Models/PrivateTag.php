<?php

namespace Firebrand\Hail\Models;

use SilverStripe\ORM\ManyManyList;

/**
 * Hail Private Tags DataObject
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 *
 * @property string $Name
 * @property string $Description
 *
 * @method ManyManyList Articles()
 * @method ManyManyList Publications()
 * @method ManyManyList Images()
 * @method ManyManyList Videos()
 */
class PrivateTag extends ApiObject
{
    /**
     * @inheritdoc
     */
    public static $object_endpoint = "private-tags";
    /**
     * @inheritdoc
     */
    protected static $api_map = [
        'Name' => 'name',
        'Description' => 'description',
    ];
    private static $table_name = "HailPrivateTag";
    private static $many_many = [
        'Articles' => 'Firebrand\Hail\Models\Article',
        'Publications' => 'Firebrand\Hail\Models\Publication',
        'Images' => 'Firebrand\Hail\Models\Image',
        'Videos' => 'Firebrand\Hail\Models\Video',
    ];
    private static $db = [
        'Name' => 'Varchar',
        'Description' => 'Varchar',
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
        $this->makeRecordViewer($fields, "Publications", $this->Publications());
        $this->makeRecordViewer($fields, "Images", $this->Images());
        $this->makeRecordViewer($fields, "Videos", $this->Videos());

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
