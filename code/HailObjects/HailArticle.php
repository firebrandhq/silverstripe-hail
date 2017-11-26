<?php

/**
 * Abtract representation of an Hail Article retrieved via the Hail API.
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 * @property string Title
 * @property string Author
 * @property HTMLText Lead
 * @property HTMLText Content
 * @property SS_Datetime Date
 * @property string Location
 * @property string Status
 * @property SS_Date Created
 * @property double Rating
 * @property boolean Flagged
 *
 * @method HailImage HeroImage() Hero image for the article
 * @method ManyManyList Tags() List of {@link HailTag}
 * @method ManyManyList ImageGallery() List of {@link HailImage}
 */

class HailArticle extends HailApiObject implements SearchableLinkable {

    private static $db = array(
        'Title' => 'Text',
        'Author' => 'VarChar',

        'Lead' => 'HTMLText',
        "Content" => 'HTMLText',
        'Date' => 'Datetime',
        'Location' => 'Varchar',
        'Status' => 'Varchar',

        'Created' => 'Datetime',
        'Updated' => 'Datetime',
        'Rating' => 'Double',
        'Flagged' => 'Boolean'
    );

    private static $default_sort = "Date DESC";



    private static $has_one = array(
        'HeroImage' => 'HailImage',
        'HeroVideo' => 'HailVideo',
    );

    private static $belongs_many_many = array(
        'Tags' => 'HailTag',
        'Attachments' => 'HailAttachment'
    );

    private static $many_many = array(
        'ImageGallery' => 'HailImage',
        'VideoGallery' => 'HailVideo',
    );

    private static $summary_fields = array(
        'Organisation.Title' => 'Hail Organisation',
        'HailID' => 'Hail ID',
        'Title' => 'Title',
        'Author' => 'Author',
        'Lead' => 'Lead',
        'Date' => 'Date'
    );

    private static $create_table_options = array('MySQLDatabase' => 'ENGINE=MyISAM');

    static $indexes = array(
        'SearchFields' => 'fulltext (Title,Lead,Author,Content)'
    );

    protected static function getObjectType() {
        return HailApi::ARTICLES;
    }

    protected function importing($data) {
        if (!empty($data->body)) {
            $this->Content = $data->body;
        }

        $this->processTags($data->tags, $data->private_tags);
        $this->processHeroImage($data->hero_image);
        $this->processHeroVideo($data->hero_video);
        $this->processAttachments($data->attachments);

        // TODO: Generate Unique URL handler
    }

    protected static function apiMap() {
        return array(
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
        );
    }

    // Match the hero image if there's one
    private function processHeroImage($heroImgData) {
        if ($heroImgData) {
            $hero = HailImage::get()->filter(array('HailID' => $heroImgData->id))->first();
            if (!$hero) {
                $hero = new HailImage();
            }
            $hero->OrganisationID = $this->OrganisationID;
            $hero->importHailData($heroImgData);
            $hero = $hero->ID;
        } else {
            $hero = null;
        }

        $this->HeroImageID = $hero;
    }

    // Match the hero video if there's one
    private function processHeroVideo($heroVidData) {
        if ($heroVidData) {
            $hero = HailVideo::get()->filter(array('HailID' => $heroVidData->id))->first();
            if (!$hero) {
                $hero = new HailVideo();
            }
            $hero->OrganisationID = $this->OrganisationID;
            $hero->importHailData($heroVidData);
            $hero = $hero->ID;
        } else {
            $hero = null;
        }

        $this->HeroVideoID = $hero;
    }

    // Go through the attachments and assign them to this article.
    private function processAttachments($data) {
        $idList = array();
        foreach ($data as $attachmentData) {
            $idList[] = $attachmentData->id;

            // Find a matching attachment or create it
            $attachment = HailAttachment::get()->filter(array('HailID' => $attachmentData->id))->first();

            if (!$attachment) {
                $attachment = new HailAttachment();
            }
            $attachment->OrganisationID = $this->OrganisationID;

            // Update the Hail Attachments
            $attachment->importHailData($attachmentData);
            if (!$this->Attachments()->byID($attachment->ID)) {
                $this->Attachments()->add($attachment);
            }
        }

        // Remove old attachments that are currently assigned to this article,
        // but have not been returned this time around
        if ($idList) {
            $this->Attachments()->exclude('HailID', $idList)->removeAll();
        } else {
            // If there's no attachements, just remove everything.
            $this->Attachments()->removeAll();
        }

    }


    public function getCMSFields( ) {
        $fields = parent::getCMSFields();

        // Show relations
        $this->makeRecordViewer($fields, "Tags", $this->Tags());
        $this->makeRecordViewer($fields, "Images", $this->ImageGallery());
        $this->makeRecordViewer($fields, "Attachments", $this->Attachments(), 'GridFieldHailAttachmentDownloadButton');

        // Display a thumbnail of the hero image
        if ($this->HeroImage()) {
            $heroField = new LiteralField (
                "HeroImage",
                $this->HeroImage()->getThumbnail()
            );
            $fields->replaceField('HeroImageID', $heroField);
        } else {
            $fields->removeByName('HeroImageID');
        }

        return $fields;
    }

    protected function refreshing() {
        $this->fetchImages();
        $this->fetchVideos();
    }

