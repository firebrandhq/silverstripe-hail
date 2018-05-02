<?php

namespace Firebrand\Hail\Pages;


use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

class HailPage extends \Page
{
    private static $table_name = "HailPage";
    private static $many_many = [
        "Lists" => "Firebrand\Hail\Lists\HailList",
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $config = GridFieldConfig_RecordEditor::create();

        $list = new GridField("Lists", "Lists", $this->Lists(), $config);

        $fields->addFieldToTab('Root.Hail.Main', $list);


        return $fields;
    }
}