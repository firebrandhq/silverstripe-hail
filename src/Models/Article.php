<?php

namespace Firebrand\Hail\Models;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Pages\HailPage;
use Firebrand\Hail\Pages\HailPageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\Connect\MySQLSchemaManager;
use SilverStripe\ORM\ManyManyList;

/**
 * Hail Article DataObject
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $Title
 * @property string $Author
 * @property string $Lead
 * @property string $Content
 * @property string $Location
 * @property string $Status Article status in Hail
 * @property string $Updated Date and time of last update in Hail
 * @property double $Rating
 * @property boolean $Flagged
 * @property string $Date Publication date and time in Hail
 * @property string $HailURL
 *
 * @method Image HeroImage()
 * @method Video HeroVideo()
 * @method ManyManyList PublicTags()
 * @method ManyManyList PrivateTags()
 * @method ManyManyList Attachments()
 * @method ManyManyList ImageGallery()
 * @method ManyManyList VideoGallery()
 */
class Article extends ApiObject
{
    /**
     * @inheritdoc
     */
    public static $object_endpoint = "articles";
    /**
     * @inheritdoc
     */
    protected static $api_map = [
        'Title' => 'title',
        'Author' => 'author',
        'Lead' => 'lead',
        'Location' => 'location',
        'Status' => 'status',
        'Created' => 'date',
        'Updated' => 'updated_date',
        'Rating' => 'average_rating',
        'Flagged' => 'flagged',
        'Date' => 'date',
        'HailURL' => 'hail_url',
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

        'Updated' => 'Datetime',
        'Rating' => 'Double',
        'Flagged' => 'Boolean',
        'HailURL' => 'Varchar'
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
    private static $indexes = [
        'SearchFields' => [
            'type' => 'fulltext',
            'columns' => ['Author', 'Title', 'Lead', 'Content'],
        ]
    ];
    private static $create_table_options = [
        MySQLSchemaManager::ID => 'ENGINE=MyISAM'
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
            $heroField = new LiteralField(
                "HeroImage",
                $this->HeroImage()->getThumbnailField("Hero Image")
            );
            $fields->replaceField('HeroImageID', $heroField);
        } else {
            $fields->removeByName('HeroImageID');
        }

        // Display a thumbnail of the hero image
        if ($this->HeroVideo()->ID != 0) {
            $heroField = new LiteralField(
                "HeroVideo",
                $this->HeroVideo()->getThumbnailField("Hero Video")
            );
            $fields->replaceField('HeroVideoID', $heroField);
        } else {
            $fields->removeByName('HeroVideoID');
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
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
        if (count($data['short_gallery']) > 0) {
            $this->fetchImages();
        }
        //IF we have an video gallery, fetch every videos
        if (count($data['short_video_gallery']) > 0) {
            $this->fetchVideos();
        }
    }

    /**
     * Fetch the image gallery of this article from the Hail API
     *
     * @return void
     * @throws
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
     * @throws
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

    /**
     * Return the Article link for the current HailPageController
     *
     * @return string
     */
    public function Link()
    {
        $ctrl = Controller::curr();
        if ($ctrl instanceof HailPageController) {
            $link = $ctrl->Link();
        } else {
            //If outside HailPageController try to find the first Hail Page
            $page = HailPage::get()->first();
            if (!empty($page)) {
                $link = $page->Link();
            }
        }

        if (!isset($link)) {
            return "";
        }

        return $link . "article/" . $this->HailID . '/' . Convert::raw2url($this->Title);
    }

    /**
     * Return the Article link for specified HailPage
     *
     * @param HailPage $page
     * @return string
     */
    public function getLinkForPage($page)
    {
        return $page->Link() . "article/" . $this->HailID . '/' . Convert::raw2url($this->Title);
    }

    /**
     * Return the absolute Article link
     *
     * @return string
     */
    public function AbsoluteLink()
    {
        return Director::absoluteURL($this->Link());
    }

    /**
     * Helper to return the object type
     *
     * @return string
     */
    public function getType()
    {
        return "article";
    }

    /**
     * Helper to return the title from the breadcrumbs
     *
     * @return string
     */
    public function getMenuTitle()
    {
        return $this->Title;
    }

    /**
     * Return the placeholder HeroImage link
     *
     * @return string
     */
    public function getPlaceHolderHero()
    {
        return '/resources/' . HAIL_DIR . '/client/dist/images/placeholder-hero.png';
    }

    /**
     * List of this Article's public tag names separated by spaces.
     *
     * Suitable to be used as CSS classes.
     *
     * @return string
     */
    public function getTagList()
    {
        $string = '';
        foreach ($this->PublicTags() as $t) {
            $string .= Convert::raw2url($t->Name) . ' ';
        }
        return trim($string);
    }

    /**
     * List of this Article's images
     *
     * Includes the Hero Image
     *
     * @return ArrayList
     */
    public function getAllImages()
    {
        $images = new ArrayList();
        if ($this->hasHeroImage()) {
            $images->push($this->HeroImage());
        }
        if ($this->hasGalleryImages()) {
            $images->merge($this->ImageGallery());
        }
        $images->removeDuplicates('HailID');

        return $images;
    }

    /**
     * List of this Article's videos
     *
     * Includes the Hero Video
     *
     * @return ArrayList
     */
    public function getAllVideos()
    {
        $videos = new ArrayList();
        if ($this->hasHeroVideo()) {
            $videos->push($this->HeroVideo());
        }
        if ($this->hasGalleryVideos()) {
            $videos->merge($this->VideoGallery());
        }
        $videos->removeDuplicates('HailID');

        return $videos;
    }

    /**
     * Checks if Article has a HeroImage
     *
     * @return boolean
     */
    public function hasHeroImage()
    {
        return $this->HeroImage()->ID != 0;
    }

    /**
     * Checks if Article has a HeroVideo
     *
     * @return boolean
     */
    public function hasHeroVideo()
    {
        return $this->HeroVideo()->ID != 0;
    }

    /**
     * Checks if Article has an Image Gallery
     *
     * @return boolean
     */
    public function hasGalleryImages()
    {
        return $this->ImageGallery()->count() > 0;
    }

    /**
     * Checks if Article has a Video Gallery
     *
     * @return boolean
     */
    public function hasGalleryVideos()
    {
        return $this->VideoGallery()->count() > 0;
    }

    /**
     * Checks if Article has any Images attached (Hero and / or Gallery)
     *
     * @return boolean
     */
    public function hasImages()
    {
        return $this->hasHeroImage() || $this->hasGalleryImages();
    }

    /**
     * Checks if Article has any Videos attached (Hero and / or Gallery)
     *
     * @return boolean
     */
    public function hasVideos()
    {
        return $this->hasHeroVideo() || $this->hasGalleryVideos();
    }

    /**
     * Returns hero image to be added to article sitemap when googlesitemap module is installed
     *
     * @return Image|null
     */
    public function ImagesForSitemap()
    {
        if($this->hasHeroImage()) {
            return $this->HeroImage();
        }

        return null;
    }
}
