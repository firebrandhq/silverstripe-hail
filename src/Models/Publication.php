<?php

namespace Firebrand\Hail\Models;

use SilverStripe\Forms\LiteralField;

class Publication extends ApiObject
{
    public static $object_endpoint = "publications";
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
        'Created' => 'Datetime',
        'Url' => 'Varchar',
    ];
    private static $has_one = [
        'FeaturedArticle' => 'Firebrand\Hail\Models\Article',
        'HeroImage' => 'Firebrand\Hail\Models\Image'
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

    protected function importing($data)
    {
        if (isset($data['style'])) {
            $this->Style = isset($data['style']['name']) && !empty($data['style']['name']) ? $data['style']['name'] : '';
        }

        $this->processPrivateTags($data['private_tags']);

        $featured = isset($data['featured_article']) && !empty($data['featured_article']) ? $data['featured_article'] : null;
        $this->processFeaturedArticle($featured);
    }

    // Match the featured article if there's one
    private function processFeaturedArticle($articleData)
    {
        if ($articleData) {
            $article = Article::get()->filter(['HailID' => $articleData['id']])->first();
            if (!$article) {
                $article = new Article();
            }
            $article->importHailData($articleData);

            $heroImage = $article->HeroImageID;

            $article = $article->ID;
        } else {
            $article = null;
        }

        $this->FeaturedArticleID = $article;

        if (isset($heroImage) && $heroImage) {
            $this->HeroImageID = $heroImage;
        }

    }
}
