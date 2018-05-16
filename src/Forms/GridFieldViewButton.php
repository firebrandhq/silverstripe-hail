<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Forms\GridField\GridFieldViewButton as SS_GridFieldViewButton;
use SilverStripe\View\ArrayData;

/**
 * GriedField component to redirect the user to the gridfield item
 *
 * Used when the gridfield is readonly
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 *
 */
class GridFieldViewButton extends SS_GridFieldViewButton
{
    public function getColumnContent($field, $record, $col)
    {
        if ($record->canView()) {
            $data = new ArrayData([
                'Link' => singleton('Firebrand\Hail\Admin\HailModelAdmin')->Link(
                    str_replace("\\", "-", $record->ClassName) .
                    '/EditForm/field/' .
                    str_replace("\\", "-", $record->ClassName) .
                    '/item/' . $record->ID)

            ]);
            return $data->renderWith('GridFieldViewButton');
        }
    }
}

