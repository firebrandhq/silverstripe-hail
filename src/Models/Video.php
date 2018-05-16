<?php

namespace Firebrand\Hail\Models;

use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\View\ArrayData;


/**
 * Hail Image DataObject
 *
 * Videos can be from Youtube or Vimeo
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $Caption
 * @property string $Description
 * @property string $Date
 * @property string $Videographer
 * @property string $Status
 * @property string $Service
 * @property string $ServiceData
 * @property double Rating
 * @property string $Url150Square
 * @property string $Url500
 * @property string $Url500Square
 * @property string $Url1000
 * @property string $Url1000Square
 * @property string $Url2000
 * @property string $Urloriginal
 * @property Int $FaceCentreX
 * @property Int $FaceCentreY
 * @property Int $OriginalWidth
 * @property Int $OriginalHeight
 *
 * @method Color Background()
 * @method Color Primary()
 * @method Color Secondary()
 * @method Color Detail()
 * @method ManyManyList PublicTags()
 * @method ManyManyList PrivateTags()
 * @method ManyManyList Articles()
 */
class Video extends ApiObject
{
    /**
     * @inheritdoc
     */
    public static $object_endpoint = "videos";
    /**
     * @inheritdoc
     */
    protected static $api_map = [
        'Caption' => 'caption',
        'Description' => 'description',
        'Date' => 'date',
        'Videographer' => 'videographer',
        'Status' => 'status',
        'Service' => 'service',
        'ServiceData' => 'service_data',
        'IsPreviewOverridden' => 'is_preview_overridden',
        'Rating' => 'average_rating',
    ];
    private static $table_name = "HailVideo";
    private static $db = [
        'Caption' => 'Varchar',
        'Description' => 'Text',
        'Date' => 'Date',
        'Videographer' => 'Varchar',
        'Status' => 'Varchar',
        'Service' => 'Varchar',
        'ServiceData' => 'Varchar',
        'IsPreviewOverridden' => 'Boolean',

        'Url150Square' => 'Varchar',
        'Url500' => 'Varchar',
        'Url500Square' => 'Varchar',
        'Url1000' => 'Varchar',
        'Url1000Square' => 'Varchar',
        'Url2000' => 'Varchar',
        'Urloriginal' => 'Varchar',

        'Rating' => 'Double',

        'FaceCentreX' => 'Int',
        'FaceCentreY' => 'Int',
        'OriginalWidth' => 'Int',
        'OriginalHeight' => 'Int',
    ];
    private static $has_one = [
        'Background' => 'Firebrand\Hail\Models\Color',
        'Primary' => 'Firebrand\Hail\Models\Color',
        'Secondary' => 'Firebrand\Hail\Models\Color',
        'Detail' => 'Firebrand\Hail\Models\Color',
    ];
    private static $belongs_many_many = [
        'Articles' => 'Firebrand\Hail\Models\Article',
        'PublicTags' => 'Firebrand\Hail\Models\PublicTag',
        'PrivateTags' => 'Firebrand\Hail\Models\PrivateTag',
    ];
    private static $summary_fields = [
        'Organisation.Title' => 'Hail Organisation',
        'HailID' => 'Hail ID',
        'Thumbnail' => 'Thumbnail',
        'Caption' => 'Caption',
        'Date' => 'Date',
        'Fetched' => 'Fetched'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Display relations
        $this->makeRecordViewer($fields, "Articles", $this->Articles());
        $this->makeRecordViewer($fields, "PublicTags", $this->PublicTags());
        $this->makeRecordViewer($fields, "PrivateTags", $this->PrivateTags());

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

        //Display the colors
        if ($this->Background()->ID != 0) {
            $bg_field = new LiteralField(
                "BackgroundID",
                $this->Background()->getThumnailField("Background")
            );
            $fields->replaceField('BackgroundID', $bg_field);
        }
        if ($this->Primary()->ID != 0) {
            $bg_field = new LiteralField(
                "PrimaryID",
                $this->Primary()->getThumnailField("Primary")
            );
            $fields->replaceField('PrimaryID', $bg_field);
        }
        if ($this->Secondary()->ID != 0) {
            $bg_field = new LiteralField(
                "SecondaryID",
                $this->Secondary()->getThumnailField("Secondary")
            );
            $fields->replaceField('SecondaryID', $bg_field);
        }
        if ($this->Detail()->ID != 0) {
            $bg_field = new LiteralField(
                "DetailID",
                $this->Detail()->getThumnailField("Detail")
            );
            $fields->replaceField('DetailID', $bg_field);
        }

        // Display a thumbnail
        $heroField = new LiteralField(
            "Thumbnail",
            $this->getThumbnailField('Thumbnail')
        );
        $fields->addFieldToTab('Root.Main', $heroField);

        return $fields;
    }

