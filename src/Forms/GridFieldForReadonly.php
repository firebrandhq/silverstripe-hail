<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Forms\GridField\GridField;

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