    /**
     * Fetch the image gallery of this article from the Hail API
     *
     * @return void
     */
    public function fetchImages() {
        try {
            $list = HailApi::getImagesByArticles($this->HailID, HailOrganisation::get()->byID($this->OrganisationID));
        } catch (HailApiException $ex) {
            Debug::warningHandler(E_WARNING, $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTrace());
            return;
        }

        $hailIdList = array();

        foreach($list as $hailData) {
            // Build up Hail ID list
            $hailIdList[] = $hailData->id;

            // Check if we can find an existing item.
            $hailObj = HailImage::get()->filter(array('HailID' => $hailData->id))->First();
            if (!$hailObj) {
                $hailObj = new HailImage();
            }
            $hailObj->OrganisationID = $this->OrganisationID;
            $hailObj->importHailData($hailData);
            $this->ImageGallery()->add($hailObj);
        }

        // Remove images that are no longer assign to this article
        if ($hailIdList) {
            $this->ImageGallery()->exclude('HailID', $hailIdList)->removeAll();
        } else {
            $this->ImageGallery()->removeAll();
        }
    }

    /**
     * Fetch the video gallery of this article from the Hail API
     *
     * @return void
     */
    public function fetchVideos() {
        try {
            $list = HailApi::getVideosByArticles($this->HailID, HailOrganisation::get()->byID($this->OrganisationID));
        } catch (HailApiException $ex) {
            Debug::warningHandler(E_WARNING, $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTrace());
            return;
        }

        $hailIdList = array();

        foreach($list as $hailData) {
            // Build up Hail ID list
            $hailIdList[] = $hailData->id;

            // Check if we can find an existing item.
            $hailObj = HailVideo::get()->filter(array('HailID' => $hailData->id))->First();
            if (!$hailObj) {
                $hailObj = new HailVideo();
            }
            $hailObj->OrganisationID = $this->OrganisationID;
            $hailObj->importHailData($hailData);
            $this->VideoGallery()->add($hailObj);
        }

        // Remove images that are no longer assign to this article
        if ($hailIdList) {
            $this->VideoGallery()->exclude('HailID', $hailIdList)->removeAll();
        } else {
            $this->VideoGallery()->removeAll();
        }
    }

    public function forTemplate() {
        return $this->renderWith('HailArticle', array('HailHolder' => Controller::curr()) );
    }

    protected function importHailData($data)
    {
        $originalUpdated = $this->Updated;
        $return = parent::importHailData($data);

        if ($originalUpdated != $this->Updated) {

            $this->refresh();
        }

        return $return;
    }

    public function getFirstSentence() {
        if ($this->Content) {
            $sentence = strip_tags($this->Content);
            $sentence = str_replace("\r\n", ' ', $sentence);
            $sentence = str_replace("\n", ' ', $sentence);
            $sentence = str_replace("\r", ' ', $sentence);
            $sentence = trim($sentence);
            $sentence = preg_replace('/(.*?[?!.](?=\s|$)).*/', '\\1', $sentence);
            return $sentence;
        } else {
            return '';
        }
    }

    public function Link() {
        $holder = SiteConfig::current_site_config()->PrimaryHailHolder;
        if (!$holder) {
            $holder = HailHolder::get()->first();
        }
        if (!$holder) {
            return false;
        }
        return $holder->Link('article/' . $this->ID);
    }

    /**
     * Filter array
     * eg. array('Disabled' => 0);
     * @return array
     */
    public static function getSearchFilter() {
        return array();
    }

    public static function getSearchFilterByCallback() {
        return function($item, $list) {
            $hailLists = HailList::get();

            // Index all articles if a HailList is used anywhere
            if($hailLists->filter('ClassName', 'HailList')->count() > 0) {
                return true;
            }

            $hailTagLists = TagHailList::get()->column('TagID');
            $hailMultiTagLists = SQLQuery::create()->setFrom('MultiTagHailList_Tags')->execute()->column('HailTagID');

            $allowedTags = array_merge($hailTagLists, $hailMultiTagLists);

            $tags = $item->Tags()->column('ID');

            // Index articles if the tag is used in a HailList
            if(array_intersect($allowedTags, $tags)) {
                return true;
            }

            return false;
        };
    }

    /**
     * Fields that compose the Title
     * eg. array('Title', 'Subtitle');
     * @return array
     */
    public function getTitleFields() {
        return array('Title');
    }

    /**
     * Fields that compose the Content
     * eg. array('Teaser', 'Content');
     * @return array
     */
    public function getContentFields() {
        return array(
            "Title",
            "Lead",
            "Content",
        );
    }

    public function getOwner() {
        return $this;
    }

    public function IncludeInSearch() {
        return true;
    }

    public function getHeroImage() {
        $img = $this->HeroImage();
        if ($img && $img->ID > 0) { return $img; }

        $img = $this->HeroVideo();
        if ($img && $img->ID > 0) { return $img; }

        return $this->HeroImage();
    }

    /**
     * Whatever the HeroImage is in the Gallery. Will return false if there's no HeroImage at all.
     *
     * @return boolean
     */
    public function HeroImageInGallery()
    {
        $img = $this->HeroImage();
        if ($img && $img->ID > 0) {
            return $this->ImageGallery()->where('HailID', $img->HailID)->count() > 0;
        }

        return false;
    }

    /**
     * List of the tag IDs associated to this article seperated by spaces. Suitable to be used as CSS classes.
     * @return string
     */
    public function TagList()
    {
        $string = '';
        foreach ($this->Tags() as $t) {
            $string .= $t->HailID . ' ';
        }
        return trim($string);
    }

}