    /**
     * @inheritdoc
     */
    protected function importing($data)
    {
        $this->processPublicTags($data['tags']);
        $this->processPrivateTags($data['private_tags']);

        $preview = $data['preview'];

        $this->Url150Square = $preview['file_150_square_url'];
        $this->Url500 = $preview['file_500_url'];
        $this->Url500Square = $preview['file_500_square_url'];
        $this->Url1000 = $preview['file_1000_url'];
        $this->Url1000Square = $preview['file_1000_square_url'];
        $this->Url2000 = $preview['file_2000_url'];
        $this->Urloriginal = $preview['file_original_url'];

        $this->OriginalWidth = $preview['original_width'];
        $this->OriginalHeight = $preview['original_height'];

        // Import face position data
        if (!isset($preview['focal_point']) || empty($preview['focal_point'])) {
            $this->FaceCentreX = -1;
            $this->FaceCentreY = -1;
        } else {
            $this->FaceCentreX = !isset($preview['focal_point']['x']) || empty($preview['focal_point']['x']) ? -1 : $preview['focal_point']['x'];
            $this->FaceCentreY = !isset($preview['focal_point']['y']) || empty($preview['focal_point']['y']) ? -1 : $preview['focal_point']['y'];
        }

        $this->importingColor('Background', $preview['colour_palette']['background']);
        $this->importingColor('Primary', $preview['colour_palette']['primary']);
        $this->importingColor('Secondary', $preview['colour_palette']['secondary']);
        $this->importingColor('Details', $preview['colour_palette']['detail']);
    }

    /**
     * Process the colors
     *
     * @param $SSName
     * @param $data
     */
    protected function importingColor($SSName, $data)
    {
        $color = $this->{$SSName};
        if (!$color) {
            $color = new Color();
        }
        $this->Fetched = date("Y-m-d H:i:s");
        $color->OrganisationID = $this->OrganisationID;
        $color->HailOrgID = $this->HailOrgID;
        $color->HailOrgName = $this->HailOrgName;

        $this->{$SSName} = $color;
    }

    /**
     * Renders the thumbnail of this Video
     *
     * @return DBHTMLText
     */
    public function getThumbnail()
    {
        $data = new ArrayData([
            'HailVideo' => $this
        ]);

        return $data->renderWith('VideoThumbnail');
    }

    /**
     * Returns the thumbnail HTML for the CMS Field
     *
     * @param string $label
     *
     * @return string
     */
    public function getThumbnailField($label)
    {
        return "<div class='form-group field lookup readonly '><label class='form__field-label'>$label</label><div class='form__field-holder'><div class='hail-video-thumbnail-holder'> {$this->getThumbnail()} </div></div></div>";
    }


    /**
     * Get the X axis for the relative center of this video's still image
     *
     * @return int
     */
    public function getRelativeCenterX()
    {
        $pos = 50;
        if ($this->FaceCentreX > 0 && $this->OriginalWidth > 0) {
            $pos = $this->FaceCentreX / $this->OriginalWidth * 100;
            $pos = ($pos > 100 || $pos < 0) ? 50 : $pos;
        }

        return $pos;
    }

    /**
     * Get the Y axis for the relative center of this video's still image
     *
     * @return int
     */
    public function getRelativeCenterY()
    {
        $pos = 50;
        if ($this->FaceCentreY > 0 && $this->OriginalHeight > 0) {
            $pos = $this->FaceCentreY / $this->OriginalHeight * 100;
            $pos = ($pos > 100 || $pos < 0) ? 50 : $pos;
        }

        return $pos;
    }

    /**
     * Get the original URL of this video's still image
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->Urloriginal;
    }

    /**
     * Get the original URL of this video
     *
     * Can be from Youtube or Vimeo
     *
     * @return string
     */
    public function getLink()
    {
        switch ($this->Service) {
            case 'youtube':
                return '//www.youtube.com/watch?v=' . $this->ServiceData;
                break;
            case 'vimeo':
                return '//vimeo.com/' . $this->ServiceData;
                break;
            default:
                return $this->ServiceData;
        }
    }
}
