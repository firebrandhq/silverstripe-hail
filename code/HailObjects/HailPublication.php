<?php

class HailPublication extends HailApiObject
{
    private static $db = array(
        'Title' => 'Varchar',
        'Editorial' => 'HTMLText',
        'DueDate' => 'Datetime',
        'Status' => 'Text',
        'Style' => 'Text',
        'Created' => 'Datetime',
        'Url' => 'Text',
    );

    private static $has_one = array(
        'FeaturedArticle' => 'HailArticle',
        'HeroImage' => 'HailImage',
    );

    private static $searchable_fields = array(
        'Title', 'Editorial', 'Created', 'Style',
    );

    private static $summary_fields = array(
        'HailID',
        'Title',
        'Editorial',
        'Style',
        'Fetched',
    );

    protected static function getObjectType()
    {
        return HailApi::PUBLICATIONS;
    }

    protected function importing($data)
    {
        if (!empty($data->style)) {
            $this->Style = empty($data->style->name) ? '' : $data->style->name;
        }

        $this->processFeaturedArticle($data->featured_article);
    }

    protected static function apiMap()
    {
        return array(
            'Title' => 'title',
            'Editorial' => 'editorial',
            'DueDate' => 'due_date',
            'Status' => 'status',
            'Created' => 'created_date',
            'Url' => 'url',
        );
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

        // Match the featured article if there's one
    private function processFeaturedArticle($articleData)
    {
        if ($articleData) {
            $article = HailArticle::get()->filter(array('HailID' => $articleData->id))->first();
            if (!$article) {
                $article = new HailArticle();
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

    public function forTemplate()
    {
        return $this->renderWith('HailPublication', array('HailHolder' => Controller::curr()));
    }
}
