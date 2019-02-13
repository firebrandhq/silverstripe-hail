<?php

namespace Firebrand\Hail\Models;

use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ManyManyList;

/**
 * Hail Publication DataObject
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $Title
 * @property string $Editorial
 * @property string $DueDate Create date in hail
 * @property string $Status Publication status in Hail
 * @property string $Updated Date and time of last update in Hail
 * @property string $Style
 * @property string $Url
 *
 * @method Article FeaturedArticle()
 * @method Image HeroImage()
 * @method Video HeroVideo()
 * @method ManyManyList PrivateTags()
 */
class Publication extends ApiObject
{
    /**
     * @inheritdoc
     */
    public static $object_endpoint = "publications";
    /**
     * @inheritdoc
     */
    protected static $api_map = [
        'Title' => 'title',
        'Editorial' => 'editorial',
        'DueDate' => 'due_date',
        'Status' => 'status',
        'Created' => 'created_date',
        'Url' => 'url',
    ];
    private static $table_name = "HailPublication";
    private static $db = [
        'Title' => 'Varchar',
        'Editorial' => 'HTMLText',
        'DueDate' => 'Datetime',
        'Status' => 'Varchar',
        'Style' => 'Varchar',
        'Url' => 'Varchar',
    ];
    private static $has_one = [
        'FeaturedArticle' => 'Firebrand\Hail\Models\Article',
        'HeroImage' => 'Firebrand\Hail\Models\Image',
        'HeroVideo' => 'Firebrand\Hail\Models\Video',
    ];
    private static $belongs_many_many = [
        'PrivateTags' => 'Firebrand\Hail\Models\PrivateTag',
    ];
    private static $searchable_fields = [
        'Title',
        'Editorial',
        'Created',
        'Style'
    ];
    private static $summary_fields = [
        'Organisation.Title' => 'Hail Organisation',
        'HailID' => 'Hail ID',
        'Title' => 'Title',
        'Editorial' => 'Editorial',
        'Style' => 'Style',
        'Fetched' => 'Fetched'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $this->makeRecordViewer($fields, "Private Tags", $this->PrivateTags());

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

        if ($this->FeaturedArticleID != 0) {
            $record = $this->FeaturedArticle();
            $html = "<div class='form-group field lookup readonly '><label class='form__field-label'>Featured Article</label><div class='form__field-holder'><p class='form-control-static readonly'><a href='" . singleton('Firebrand\Hail\Admin\HailModelAdmin')->Link(str_replace("\\",
                        "-", $record->ClassName) . '/EditForm/field/' . str_replace("\\", "-",
                        $record->ClassName) . '/item/' . $record->ID) . "'>" . $record->Title . "</a></p></div></div>";
            $heroField = new LiteralField(
                "FeaturedArticleID",
                $html
            );
            $fields->replaceField('FeaturedArticleID', $heroField);
        } else {
            $fields->removeByName('FeaturedArticleID');
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    protected function importing($data)
    {
        if (isset($data['style'])) {
            $this->Style = isset($data['style']['name']) && !empty($data['style']['name']) ? $data['style']['name'] : '';
        }

        $this->processPrivateTags($data['private_tags']);

        $featured = isset($data['featured_article']) && !empty($data['featured_article']) ? $data['featured_article'] : null;
        $this->processFeaturedArticle($featured);
    }

    /**
     * Attach the featured article to this publication if there is one
     *
     * @param array $articleData
     */
    private function processFeaturedArticle($articleData)
    {
        if ($articleData) {
            $article = Article::get()->filter(['HailID' => $articleData['id']])->first();
            if (!$article) {
                $article = new Article();
                $article->importHailData($articleData);
            }

            $heroImage = $article->HeroImageID;
            $heroVideo = $article->HeroVideoID;

            $article = $article->ID;
        } else {
            $article = null;
        }

        $this->FeaturedArticleID = $article;

        if (isset($heroImage) && $heroImage) {
            $this->HeroImageID = $heroImage;
        }

        if (isset($heroVideo) && $heroVideo) {
            $this->HeroVideoID = $heroVideo;
        }

    }

    /**
     * Return a full URL for this publication
     *
     * @return string
     */
    public function Link()
    {
        return $this->Url;
    }

    /**
     * @link Link()
     */
    public function getLinkForPage($page)
    {
        return $this->Link();
    }

    /**
     * Helper to return the object type
     *
     * @return string
     */
    public function getType()
    {
        return "publication";
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

}
