<?php

namespace Firebrand\Hail\Models;

class Video extends ApiObject
{
    protected static $object_endpoint = "videos";
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

        'Created' => 'Date',
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
        'PublicTags' => 'Firebrand\Hail\Models\PublicTag',
        'PrivateTags' => 'Firebrand\Hail\Models\PrivateTag',
        'Articles' => 'Firebrand\Hail\Models\Article'
    ];
    private static $summary_fields = [
        'Organisation.Title' => 'Hail Organisation',
        'HailID' => 'Hail ID',
        'Thumbnail' => 'Thumbnail',
        'Caption' => 'Caption',
        'Date' => 'Date',
        'Fetched' => 'Fetched'
    ];

    /**
     * Renders out a thumbnail of this Hail Image.
     *
     * * @return HTMLText
     */
    public function getThumbnail()
    {
        switch ($this->Service) {
            case 'youtube':
                return $this->renderWith('VideoYouTubeThumbnail');
                break;
            default:
                return $this->renderWith('VideoThumbnail');
        }
    }

    public function getRelativeCenterX()
    {
        $pos = 50;
        if ($this->FaceCentreX > 0 && $this->OriginalWidth > 0) {
            $pos = $this->FaceCentreX / $this->OriginalWidth * 100;
            $pos = ($pos > 100 || $pos < 0) ? 50 : $pos;
        }

        return $pos;
    }

    public function getRelativeCenterY()
    {
        $pos = 50;
        if ($this->FaceCentreY > 0 && $this->OriginalHeight > 0) {
            $pos = $this->FaceCentreY / $this->OriginalHeight * 100;
            $pos = ($pos > 100 || $pos < 0) ? 50 : $pos;
        }

        return $pos;
    }

    public function getUrl()
    {
        return $this->Urloriginal;
    }

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

    protected function importingColor($SSName, $data)
    {
        $color = $this->{$SSName};
        if (!$color) {
            $color = new Color();
        }
        $color->OrganisationID = $this->OrganisationID;
        $color->HailOrgID = $this->HailOrgID;
        $color->HailOrgName = $this->HailOrgName;

        $this->{$SSName} = $color;
    }
}
