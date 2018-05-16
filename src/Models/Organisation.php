<?php

namespace Firebrand\Hail\Models;

use SilverStripe\ORM\DataObject;

/**
 * Hail Organisation DataObject
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $HailID
 * @property string $Title
 */
class Organisation extends DataObject
{
    private static $table_name = "HailOrganisation";

    private static $db = [
        "HailID" => "Varchar",
        "Title" => "Varchar",
    ];
}