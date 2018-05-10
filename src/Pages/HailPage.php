<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Lists\HailList;
use Firebrand\Hail\Models\Article;
use Firebrand\Hail\Models\PublicTag;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;

class HailPage extends \Page
{
    private static $table_name = "HailPage";
    private static $db = [
        "PaginationStyle" => "Enum(array('Default','InfiniteScroll'))",
        "PaginationPerPage" => "Int",
    ];
    private static $defaults = [
        'PaginationPerPage' => 9,
        'PaginationStyle' => 'InfiniteScroll',
    ];
    private static $has_one = [
        "List" => "Firebrand\Hail\Lists\HailList",
        "HeroImage" => Image::class,
    ];
    private static $many_many = [
        'FilterTags' => 'Firebrand\Hail\Models\PublicTag',
    ];
    private static $icon = "vendor/firebrand/silverstripe-hail/client/dist/images/admin-icon.png";
    private static $type_map = [
        "Articles" => "Firebrand\Hail\Models\Article",
        "Publications" => "Firebrand\Hail\Models\Publication",
    ];

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
        $hero_image = UploadField::create('HeroImage', 'Hail Page header image')->setAllowedMaxFileNumber(1);

        $fields->addFieldsToTab('Root.Hail', [$filter_tags, $pagination_style, $pagination_per_page]);
        $fields->addFieldsToTab('Root.Hail', $this->List()->getFieldsForHasOne("List"));
        $fields->addFieldsToTab('Root.Main', $hero_image, 'Content');

        return $fields;
    }

    public function getCMSValidator()
    {
        return new RequiredFields([
            'List___Type',
            'List___OrganisationsIDs',
        ]);
    }

    public function AbsoluteLink($action = null)
    {
        $link = parent::AbsoluteLink($action);

        $this->extend('AbsoluteLink', $link);

        return $link;
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();

        if ($this->List()->exists()) {
            $this->List()->delete();
        }
    }

    public function MetaTags($includeTitle = true)
    {
        $tags = parent::MetaTags($includeTitle);
        $params = Controller::curr()->getRequest()->params();
        if ($params['Action'] === "article" && !empty($params['ID'])) {
            $article = Article::get()->filter(['HailID' => $params['ID']])->first();
            if ($article && $article->HailURL) {
                $tags .= "<link rel=\"canonical\" href=\"{$article->HailURL}\" />";
            }
        }

        return $tags;
    }

    public function getHailList($per_page = null, $limit = null, $tags_to_filter = null)
    {
        $request = Controller::curr()->getRequest();
        $params = $request->params();
        $filter_publications = false;

        $list = new ArrayList();
        if ($this->List()->exists()) {
            $hail_list = $this->List();
            $list_types = json_decode($hail_list->Type);
            //If we have configured Type on our Hail Page
            if (is_array($list_types)) {
                foreach ($list_types as $type) {
                    //Check we have a valid object type
                    if (isset(self::$type_map[$type])) {
                        $class_name = self::$type_map[$type];
                        $filters = [];
                        $has_public_tags = singleton($class_name)->getRelationType('PublicTags');
                        $has_private_tags = singleton($class_name)->getRelationType('PrivateTags');

                        //Get objects and apply filters
                        $objects = $class_name::get();

                        //Included Organisations
                        if ($organisations = json_decode($hail_list->OrganisationsIDs)) {
                            $filters['HailOrgID'] = $organisations;
                        }
                        //Included Private Tags, if null we include all
                        if (json_decode($hail_list->IncludedPrivateTagsIDs) && $has_private_tags) {
                            $filters['PrivateTags.HailID'] = json_decode($hail_list->IncludedPrivateTagsIDs);
                        }
                        //Included Public Tags, if null we include all
                        if (json_decode($hail_list->IncludedPublicTagsIDs) && $has_public_tags) {
                            $filters['PublicTags.HailID'] = json_decode($hail_list->IncludedPublicTagsIDs);
                        }
                        //Excluded Private Tags
                        if (json_decode($hail_list->ExcludedPrivateTagsIDs) && $has_private_tags) {
                            //We use a inverted filter because of the LEFT JOIN not properly filtering if filter is a :not on relation
                            $inverse_filter = $class_name::get()->filter(['PrivateTags.HailID' => json_decode($hail_list->ExcludedPrivateTagsIDs)])->map('ID', 'ID')->toArray();
                            $filters['ID:not'] = $inverse_filter;
                        }
                        //Excluded Public Tags
                        if (json_decode($hail_list->ExcludedPublicTagsIDs) && $has_public_tags) {
                            //We use a inverted filter because of the LEFT JOIN not properly filtering if filter is a :not on relation
                            $inverse_filter = $class_name::get()->filter(['PublicTags.HailID' => json_decode($hail_list->ExcludedPublicTagsIDs)])->map('ID', 'ID')->toArray();
                            $filters['ID:not'] = $inverse_filter;
                        }

                        //In page public tag filter
                        if ($params['Action'] === "tag" && !empty($params['ID']) && $has_public_tags) {
                            $filters['PublicTags.HailID'] = $params['ID'];
                            $filter_publications = true;
                        }

                        //On demand tag filtering
                        if ($tags_to_filter && $has_public_tags) {
                            $filters['PublicTags.HailID'] = $tags_to_filter;
                            $filter_publications = true;
                        }

                        if ($filter_publications) {
                            //IF we have a page filter, only show articles (publications don't have public tags)
                            $filters['ClassName'] = 'Firebrand\Hail\Models\Article';
                        }

                        if (count($filters) > 0) {
                            $objects = $objects->filter($filters);
                        }

                        $list->merge($objects);
                    }
                }
            }
        }
        $per_page = is_numeric($per_page) ? $per_page : $this->PaginationPerPage;

        //Add correct link to article when link from outside a hail page
        $page = $this;
        $list->each(function ($item) use ($page) {
            if ($item->getType() === "article") {
                $item->PageLink = $item->getLinkForPage($page);
            } else {
                $item->PageLink = $item->Link();
            }
        });
        //Limit the request if necessary
        if (is_numeric($limit)) {
            $list = $list->limit($limit);
        }

        return PaginatedList::create($list->sort('Created DESC'), $request)->setPageLength($per_page);
    }

    public function getFullHailList($limit = null, $tags_to_filter = null)
    {
        return $this->getHailList(0, $limit, $tags_to_filter);
    }

    public function getAllowedPublicTags()
    {
        $return_list = ['*' => 'All'];
        $hail_list = $this->List();
        $tags = $hail_list->getPublicTagsList();
        //Only include allowed Public Tags
        if (!empty($hail_list->IncludedPublicTagsIDs) && $hail_list->IncludedPublicTagsIDs !== "null") {
            $allowed_tags = json_decode($hail_list->IncludedPublicTagsIDs);
            foreach ($tags as $hail_id => $tag_name) {
                if (in_array($hail_id, $allowed_tags)) {
                    $return_list[] = [$hail_id => $tag_name];
                }
            }
        } else {
            $return_list = array_merge($return_list, $tags);
        }

        //Remove excluded public tags from list
        if (!empty($hail_list->ExcludedPublicTagsIDs) && $hail_list->ExcludedPublicTagsIDs !== "null") {
            $excluded_tags = json_decode($hail_list->ExcludedPublicTagsIDs);
            foreach ($return_list as $hail_id => $tag_name) {
                if (in_array($hail_id, $excluded_tags)) {
                    unset($return_list[$hail_id]);
                }
            }
        }

        return $return_list;
    }
}