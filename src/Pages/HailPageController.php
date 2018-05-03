<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Models\Article;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ArrayList;

class HailPageController extends \PageController
{
    private static $type_map = [
        "Articles" => "Firebrand\Hail\Models\Article",
        "Publications" => "Firebrand\Hail\Models\Publication",
    ];
    private static $allowed_actions = [
        'article',
    ];

    protected function init()
    {
        parent::init();
    }

    public function article(HTTPRequest $request)
    {
        $params = $request->params();
        if ($params['ID']) {
            $article = Article::get()->filter(['HailID' => $params['ID']])->first();
        }
        if (!$params['ID'] || !isset($article) || !$article) {
            return $this->httpError(404, 'That region could not be found');
        }

        return [
            'Article' => $article
        ];
    }
//    public function Link($action = null)
//    {
//        // Construct link with graceful handling of GET parameters
//        $link = Controller::join_links('teams', $action);
//
//        // Allow Versioned and other extension to update $link by reference.
//        $this->extend('updateLink', $link, $action);
//
//        return $link;
//    }

    public function HailList()
    {
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

                        if (count($filters) > 0) {
                            $objects = $objects->filter($filters);
                        }

                        $list->merge($objects);
                    }
                }
            }
        }
        return $list;
    }
}