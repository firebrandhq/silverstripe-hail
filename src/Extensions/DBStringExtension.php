<?php

namespace Firebrand\Hail\Extensions;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataExtension;

/**
 * DBString Extension
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 *
 */
class DBStringExtension extends DataExtension
{
    /**
     * Transform the DB String to a CSS Safe string usable in template and element classes / IDs
     *
     * @return string
     */
    public function CSSSafe()
    {
        return Convert::raw2url($this->owner->value);
    }

}