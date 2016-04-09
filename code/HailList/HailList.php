<?php

class HailList extends DataObject {

	private static $db = array(
		'ListTitle' => 'Varchar',
		'ListDescription' => 'Text',
		'SortOrder' => 'Int',
		'Fetched' => 'Datetime'
	);

	private static $has_one = array(
		'Page' => 'SiteTree'
	);

	private static $api_access = true;

	private static $summary_fields = array(
		'Title', 'Description', 'Type'
	);

	public function getTitle() {
		return $this->ListTitle;
	}

	public function getDescription() {
		return $this->ListDescription;
	}

	public function getCMSFields( ) {
		$fields = parent::getCMSFields();

		$fields->removeFieldFromTab("Root.Main","SortOrder");
		$fields->removeByName('Fetched');
		$fields->removeByName('PageID');

		return $fields;
	}

	public function Articles() {
		#TODO Make the HailList fetch logic smarter
		#$this->fetch(); Deactivated for now.
		return HailArticle::get()->sort('Date', 'DESC');
	}

	public function MyPaginatedListArticles() {
		$plist = new PaginatedList($this->Articles(), Controller::curr()->getRequest());
		$plist->setPageLength(20);
		return $plist;
	}

	protected function fetch() {
		if (!$this->Fetched || HailApiObject::isOutdated($this->Fetched)) {
			$this->fetchMethod();
			$this->Fetched = date("Y-m-d H:i:s");
			$this->write();
		}

	}

	protected function fetchMethod() {
		HailArticle::fetch();
	}

	// Get a position and return the matching article in list
	// Adds the real position of the article in the list as an extra attribute
	public function Article($pos) {
		if ($this->Articles()) {
			$count = $this->Articles()->Count();
			$pos = (int)$pos % (int)$count;
			$article = $this->Articles(1, $pos)->First();
			$article->Position = $pos;
			return $article;
		}
	}

	public function canView($member=null) {
		return true;
	}

	public function getLink() {
		/*if ($this->Page()->ID == 1) {
			return $this->Page()->getLiveURLSegment() . '/HailList/' . $this->ID;
		} else {*/
			return $this->Page()->Link('list') . '/' . $this->ID;
		//}
	}

	public function Type() {
		return 'Standard List';
	}

	public function forTemplate() {
		return $this->renderWith($this->defaultTemplate());
	}

	protected function defaultTemplate() {
		return array('HailList');
	}

	public function defaultFullListingTemplate() {

	}

	/**
	 * Return a list of all class subclass of HailList, including HailList
	 * @return array
	 */
	public static function getSubClasses() {
		return ClassInfo::subclassesFor('HailList');
	}

}
