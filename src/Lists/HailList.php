<?php

namespace Firebrand\Hail\Lists;

use Firebrand\Hail\Models\Organisation;
use Firebrand\Hail\Models\PrivateTag;
use Firebrand\Hail\Models\PublicTag;
use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\DataObject;

class HailList extends DataObject
{
    private static $table_name = "HailList";
    private static $db = [
        "Title" => "Varchar",
        "Description" => "Text",
        "Type" => "Enum(array('Articles', 'Publications'))",
    ];
    private static $many_many = [
        "Organisations" => "Firebrand\Hail\Models\Organisation",
        "IncludedPublicTags" => "Firebrand\Hail\Models\PublicTag",
        "IncludedPrivateTags" => "Firebrand\Hail\Models\PrivateTag",
        "ExcludedPublicTags" => "Firebrand\Hail\Models\PublicTag",
        "ExcludedPrivateTags" => "Firebrand\Hail\Models\PrivateTag",
    ];
    private static $belongs_many_many = [
        'HailPages' => 'Firebrand\Hail\Pages\HailPage',
    ];
    private static $summary_fields = [
        "Type",
        "Title",
        "Description"
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Organisations');
        $fields->removeByName('IncludedPublicTags');
        $fields->removeByName('IncludedPrivateTags');
        $fields->removeByName('ExcludedPublicTags');
        $fields->removeByName('ExcludedPrivateTags');
        $fields->removeByName('HailPages');

        $org_lists = ListboxField::create("Organisations", "Organisations included", Organisation::get());
        $fields->addFieldToTab('Root.Main', $org_lists);

        $inc_pub_tags = ListboxField::create("IncludedPublicTags", "Public Tags included", PublicTag::get());
        $inc_pub_tags->setDescription("Leave empty to include all tags");
        $fields->addFieldToTab('Root.Main', $inc_pub_tags);

        $exc_pub_tags = ListboxField::create("ExcludedPublicTags", "Public Tags excluded", PublicTag::get());
        $fields->addFieldToTab('Root.Main', $exc_pub_tags);

        $inc_pri_tags = ListboxField::create("IncludedPrivateTags", "Private Tags included", PrivateTag::get());
        $inc_pri_tags->setDescription("Leave empty to include all tags");
        $fields->addFieldToTab('Root.Main', $inc_pri_tags);

        $exc_pri_tags = ListboxField::create("ExcludedPrivateTags", "Private Tags excluded", PrivateTag::get());
        $fields->addFieldToTab('Root.Main', $exc_pri_tags);

        return $fields;
    }
}