<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Forms\GridField\GridFieldViewButton;
use SilverStripe\View\ArrayData;

class GridFieldAttachmentDownloadButton extends GridFieldViewButton
{
    public function getColumnContent($field, $record, $col)
    {
        if ($record->canView()) {
            $data = new ArrayData([
                'Link' => $record->Url

            ]);
            return $data->renderWith('AttachmentDownloadButton');
        }
    }
}

