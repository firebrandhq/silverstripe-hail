<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Lists\HailList;
use Firebrand\Hail\Models\PublicTag;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\RequiredFields;

class HailPage extends \Page
{
    private static $table_name = "HailPage";
    private static $db = [
        "PaginationStyle" => "Enum(array('Default','InfiniteScroll'))",
        "PaginationPerPage" => "Int",
    ];
    private static $defaults = [
        'PaginationPerPage' => 12,
    ];
    private static $has_one = [
        "List" => "Firebrand\Hail\Lists\HailList",
    ];
    private static $many_many = [
        'FilterTags' => 'Firebrand\Hail\Models\PublicTag',
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
        $filter_tags = ListboxField::create('FilterTags', 'Filter tags', PublicTag::get())->setDescription('Leave empty to disable filtering');
        $pagination_style = DropdownField::create('PaginationStyle', 'Pagination style', ['Default', 'InfiniteScroll' => 'Infinite scroll']);
        $pagination_per_page = NumericField::create('PaginationPerPage', 'Items displayed per page');

        $fields->addFieldsToTab('Root.Hail.Tab', [$filter_tags, $pagination_style, $pagination_per_page]);
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