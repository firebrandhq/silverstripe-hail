<?php

namespace Firebrand\Hail\Models;

use Firebrand\Hail\Api\Client;
use SilverStripe\Forms\LiteralField;

class Article extends ApiObject
{
    public static $object_endpoint = "articles";
    private static $api_access = true;
    protected static $api_map = [
        'Title' => 'title',
        'Author' => 'author',
        'Lead' => 'lead',
        'Location' => 'location',
        'Status' => 'status',
        'Created' => 'created_date',
        'Updated' => 'updated_date',
        'Rating' => 'average_rating',
        'Flagged' => 'flagged',
        'Date' => 'date'
    ];
    private static $table_name = "HailArticle";
    private static $db = [
        'Title' => 'Varchar',
        'Author' => 'Varchar',

        'Lead' => 'HTMLText',
        'Content' => 'HTMLText',
        'Date' => 'Datetime',
        'Location' => 'Varchar',
        'Status' => 'Varchar',

        'Created' => 'Datetime',
        'Updated' => 'Datetime',
        'Rating' => 'Double',
        'Flagged' => 'Boolean'
    ];
    private static $default_sort = "Date DESC";
    private static $has_one = [
        'HeroImage' => 'Firebrand\Hail\Models\Image',
        'HeroVideo' => 'Firebrand\Hail\Models\Video',
    ];
    private static $belongs_many_many = [
        'PublicTags' => 'Firebrand\Hail\Models\PublicTag',
        'PrivateTags' => 'Firebrand\Hail\Models\PrivateTag',
        'Attachments' => 'Firebrand\Hail\Models\Attachment'
    ];
    private static $many_many = [
        'ImageGallery' => 'Firebrand\Hail\Models\Image',
        'VideoGallery' => 'Firebrand\Hail\Models\Video',
    ];
    private static $summary_fields = [
        'Organisation.Title' => 'Hail Organisation',
        'HailID' => 'Hail ID',
        'Title' => 'Title',
        'Author' => 'Author',
        'Lead' => 'Lead',
        'Date' => 'Date'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Show relations, SilverStripe can't do Read Only Gridfield by default yet
        $this->makeRecordViewer($fields, "Public Tags", $this->PublicTags());
        $this->makeRecordViewer($fields, "Private Tags", $this->PrivateTags());
        $this->makeRecordViewer($fields, "Image Gallery", $this->ImageGallery());
        $this->makeRecordViewer($fields, "Video Gallery", $this->VideoGallery());
        $this->makeRecordViewer($fields, "Attachments", $this->Attachments(), 'Firebrand\Hail\Forms\GridFieldAttachmentDownloadButton');

        // Display a thumbnail of the hero image
        if ($this->HeroImage()->ID != 0) {
            $html = "<div class='form-group field lookup readonly '><label class='form__field-label'>Hero Image</label><div class='form__field-holder'>{$this->HeroImage()->getThumbnail()}</div></div>";
            $heroField = new LiteralField(
                "HeroImage",
                $html
            );
            $fields->replaceField('HeroImageID', $heroField);
        } else {
            $fields->removeByName('HeroImageID');
        }

        // Display a thumbnail of the hero image
        if ($this->HeroVideo()->ID != 0) {
            $html = "<div class='form-group field lookup readonly '><label class='form__field-label'>Hero Video</label><div class='form__field-holder'>{$this->HeroVideo()->getThumbnail()}</div></div>";
            $heroField = new LiteralField(
                "HeroVideo",
                $html
            );
            $fields->replaceField('HeroVideoID', $heroField);
        } else {
            $fields->removeByName('HeroVideoID');
        }

        return $fields;
    }
    
    protected function importing($data)
    {
        if (!empty($data['body'])) {
            $this->Content = $data['body'];
        }

        $this->processPublicTags($data['tags']);
        $this->processPrivateTags($data['private_tags']);
        $this->processHeroImage($data['hero_image']);
        $this->processHeroVideo($data['hero_video']);
        $this->processAttachments($data['attachments']);

        //IF we have an image gallery, fetch every images
        if(count($data['short_gallery']) > 0) {
            $this->fetchImages();
        }
        //IF we have an video gallery, fetch every videos
        if(count($data['short_video_gallery']) > 0) {
            $this->fetchVideos();
        }
    }

    /**
     * Fetch the image gallery of this article from the Hail API
     *
     * @return void
     */
    public function fetchImages()
    {
        try {
            $api_client = new Client();
            $list = $api_client->getImagesByArticles($this->HailID);
        } catch (\Exception $ex) {
            return;
        }

        $hailIdList = [];

        // Clean before importing
        $this->ImageGallery()->removeAll();

        foreach ($list as $hailData) {
            // Build up Hail ID list
            $hailIdList[] = $hailData['id'];

            // Check if we can find an existing item.
            $hailObj = Image::get()->filter(['HailID' => $hailData['id']])->First();

            if (!$hailObj) {
                $hailObj = new Image();
            }
            $hailObj->OrganisationID = $this->OrganisationID;
            $hailObj->HailOrgID = $this->HailOrgID;
            $hailObj->HailOrgName = $this->HailOrgName;

            $hailObj->importHailData($hailData);
            $this->ImageGallery()->add($hailObj);
        }
    }

    /**
     * Fetch the video gallery of this article from the Hail API
     *
     * @return void
     */
    public function fetchVideos()
    {
        try {
            $api_client = new Client();
            $list = $api_client->getVideosByArticles($this->HailID);
        } catch (\Exception $ex) {
            return;
        }

        $hailIdList = [];
        // CLean before importing
        $this->VideoGallery()->removeAll();

        foreach ($list as $hailData) {
            // Build up Hail ID list
            $hailIdList[] = $hailData['id'];

            // Check if we can find an existing item.
            $hailObj = Video::get()->filter(['HailID' => $hailData['id']])->First();
            if (!$hailObj) {
                $hailObj = new Video();
            }
            $hailObj->OrganisationID = $this->OrganisationID;
            $hailObj->HailOrgID = $this->HailOrgID;
            $hailObj->HailOrgName = $this->HailOrgName;

            $hailObj->importHailData($hailData);
            $this->VideoGallery()->add($hailObj);
        }
    }
}