<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Models\Article;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\Requirements;

class HailPageController extends \PageController
{
    private static $allowed_actions = [
        'article',
        'tag' => 'index',
    ];

    protected function init()
    {
        parent::init();

        if (Config::inst()->get(self::class, 'UseDefaultCss')) {
            Requirements::css('firebrand/silverstripe-hail: thirdparty/bootstrap/styles/bootstrap.min.css');
            Requirements::css('firebrand/silverstripe-hail: client/dist/styles/hail.bundle.css');
        }
        //Include jQuery and Bootstrap, refer to the readme to block those requirements and replace with your own
        Requirements::javascript('firebrand/silverstripe-hail: thirdparty/jquery/js/jquery.min.js');
        Requirements::javascript('firebrand/silverstripe-hail: thirdparty/bootstrap/js/bootstrap.bundle.min.js');
        //Hail logic
        Requirements::javascript('firebrand/silverstripe-hail: client/dist/js/hail.bundle.js');
        if ($this->owner->PaginationStyle === "InfiniteScroll") {
            Requirements::javascript('firebrand/silverstripe-hail: client/dist/js/jquery-ias.min.js');
            Requirements::javascript('firebrand/silverstripe-hail: client/dist/js/infinite-load.js');
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
        $data = [
            'Article' => $article,
            'Related' => null
        ];

        //If Related Articles are enabled on the page (from the CMS)
        if ($this->owner->EnableRelated === "Yes") {
            //Try to find 3 related articles
            if ($article->PublicTags()) {
                $related = Article::get()->filter(['PublicTags.ID' => $article->PublicTags()->map('ID', 'ID')->toArray()])->sort('Date DESC')->limit(3);
                if ($related->Count() > 0) {
                    $data['Related'] = $related;
                }
            }
        }

        return $data;
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