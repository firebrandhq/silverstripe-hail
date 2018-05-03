<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Lists\HailList;
use SilverStripe\Forms\RequiredFields;

class HailPage extends \Page
{
    private static $table_name = "HailPage";
    private static $has_one = [
        "List" => "Firebrand\Hail\Lists\HailList",
    ];
    private static $icon = "vendor/firebrand/silverstripe-hail/client/dist/images/admin-icon.png";

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        //Create and attach a list to the page if it doesn't exist
        if (!$this->List()->exists()) {
            $list = new HailList();
            $list->write();

            $this->ListID = $list->ID;
            $this->write();
        }

        $fields->addFieldsToTab('Root.Hail.Tab', $this->List()->getFieldsForHasOne("List"));

        return $fields;
    }

    public function getCMSValidator()
    {
        return new RequiredFields([
            'List___Type',
            'List___OrganisationsIDs',
        ]);
    }


    public function onBeforeDelete()
    {
        parent::onBeforeDelete();

        if ($this->List()->exists()) {
            $this->List()->delete();
        }
    }

}