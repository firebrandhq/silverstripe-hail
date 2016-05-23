<?php
/**
 * A Gridfiled button that allows a user to download a {@link HailAttachment} from the Silverstripe backend.
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
