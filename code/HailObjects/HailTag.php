<?php

class HailTag extends HailApiObject {

	private static $db = array(
		'Name' => 'Varchar',
		'Description' => 'Varchar'
	);

	private static $many_many = array(
		'Articles' => 'HailArticle',
		'Images' => 'HailImage',
		'Publications' => 'HailPublication',
		'Videos' => 'HailVideo',
	);

	private static $belongs_many_many = array(
		'TagLists' => 'TagHailList'
	);

	private static $searchable_fields = array(
		'Name','Description'
	);

	private static $api_access = true;

	private static $summary_fields = array(
		'Organisation.Title' => 'Hail Organisation',
		'HailID' => 'Hail ID',
		'Name' => 'Name',
		'Description' => 'Description',
		'Fetched' => 'Fetched'
	);

	public function importHailData($data) {
		$this->Name = $data->name;
		$this->Description = $data->description;
		return parent::importHailData($data);
	}

	public function getCMSFields( ) {
		$fields = parent::getCMSFields();

		$this->makeRecordViewer($fields, "Articles", $this->Articles());
		$this->makeRecordViewer($fields, "Images", $this->Images());

		return $fields;
	}

	protected function refreshing() {
		$this->fetchArticles();
	}

	public function canView($member=null) {
		return true;
	}

	public function fetchArticles() {
		try {
			$list = HailApi::getArticlesByTag($this->HailID, HailOrganisation::get()->byID($this->OrganisationID));
		} catch (HailApiException $ex) {
			Debug::warningHandler(E_WARNING, $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTrace());
			return;
		}

		$hailIdList = array();

		foreach($list as $hailData) {
			// Build up Hail ID list
			$hailIdList[] = $hailData->id;

			// Check if we can find an existing item.
			$hailObj = HailArticle::get()->filter(array('HailID' => $hailData->id))->First();
			if (!$hailObj) {
				$hailObj = new HailArticle();
			}
			$hailObj->importHailData($hailData);
			$this->Articles()->add($hailObj);
		}

		$this->Articles()->exclude('HailID', $hailIdList)->removeAll();
	}

}
