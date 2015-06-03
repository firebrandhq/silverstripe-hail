<?php

class GridFieldHailFetchArticlesButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler {
	
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
			'fetchhailarticle', 
			_t('Hail', 'Fetch article content'),
			'fetchhailarticle', 
			null
		);

		return array(
			$this->targetFragment => $button->Field(),
		);
	}

	/**
	 * export is an action button
	 */
	public function getActions($gridField) {
		return array('fetchhailarticle');
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'fetchhailarticle') {
			return $this->handleFetchHailArticle($gridField);
		}
	}

	/**
	 * it is also a URL
	 */
	public function getURLHandlers($gridField) {
		return array(
			'fetchhailarticle' => 'handleFetchHailArticle',
		);
	}

	/**
	 * Handle the export, for both the action button and the URL
 	 */
	public function handleFetchHailArticle($gridField, $request = null) {
		HailArticle::fetchAll();
	}
}
