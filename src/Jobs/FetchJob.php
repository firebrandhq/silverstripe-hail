<?php

namespace Firebrand\Hail\Jobs;

use SilverStripe\ORM\DataObject;

/**
 * DataObject storing all Fetch jobs and their progress when running
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 *
 */
class FetchJob extends DataObject
{
    private static $table_name = "HailFetchJob";
    private static $db = [
        'Status' => 'Enum(array("Starting","Running","Done"))',
        'ToFetch' => 'Varchar',
        'CurrentObject' => 'Varchar',
        'CurrentDone' => 'Int',
        'CurrentTotal' => 'Int',
        'GlobalDone' => 'Int',
        'GlobalTotal' => 'Int',
    ];
}
