<?php

namespace Firebrand\Hail\Models;

class Attachment extends ApiObject
{
    public static $object_endpoint = "attachments";
    protected static $api_map = [
        'UploadedName' => 'uploaded_name',
        'UploadedExtension' => 'uploaded_extension',
        'MimeType' => 'mime_type',
        'FileSize' => 'file_size',
        'Url' => 'url',
        'Name' => 'name',
    ];
    private static $table_name = "HailAttachment";
    private static $db = [
        'UploadedName' => 'Varchar',
        'UploadedExtension' => 'Varchar',
        'MimeType' => 'Varchar',
        'FileSize' => 'Varchar',
        'Url' => 'Varchar',
        'Name' => 'Varchar',
    ];
    private static $many_many = [
        'Articles' => 'Firebrand\Hail\Models\Article',
    ];
    private static $summary_fields = [
        'HailID',
        'Name',
        'UploadedName',
        'MimeType',
        'FileSize',
    ];
}
