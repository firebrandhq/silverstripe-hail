<?php
/**
 * A button that allows a user to download an HailAttachment from the
 * Silverstripe backend.
 *
 */
class GridFieldHailAttachmentDownloadButton extends GridFieldViewButton {
	public function getColumnContent($field, $record, $col) {
		if($record->canView()) {
			$data = new ArrayData(array(
				'Link' => $record->Url
					
			));
			return $data->renderWith('HailAttachmentDownloadButton');
		}
	}
}

