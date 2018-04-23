<?php

namespace Firebrand\Hail\Models;

class PublicTag extends ApiObject
{
    protected static $object_endpoint = "tags";
    protected static $api_map = [
        'Name' => 'name',
        'Description' => 'description',
    ];
    private static $table_name = "HailTag";
    private static $db = [
        'Name' => 'Varchar',
        'Description' => 'Varchar',
    ];
    private static $many_many = [
        'Articles' => 'Firebrand\Hail\Models\Article',
        'Images' => 'Firebrand\Hail\Models\Image',
        'Videos' => 'Firebrand\Hail\Models\Video',
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

    public function importHailData($data)
    {
        $this->Name = $data['name'];
        $this->Description = $data['description'];
        return parent::importHailData($data);
    }

}
