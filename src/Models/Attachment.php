<?php

namespace Firebrand\Hail\Models;

use SilverStripe\ORM\ManyManyList;

/**
 * Hail Attachment DataObject
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property string $UploadedName
 * @property string $UploadedExtension
 * @property string $MimeType
 * @property string $FileSize
 * @property string $Url
 * @property string $Name
 *
 * @method ManyManyList Articles()
 */
class Attachment extends ApiObject
{
    /**
     * @inheritdoc
     */
    public static $object_endpoint = "attachments";
    /**
     * @inheritdoc
     */
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

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Show relations, SilverStripe can't do Read Only Gridfield by default yet
        $this->makeRecordViewer($fields, "Articles", $this->Articles());

        return $fields;
    }
}
