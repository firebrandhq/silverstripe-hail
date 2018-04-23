<?php

namespace Firebrand\Hail\Models;

use SilverStripe\View\ArrayData;

class Image extends ApiObject
{
    protected static $object_endpoint = "images";
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

        'Created' => 'Date',
        'Rating' => 'Double',
        'Flagged' => 'Boolean',

        'FaceCentreX' => 'Int',
        'FaceCentreY' => 'Int',
        'OriginalWidth' => 'Int',
        'OriginalHeight' => 'Int',
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
        $data = new ArrayData([
            'HailImage' => $this
        ]);

        return $data->renderWith('ImageThumbnail');
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
}
