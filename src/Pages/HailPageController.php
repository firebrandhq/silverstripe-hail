<?php

namespace Firebrand\Hail\Pages;

use Firebrand\Hail\Models\Article;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

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
    private $article;
    private static $allowed_actions = [
        'article',
        'tag',
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
     * Return a breadcrumb trail to this page. Excludes "hidden" pages (with ShowInMenus=0) by default.
     *
     * @param int $maxDepth The maximum depth to traverse.
     * @param boolean $unlinked Whether to link page titles.
     * @param boolean|string $stopAtPageType ClassName of a page to stop the upwards traversal.
     * @param boolean $showHidden Include pages marked with the attribute ShowInMenus = 0
     * @return string The breadcrumb trail.
     */
    public function Breadcrumbs($maxDepth = 20, $unlinked = false, $stopAtPageType = false, $showHidden = false, $delimiter = '&raquo;')
    {
        $pages = $this->getBreadcrumbItems($maxDepth, $stopAtPageType, $showHidden);
        $template = SSViewer::create('BreadcrumbsTemplate');

        //Add the Hail Article at the end of the page list if needed
        if(!empty($this->article)) {
            $pages->push($this->article);
        }

        return $template->process($this->customise(new ArrayData(array(
            "Pages" => $pages,
            "Unlinked" => $unlinked,
            "Delimiter" => $delimiter,
        ))));
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
            //Try to find the article with the database ID field, to be backward compatible with old Hail module (after upgrade)
            if(!isset($article) || empty($article)) {
                $article = Article::get()->filter(['ID' => $params['ID']])->first();
            }
        }

        if (!$params['ID'] || !isset($article) || empty($article)) {
            return $this->httpError(404, 'That article could not be found');
        }
        $data = [
            'Article' => $article,
            'Related' => null
        ];
        
        //Store the current article so we can use it in other functions in the controller
        $this->article = $article;

        //If Related Articles are enabled on the page (from the CMS)
        if ($this->owner->EnableRelated === "Yes") {
            //Try to find 3 related articles
            if ($article->PublicTags()->Count() > 0) {
                $related = Article::get()->filter(['PublicTags.ID' => $article->PublicTags()->map('ID', 'ID')->toArray()])->exclude(['HailID' => $params['ID']])->sort('Date DESC')->limit(3);
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
