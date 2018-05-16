<?php

namespace Firebrand\Hail\Models;

use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\View\ArrayData;

/**
 * Hail Image DataObject
 *
 * Images are hosted on CloudFront
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $Caption
 * @property string $People
 * @property string $Location
 * @property string $Date
 * @property string $Photographer
 * @property string $Status
 * @property string $Url150Square
 * @property string $Url500
 * @property string $Url500Square
 * @property string $Url1000
 * @property string $Url1000Square
 * @property string $Url2000
 * @property string $Urloriginal
 * @property double $Rating
 * @property boolean $Flagged
 * @property int $FaceCentreX X axis for the Image focus point
 * @property int $FaceCentreY Y axis for the Image focus point
 * @property int $OriginalWidth
 * @property int $OriginalHeight
 *
 * @method ManyManyList Articles()
 * @method ManyManyList PublicTags()
 * @method ManyManyList PrivateTags()
 */
class Image extends ApiObject
{
    /**
     * @inheritdoc
     */
    public static $object_endpoint = "images";
    /**
     * @inheritdoc
     */
    protected static $api_map = [
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
    ];
    private static $table_name = "HailImage";
    private static $db = [
        'Caption' => 'Varchar',
        'People' => 'Varchar',
        'Location' => 'Varchar',
        'Date' => 'Date',
        'Photographer' => 'Varchar',
        'Status' => 'Varchar',

        'Url150Square' => 'Varchar',
        'Url500' => 'Varchar',
        'Url500Square' => 'Varchar',
        'Url1000' => 'Varchar',
        'Url1000Square' => 'Varchar',
        'Url2000' => 'Varchar',
        'Urloriginal' => 'Varchar',

        'Rating' => 'Double',
        'Flagged' => 'Boolean',

        'FaceCentreX' => 'Int',
        'FaceCentreY' => 'Int',
        'OriginalWidth' => 'Int',
        'OriginalHeight' => 'Int',
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

        // Display a thumbnail
        $heroField = new LiteralField(
            "Thumbnail",
            $this->getThumbnailField("Thumbnail")
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
        // Import face position data
        if (!isset($data['focal_point']) || empty($data['focal_point'])) {
            $this->FaceCentreX = -1;
            $this->FaceCentreY = -1;
        } else {
            $this->FaceCentreX = !isset($data['focal_point']['x']) || empty($data['focal_point']['x']) ? -1 : $data['focal_point']['x'];
            $this->FaceCentreY = !isset($data['focal_point']['y']) || empty($data['focal_point']['y']) ? -1 : $data['focal_point']['y'];
        }
    }

    /**
     * Renders the thumbnail of this Image
     *
     * @return DBHTMLText
     */
    public function getThumbnail()
    {
        $data = new ArrayData([
            'HailImage' => $this
        ]);

        return $data->renderWith('ImageThumbnail');
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
        return "<div class='form-group field lookup readonly '><label class='form__field-label'>$label</label><div class='form__field-holder'>{$this->getThumbnail()}</div></div>";
    }

    /**
     * Get the X axis for the relative center of this image
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
     * Get the Y axis for the relative center of this image
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
     * Get the original URL of this Image
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->Urloriginal;
    }
}
