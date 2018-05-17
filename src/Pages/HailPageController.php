<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Models\Article;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\Requirements;


/**
 * HailPage Controller
 *
 * Allowed actions:
 * - article: display a full article
 * - tag: display the HailList filtered by a specific public tag
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 */
class HailPageController extends \PageController
{
    private static $allowed_actions = [
        'article',
        'tag' => 'index',
    ];

    protected function init()
    {
        parent::init();
        //You can disable the default styles from the config, see readme
        if (Config::inst()->get(self::class, 'UseDefaultCss')) {
            Requirements::css('firebrandhq/silverstripe-hail: thirdparty/bootstrap/styles/bootstrap.min.css');
            Requirements::css('firebrandhq/silverstripe-hail: client/dist/styles/hail.bundle.css');
        }
        //Include jQuery and Bootstrap, refer to the readme to block those requirements and replace with your own
        Requirements::javascript('firebrandhq/silverstripe-hail: thirdparty/jquery/js/jquery.min.js');
        Requirements::javascript('firebrandhq/silverstripe-hail: thirdparty/bootstrap/js/bootstrap.bundle.min.js');
        //Hail logic
        Requirements::javascript('firebrandhq/silverstripe-hail: client/dist/js/hail.bundle.js');
        if ($this->owner->PaginationStyle === "InfiniteScroll") {
            Requirements::javascript('firebrandhq/silverstripe-hail: client/dist/js/jquery-ias.min.js');
            Requirements::javascript('firebrandhq/silverstripe-hail: client/dist/js/infinite-load.js');
        }
    }

    /**
     * Render a Hail Article
     *
     * @param HTTPRequest $request
     *
     * @return array
     * @throws HTTPResponse_Exception
     */
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
            if ($article->PublicTags()->Count() > 0) {
                $related = Article::get()->filter(['PublicTags.ID' => $article->PublicTags()->map('ID', 'ID')->toArray()])->sort('Date DESC')->limit(3);
                if ($related->Count() > 0) {
                    $data['Related'] = $related;
                }
            }
        }

        return $data;
    }

    /**
     * Helper to get current tag filtering in templates
     *
     * @return string
     */
    public function currentTagFilter()
    {
        $params = $this->getRequest()->params();
        if ($params['Action'] === "tag" && !empty($params['ID'])) {
            return $params['ID'];
        }

        return 'none';
    }
}