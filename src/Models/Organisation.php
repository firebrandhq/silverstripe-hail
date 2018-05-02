<?php

namespace Firebrand\Hail\Models;

use SilverStripe\ORM\DataObject;

class Organisation extends DataObject
{
    private static $table_name = "HailOrganisation";

    private static $db = [
        "HailID" => "Varchar",
        "Title" => "Varchar",
    ];

    private static $belongs_many_many = [
        'HailLists' => 'Firebrand\Hail\Lists\HailList',
    ];
}