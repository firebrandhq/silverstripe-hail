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

    public function currentTagFilter()
    {
        $params = $this->getRequest()->params();
        if ($params['Action'] === "tag" && !empty($params['ID'])) {
            return $params['ID'];
        }

        return 'none';
    }
}