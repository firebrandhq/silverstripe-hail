<?php

namespace Firebrand\Hail\Models;

use SilverStripe\Forms\LiteralField;

class Article extends ApiObject
{
    protected static $object_endpoint = "articles";
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
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
//        // Show relations
        $this->makeRecordViewer($fields, "Public Tags", $this->PublicTags());
        $this->makeRecordViewer($fields, "Private Tags", $this->PrivateTags());
        $this->makeRecordViewer($fields, "Image Gallery", $this->ImageGallery());
        $this->makeRecordViewer($fields, "Video Gallery", $this->VideoGallery());
        $this->makeRecordViewer($fields, "Attachments", $this->Attachments(), 'Firebrand\Hail\Forms\GridFieldAttachmentDownloadButton');

        // Display a thumbnail of the hero image
        if ($this->HeroImage()) {
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
        if ($this->HeroVideo()) {
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
}