<?php
/**
 * A Gridfiled button that allows a user to download a {@link HailAttachment} from the Silverstripe backend.
 *
 * @package Hail\Admin
 * @author Maxime Rainville <max@firebrand.nz>
 * @license <https://raw.githubusercontent.com/firebrandhq/silverstripe-hail/dev/LICENSE> The MIT License (MIT)
 * @copyright 2016 Firebrand Limited
 */
class GridFieldHailAttachmentDownloadButton extends GridFieldViewButton
{
    /**
     * {@inheritdoc}
     */
    public function getColumnContent($field, $record, $col)
    {
        if ($record->canView()) {
            $data = new ArrayData(array(
                'Link' => $record->Url,
            ));

            return $data->renderWith('HailAttachmentDownloadButton');
        }
    }
}
