<?php

class GridFieldHailFetchButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler {

	/**
	 * Fragment to write the button to
	 */
	protected $targetFragment;

	public function __construct($targetFragment = "before") {
		$this->targetFragment = $targetFragment;
	}

	/**
	 * Place the export button in a <p> tag below the field
	 */
	public function getHTMLFragments($gridField) {

		$button = new GridField_FormAction(
			$gridField,
			'fetchhail',
			_t('Hail', 'Fetch'),
			'fetchhail',
			null
		);
		//$button->setAttribute('data-icon', 'download-csv');
		//$button->addExtraClass('no-ajax');
		return array(
			$this->targetFragment => $button->Field(),
		);
	}

	/**
	 * export is an action button
	 */
	public function getActions($gridField) {
		return array('fetchhail');
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'fetchhail') {
			return $this->handleFetchHail($gridField);
		}
	}

	/**
	 * it is also a URL
	 */
	public function getURLHandlers($gridField) {
		return array(
			'fetchhail' => 'handleFetchHail',
		);
	}

	/**
	 * Handle the export, for both the action button and the URL
 	 */
	public function handleFetchHail($gridField, $request = null) {
		singleton('QueuedJobService')->queueJob(new HailFetchQueueJob($gridField->getModelClass()));
	}
}
