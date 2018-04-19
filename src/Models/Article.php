<?php

namespace Firebrand\Hail\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\PermissionProvider;

class Article extends DataObject implements PermissionProvider
{
    private static $table_name = "HailArticles";
    private static $db = [
        'Title' => 'Varchar',
        'Author' => 'Varchar',
        'Lead' => 'Varchar',
        "Content" => 'Text',
        'Date' => 'Datetime',
        'Location' => 'Varchar',
        'Status' => 'Varchar',
        'Created' => 'Datetime',
        'Updated' => 'Datetime',
        'Rating' => 'Double',
        'Flagged' => 'Boolean'
    ];

    private static $api_access = true;

    function canView($member = false)
    {
        return true;
    }

    function canEdit($member = false)
    {
        return true;
    }

    function canDelete($member = false)
    {
        return true;
    }

    function canCreate($member = false, $context = [])
    {
        return true;
    }

    function providePermissions()
    {
        return [
            'ARTICLE_VIEW' => 'Read an article object',
            'ARTICLE_EDIT' => 'Edit an article object',
            'ARTICLE_DELETE' => 'Delete an article object',
            'ARTICLE_CREATE' => 'Create an article object',
        ];
    }
}