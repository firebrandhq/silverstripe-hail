<?php

/**
 * Abtract representation of an Hail Image retrieved via the Hail API.
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 * @property string Caption
 * @property string People
 * @property SS_Datetime Date
 * @property string Location
 * @property string Photographer
 * @property string Status
 * @property SS_Date Created
 * @property double Rating
 * @property boolean Flagged
 *
 * @property string Url150Square
 * @property string Url500
 * @property string Url500Square
 * @property string Url1000
 * @property string Url1000Square
 * @property string Url2000
 * @property string Urloriginal
 *
 * @method ManyManyList Tags() List of {@link HailTag}
 * @method ManyManyList Articles() List of {@link HailArticle}
 */
class HailImage extends HailApiObject {

	private static $db = array(
		'Caption' => 'Varchar',
		'People' => 'Varchar',
		'Location' => 'Varchar',
		'Date' => 'Date',
		'Photographer' => 'Varchar',
		'Status' => 'Varchar',

		'Url150Square' => 'Text',
		'Url500' => 'Text',
		'Url500Square' => 'Text',
		'Url1000' => 'Text',
		'Url1000Square' => 'Text',
		'Url2000' => 'Text',
		'Urloriginal' => 'Text',

		'Created' => 'Date',
		'Rating' => 'Double',
		'Flagged' => 'Boolean',

		'FaceCentreX' => 'Int',
		'FaceCentreY' => 'Int',
		'OriginalWidth' => 'Int',
		'OriginalHeight' => 'Int',
	);

	private static $belongs_many_many = array(
		'Tags' => 'HailTag',
		'Articles' => 'HailArticle'
	);

	private static $summary_fields = array(
		'Organisation.Title' => 'Hail Organisation',
		'HailID' => 'Hail ID',
		'Thumbnail' => 'Thumbnail',
		'Caption' => 'Caption',
		'Date' => 'Date',
		'Fetched' => 'Fetched'
	);

	private static $api_access = true;

	protected static function getObjectType() {
		return HailApi::IMAGES;
	}

	protected function importing($data) {

        $this->processTags($data->tags, $data->private_tags);

		// Import face position data
		if (empty($data->focal_point)) {
			$this->FaceCentreX = -1;
			$this->FaceCentreY = -1;
		} else {
			$this->FaceCentreX = empty($data->focal_point->x) ? -1 : $data->focal_point->x;
			$this->FaceCentreY = empty($data->focal_point->y) ? -1 : $data->focal_point->y;
		}
	}

	protected static function apiMap() {
		return array(
			'Caption' => 'caption',
			'People' => 'people',
			'Location' => 'location',
			'Date' => 'date',
			'Photographer' => 'photographer',
			'Status' => 'status',

			'Url150Square' => 'file_150_square_url',
			'Url500' => 'file_500_url',
			'Url500Square' => 'file_500_square_url',
			'Url1000' => 'file_1000_url',
			'Url1000Square' => 'file_1000_square_url',
			'Url2000' => 'file_2000_url',
			'Urloriginal' => 'file_original_url',

			'OriginalWidth' => 'original_width',
			'OriginalHeight' => 'original_height',

			'Rating' => 'average_rating',
			'Flagged' => 'flagged',
		);
	}

	/**
	 * Renders out a thumbnail of this Hail Image.
	 *
	 * * @return HTMLText
	 */
	public function getThumbnail() {
		$data = new ArrayData(array(
			'HailImage' => $this
		));

		return $data->renderWith('HailImageThumbnail');
	}

	public function getRelativeCenterX() {
		$pos = 50;
		if ($this->FaceCentreX > 0 && $this->OriginalWidth > 0) {
			$pos = $this->FaceCentreX / $this->OriginalWidth * 100;
			$pos = ($pos > 100 || $pos < 0) ? 50 : $pos;
		}

		return $pos;
	}

	public function getRelativeCenterY() {
		$pos = 50;
		if ($this->FaceCentreY > 0 && $this->OriginalHeight > 0) {
			$pos = $this->FaceCentreY / $this->OriginalHeight * 100;
			$pos = ($pos > 100 || $pos < 0) ? 50 : $pos;
		}

		return $pos;
	}

	public function getCMSFields( ) {
		$fields = parent::getCMSFields();

		// Display relations
		$this->makeRecordViewer($fields, "Tags", $this->Tags());
		$this->makeRecordViewer($fields, "Articles", $this->Articles());

		// Hide all those URL
		$fields->removeByName('Url150Square');
		$fields->removeByName('Url500');
		$fields->removeByName('Url500Square');
		$fields->removeByName('Url1000');
		$fields->removeByName('Url1000Square');
		$fields->removeByName('Url2000');
		$fields->removeByName('Urloriginal');

		$fields->removeByName('FaceCentreX');
		$fields->removeByName('FaceCentreY');

		// Display a thumbnail
		$heroField = new LiteralField (
			"Thumbnail",
			$this->getThumbnail()
		);
		$fields->addFieldToTab('Root.Main',$heroField);

		return $fields;
	}

	public function getUrl() {
		return $this->Urloriginal;
	}

}
