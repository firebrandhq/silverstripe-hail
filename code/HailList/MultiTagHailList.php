<?php

class MultiTagHailList extends HailList {

	private static $db = array(
	);

	private static $many_many = array(
		'Tags' => 'HailTag',
	);

	private static $api_access = true;

	private static $summary_fields = array(
		'Title', 'Type'
	);

	public function getTitle() {
		return (!empty(parent::getTitle())) ? parent::getTitle() : implode(', ', $this->Tags()->column('Name'));
	}

	public function getDescription() {
		return (!empty(parent::getDescription())) ? parent::getDescription() : implode(', ', $this->Tags()->column('Description'));
	}

	public function getCMSFields( ) {
		$fields = parent::getCMSFields();
		$fields->removeByName('Tags');

		foreach(HailTag::get() as $tag) {
			$tags[$tag->ID] = function_exists('Organisation') ? $tag->Organisation()->Title . ' - ' . $tag->Name : $tag->Name;
		}

		asort($tags);

		$fields->addFieldToTab('Root.Main', ListboxField::create('Tags', 'Tags')
			->setSource($tags)
			->setDefaultItems($this->Tags()->column('ID'))
			->setMultiple(true)
		);

		return $fields;
	}

	public function onAfterWrite() {
		$this->updateManyManyComponents('Tags', $this->Tags || isset($_POST['Tags']) ? $_POST['Tags'] : null);

		parent::onAfterWrite();
	}

	public function Articles() {
		if(count($this->Tags()) > 0) {

			$list = new ArrayList();
			foreach($this->Tags() as $tag) {
				$list->merge($tag->Articles());
			}

			$list->removeDuplicates();

			return $list->sort('Date', 'DESC');
		}
		
		return HailArticle::get()->sort('Date', 'DESC');
	}

	protected function fetchMethod() {
		$this->Tag()->refresh();
	}

	public function Type() {
		return 'Multi Tag List';
	}

	/**
	 * Updates the many to many relationships for the ListDropdownFields
	 * @param string $relationName Name of the relationship to process
	 */
	public function updateManyManyComponents($relationName, $ids) {
		$components = $this->getManyManyComponents($relationName);
		$components->setByIDList($ids);
	}

}
