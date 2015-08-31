<?php

/**
 * Abtract representation of an Hail Video retrieved via the Hail API.
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 * @property string Caption
 * @property string Description
 * @property SS_Datetime Date
 * @property string Videographer
 * @property string Status
 * @property string Service
 * @property string ServiceData
 * @property SS_Date Created
 * @property double Rating
 *
 * @property string Url150Square
 * @property string Url500
 * @property string Url500Square
 * @property string Url1000
 * @property string Url1000Square
 * @property string Url2000
 * @property string Urloriginal
 *
 * @property Int FaceCentreX
 * @property Int FaceCentreY
 * @property Int OriginalWidth
 * @property Int OriginalHeight
 *
 * @property HailColor Background
 * @property HailColor Primary
 * @property HailColor Secondary
 * @property HailColor Detail
 *
 * @method ManyManyList Tags() List of {@link HailTag}
 * @method ManyManyList Articles() List of {@link HailArticle}
 */
class HailVideo extends HailApiObject {

	private static $db = array(
		'Caption' => 'Varchar',
		'Description' => 'Text',
		'Date' => 'Date',
		'Videographer' => 'Varchar',
		'Status' => 'Varchar',
		'Service' => 'Varchar',
		'ServiceData' => 'Varchar',
		'IsPreviewOverridden' => 'Boolean',

		'Url150Square' => 'Text',
		'Url500' => 'Text',
		'Url500Square' => 'Text',
		'Url1000' => 'Text',
		'Url1000Square' => 'Text',
		'Url2000' => 'Text',
		'Urloriginal' => 'Text',

		'Created' => 'Date',
		'Rating' => 'Double',

		'FaceCentreX' => 'Int',
		'FaceCentreY' => 'Int',
		'OriginalWidth' => 'Int',
		'OriginalHeight' => 'Int',
	);

	private static $has_one = array(
		'Background' => 'HailColor',
		'Primary' => 'HailColor',
		'Secondary' => 'HailColor',
		'Detail' => 'HailColor',
	);


	private static $belongs_many_many = array(
		'Tags' => 'HailTag',
		'Articles' => 'HailArticle'
	);

	private static $summary_fields = array(
		'HailID',
		'Thumbnail',
		'Caption',
		'Date',
		'Fetched'
	);

	private static $api_access = true;

	protected static function getObjectType() {
		return HailApi::VIDEOS;
	}

	protected function importing($data) {
		// Process Tags
		$tagIdList = array();
		foreach ($data->tags as $tagData) {
			$tagIdList[] = $tagData->id;

			$tag = HailTag::get()->filter(array('HailID' => $tagData->id))->first();

			if (!$tag) {
				$tag = new HailTag();
			}

			$tag->importHailData($tagData);

			if (!$this->Tags()->byID($tag->ID)) {
				$this->Tags()->add($tag);
			}
		}

		// Remove old tags
		$this->Tags()->exclude('HailID', $this->HailID)->removeAll();

		$preview = $data->preview;

		$this->Url150Square = $preview->file_150_square_url;
		$this->Url500 = $preview->file_500_url;
		$this->Url500Square = $preview->file_500_square_url;
		$this->Url1000 = $preview->file_1000_url;
		$this->Url1000Square = $preview->file_1000_square_url;
		$this->Url2000 = $preview->file_2000_url;
		$this->Urloriginal = $preview->file_original_url;

		$this->OriginalWidth = $preview->original_width;
		$this->OriginalHeight = $preview->original_height;

		// Import face position data
		if (empty($preview->focal_point)) {
			$this->FaceCentreX = -1;
			$this->FaceCentreY = -1;
		} else {
			$this->FaceCentreX = empty($preview->focal_point->x) ? -1 : $preview->focal_point->x;
			$this->FaceCentreY = empty($previewata->focal_point->y) ? -1 : $preview->focal_point->y;
		}

		$this->importingColor('Background', $preview->colour_palette->background);
		$this->importingColor('Primary', $preview->colour_palette->primary);
		$this->importingColor('Secondary', $preview->colour_palette->secondary);
		$this->importingColor('Details', $preview->colour_palette->detail);
	}

	protected function importingColor($SSName, $data) {
		$color = $this->$SSName;
		if (!$color) {
			$color = new HailColor();
		}

		$color->import($data);
		$this->$SSName = $color;
	}

	protected static function apiMap() {
		return array(
			'Caption' => 'caption',
			'Description' => 'description',
			'Date' => 'date',
			'Videographer' => 'videographer',
			'Status' => 'status',
			'Service' => 'service',
			'ServiceData' => 'service_data',
			'IsPreviewOverridden' => 'is_preview_overridden',


			'Rating' => 'average_rating',
		);
	}

	/**
	 * Renders out a thumbnail of this Hail Image.
	 *
	 * * @return HTMLText
	 */
	public function getThumbnail() {
		return $this->renderWith('HailVideoThumbnail');
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
