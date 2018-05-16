<?php

namespace Firebrand\Hail\Forms;

use SilverStripe\Forms\GridField\GridFieldViewButton;
use SilverStripe\View\ArrayData;

/**
 * GriedField component to download a Hail Attachment from the CDN
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 *
 */
class GridFieldAttachmentDownloadButton extends GridFieldViewButton
{
    public function getColumnContent($field, $record, $col)
    {
        if ($record->canView()) {
            $data = new ArrayData([
                'Link' => $record->Url

            ]);
            return $data->renderWith('GridFieldAttachmentDownloadButton');
        }
    }
}

