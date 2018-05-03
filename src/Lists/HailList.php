<?php

namespace Firebrand\Hail\Lists;

use Firebrand\Hail\Models\Organisation;
use Firebrand\Hail\Models\PrivateTag;
use Firebrand\Hail\Models\PublicTag;
use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

class HailList extends DataObject
{
    private static $table_name = "HailList";
    private static $db = [
        "Type" => "Varchar",
        "OrganisationsIDs" => "Varchar",
        "IncludedPublicTagsIDs" => "Varchar",
        "IncludedPrivateTagsIDs" => "Varchar",
        "ExcludedPublicTagsIDs" => "Varchar",
        "ExcludedPrivateTagsIDs" => "Varchar",
    ];
    private static $has_many = [
        'HailPages' => 'Firebrand\Hail\Pages\HailPage',
    ];
    private static $summary_fields = [
        "Type",
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('HailPages');

        $types = ListboxField::create('Type', 'Type included', ["Articles" => "Articles", "Publications" => "Publications"]);
        $fields->addFieldToTab('Root.Main', $types);

        $org_lists = ListboxField::create("OrganisationsIDs", "Organisations included", $this->getOrganisations());
        $fields->addFieldToTab('Root.Main', $org_lists);

        $inc_pub_tags = ListboxField::create("IncludedPublicTagsIDs", "Public Tags included", $this->getPublicTagsList());
        $inc_pub_tags->setDescription("Leave empty to include all tags");
        $fields->addFieldToTab('Root.Main', $inc_pub_tags);

        $exc_pub_tags = ListboxField::create("ExcludedPublicTagsIDs", "Public Tags excluded", $this->getPublicTagsList());
        $fields->addFieldToTab('Root.Main', $exc_pub_tags);

        $inc_pri_tags = ListboxField::create("IncludedPrivateTagsIDs", "Private Tags included", $this->getPrivateTagsList());
        $inc_pri_tags->setDescription("Leave empty to include all tags");
        $fields->addFieldToTab('Root.Main', $inc_pri_tags);

        $exc_pri_tags = ListboxField::create("ExcludedPrivateTagsIDs", "Private Tags excluded", $this->getPrivateTagsList());
        $fields->addFieldToTab('Root.Main', $exc_pri_tags);

        return $fields;
    }

    public function getFieldsForHasOne($relation_name)
    {
        $pri_tags = $this->getPrivateTagsList();
        $pub_tags = $this->getPublicTagsList();
        $organisations = $this->getOrganisations();
        return [
            ListboxField::create($relation_name . '___Type', 'Type included', ["Articles" => "Articles", "Publications" => "Publications"]),
            ListboxField::create($relation_name . "___OrganisationsIDs", "Organisations included", $organisations),
            ListboxField::create($relation_name . "___IncludedPublicTagsIDs", "Public Tags included", $pub_tags)->setDescription("Leave empty to include all tags"),
            ListboxField::create($relation_name . "___ExcludedPublicTagsIDs", "Public Tags excluded", $pub_tags),
            ListboxField::create($relation_name . "___IncludedPrivateTagsIDs", "Private Tags included", $pri_tags)->setDescription("Leave empty to include all tags"),
            ListboxField::create($relation_name . "___ExcludedPrivateTagsIDs", "Private Tags excluded", $pri_tags),
        ];
    }

    public function getOrganisations()
    {
        $config = SiteConfig::current_site_config();
        //Filter out Organisation that are not setup in the config
        $organisations = Organisation::get()->filter(['HailID' => json_decode($config->HailOrgsIDs)]);

        return $organisations->sort('Title')->map('HailID', 'Title')->toArray();
    }

    public function getPrivateTagsList()
    {
        $config = SiteConfig::current_site_config();
        //Filter out global excluded tags and non configured Organisations
        $pri_tags = PrivateTag::get()->filter(['HailID:not' => json_decode($config->HailExcludePrivateTagsIDs), 'HailOrgID' => json_decode($config->HailOrgsIDs)]);

        return $pri_tags->sort(['HailOrgName ASC', 'Name ASC'])->map('HailID', 'FullName')->toArray();
    }

    public function getPublicTagsList()
    {
        $config = SiteConfig::current_site_config();
        //Filter out global excluded tags and non configured Organisations
        $pub_tags = PublicTag::get()->filter(['HailID:not' => json_decode($config->HailExcludePublicTagsIDs), 'HailOrgID' => json_decode($config->HailOrgsIDs)]);

        return $pub_tags->sort(['HailOrgName ASC', 'Name ASC'])->map('HailID', 'FullName')->toArray();
    }
}