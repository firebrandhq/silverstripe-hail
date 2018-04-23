<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Forms\GridField\GridFieldViewButton as SS_GridFieldViewButton;
use SilverStripe\View\ArrayData;

class GridFieldViewButton extends SS_GridFieldViewButton
{
    public function getColumnContent($field, $record, $col)
    {
        if ($record->canView()) {
            $data = new ArrayData([
                'Link' => singleton('Firebrand\Hail\Admin\HailModelAdmin')->Link(
                    str_replace("\\", "-", $record->ClassName) .
                    '/EditForm/field/' .
                    str_replace("\\", "-",$record->ClassName) .
                    '/item/' . $record->ID)

            ]);
            return $data->renderWith('GridFieldViewButton');
        }
    }
}

