<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Models\Article;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\View\Requirements;

class HailPageController extends \PageController
{
    private static $type_map = [
        "Articles" => "Firebrand\Hail\Models\Article",
        "Publications" => "Firebrand\Hail\Models\Publication",
    ];
    private static $allowed_actions = [
        'article',
        'tag' => 'index',
    ];

    protected function init()
    {
        parent::init();
        if (Config::inst()->get(self::class, 'UseDefaultCss')) {
            Requirements::css(HAIL_DIR . '/client/dist/styles/hail.bundle.css');
        }
        Requirements::javascript(HAIL_DIR . '/client/dist/js/hail.bundle.js');
        if ($this->owner->PaginationStyle === "InfiniteScroll") {
            Requirements::javascript(HAIL_DIR . '/client/dist/js/jquery-ias.min.js');
            Requirements::javascript(HAIL_DIR . '/client/dist/js/infinite-load.js');
        }
    }

    public function article(HTTPRequest $request)
    {
        $params = $request->params();
        if ($params['ID']) {
            $article = Article::get()->filter(['HailID' => $params['ID']])->first();
        }
        if (!$params['ID'] || !isset($article) || !$article) {
            return $this->httpError(404, 'That article could not be found');
        }

        return [
            'Article' => $article
        ];
    }

    public function HailList()
    {
        $params = $this->getRequest()->params();
        $filter_publications = false;

        $list = new ArrayList();
        if ($this->owner->List()->exists()) {
            $hail_list = $this->owner->List();
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
        return PaginatedList::create($list->sort('Created DESC'), $this->getRequest())->setPageLength($this->owner->PaginationPerPage);
    }

    public function currentTagFilter()
    {
        $params = $this->getRequest()->params();
        if ($params['Action'] === "tag" && !empty($params['ID'])) {
            return $params['ID'];
        }

        return 'none';
    }
}