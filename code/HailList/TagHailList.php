<?php

class TagHailList extends HailList {

	private static $db = array(
	);

	private static $has_one = array(
		'Tag' => 'HailTag',
	);

	private static $api_access = true;

	private static $summary_fields = array(
		'Title', 'Type'
	);

	public function getTitle() {
		return (!empty(parent::getTitle())) ? parent::getTitle() : $this->Tag()->Name;
	}

	public function getDescription() {
		return (!empty(parent::getDescription())) ? parent::getDescription() : $this->Tag()->Description;
	}

	public function getCMSFields( ) {
		$fields = parent::getCMSFields();

		return $fields;
	}

	public function Articles() {
		if ($this->TagID) {
			$this->fetch();
			return $this->Tag()->Articles()->sort('Date', 'DESC');
		} else {
			return HailArticle::get()->sort('Date', 'DESC');
		}

	}

	protected function fetchMethod() {
		$this->Tag()->refresh();
	}

	public function Type() {
		return 'Tag List';
	}

}
