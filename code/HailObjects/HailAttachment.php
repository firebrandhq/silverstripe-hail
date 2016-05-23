<?php

/**
 * Abtract representation of an Hail Article retrieved via the Hail API.
 *
 * @author Maxime Rainville, Firebrand
 *
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
 * @property float Rating
 * @property bool Flagged
 *
 * @method HailImage HeroImage() Hero image for the article
 * @method ManyManyList Tags() List of {@link HailTag}
 * @method ManyManyList ImageGallery() List of {@link HailImage}
 */
class HailAttachment extends HailApiObject
{
    private static $db = array(
        'UploadedName' => 'Text',
        'UploadedExtension' => 'Text',
        'MimeType' => 'Text',
        'FileSize' => 'Text',
        'Url' => 'Text',
        'Name' => 'Text',
    );

    private static $many_many = array(
        'Articles' => 'HailArticle',
    );

    private static $summary_fields = array(
        'HailID',
        'Name',
        'UploadedName',
        'MimeType',
        'FileSize',
    );

    protected static function getObjectType()
    {
        return HailApi::ATTACHMENTS;
    }

    protected function importing($data)
    {
        if (!empty($data->body)) {
            $this->Content = $data->body;
        }
    }

    protected static function apiMap()
    {
        return array(
            'UploadedName' => 'uploaded_name',
            'UploadedExtension' => 'uploaded_extension',
            'MimeType' => 'mime_type',
            'FileSize' => 'file_size',
            'Url' => 'url',
            'Name' => 'name',
        );
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Show relations
        /*$this->makeRecordViewer($fields, "Tags", $this->Tags());
        $this->makeRecordViewer($fields, "Images", $this->ImageGallery());
        
        // Display a thumbnail of the hero image
        if ($this->HeroImage()) {
            $heroField = new LiteralField (
                "HeroImage",
                $this->HeroImage()->getThumbnail()
            );
            $fields->replaceField('HeroImageID', $heroField);
        } else {
            $fields->removeByName('HeroImageID');
        }*/

        return $fields;
    }

    public function FileSizeForHumans()
    {
        $bytes = $this->FileSize;
        $decimals = 2;
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)).@$size[$factor];
    }

    /*public function forTemplate() {
        return $this->renderWith('HailArticle', array('HailHolder' => Controller::curr()) );
    }*/
}
