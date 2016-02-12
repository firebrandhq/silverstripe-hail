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


		$btnLabel = _t(
			'Hail',
			'Fetch {type}',
			['type' => singleton($gridField->getModelClass())->i18n_plural_name()]
		);

		$button = new GridField_FormAction(
			$gridField,
			'fetchhail',
			$btnLabel,
			'fetchhail',
			null
		);

		$button->setAttribute('data-icon', 'fetchHail');
		// $button->addExtraClass('ss-ui-action-constructive');

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
