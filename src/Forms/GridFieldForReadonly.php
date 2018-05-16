<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Forms\GridField\GridField;

/**
 * Read Only GridField (not yet in SS4 Core)
 *
 * Taken from https://github.com/silverstripe/silverstripe-framework/issues/3357
 *
 * @package silverstripe-hail
 * @author sunnysideup
 * @version 1.0
 *
 */
class GridFieldForReadonly extends GridField
{
    /**
     * Returns a readonly version of this field
     * @return GridField
     */
    public function performReadonlyTransformation()
    {
        $this->getConfig()
            ->removeComponentsByType("GridFieldDeleteAction")
            ->removeComponentsByType("GridFieldAddExistingAutocompleter")
            ->removeComponentsByType("GridFieldAddNewButton");

        return $this;
    }
}