<?php

namespace Firebrand\Hail\Extensions;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataExtension;

class DBStringExtension extends DataExtension
{
    public function CSSSafe()
    {
        return Convert::raw2url($this->owner->value);
    }

}