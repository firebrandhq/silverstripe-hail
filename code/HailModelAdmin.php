<?php

class HailModelAdmin extends ModelAdmin {
	private static $menu_icon = 'hail/images/admin-icon.png';
	private static $menu_title = 'Hail integration';
	private static $url_segment = 'hail'; // Linked as /admin/products/
	
	private static $managed_models = array('HailTag', 'HailArticle', 'HailImage', 'HailPublication');
	
	private static $allowed_actions = array(
		'fetchForm'
	);
	
	public $showImportForm = false;
	
	/*private static $allowed_actions = array(
		'ImportForm',
		'SearchForm',
	);*/
	
	private static $url_handlers = array(
		'$ModelClass/$Action' => 'handleAction'
	);
	
	
	public function getEditForm($id = null, $fields = null) {
	
		$form = parent::getEditForm($id, $fields);

		$gridFieldName = $this->sanitiseClassName($this->modelClass);
		$gridField = $form->Fields()->fieldByName($gridFieldName)->getConfig();
		
		$gridField
			->removeComponentsByType('GridFieldAddNewButton')
			->removeComponentsByType('GridFieldDeleteAction')
			->removeComponentsByType('GridFieldExportButton')
			->removeComponentsByType('GridFieldPrintButton')
			->addComponent(new GridFieldHailFetchButton());
		
		if ($this->modelClass=='HailArticle') {
			$gridField->addComponent(new GridFieldHailFetchArticlesButton());
		}

		return $form;
	}
	
}
