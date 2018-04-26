<?php

namespace Firebrand\Hail\Models;

class Publication extends ApiObject
{
    public static $object_endpoint = "images";
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
}
