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
 * @property string $Status
 * @property string $ToFetch
 * @property string $CurrentObject
 * @property int $CurrentDone
 * @property int $CurrentTotal
 * @property int $GlobalDone
 * @property int $GlobalTotal
 */
class FetchJob extends DataObject
{
    private static $table_name = "HailFetchJob";
    private static $db = [
        'Status' => 'Enum(array("Starting","Running","Done","Error"))',
        'ToFetch' => 'Varchar',
        'CurrentObject' => 'Varchar',
        'CurrentDone' => 'Int',
        'CurrentTotal' => 'Int',
        'GlobalDone' => 'Int',
        'GlobalTotal' => 'Int',
        'ErrorShown' => 'Boolean',
    ];
}
