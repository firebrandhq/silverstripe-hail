<?php

namespace Firebrand\Hail\Controllers;

use Firebrand\Hail\Jobs\FetchJob;
use Firebrand\Hail\Models\ApiObject;
use Firebrand\Hail\Pages\HailPage;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class HailController extends Controller
{
    use Configurable;

    private static $allowed_actions = [
        'fetch',
        'fetchOneSync',
        'progress',
        'articles'
    ];

    private static $url_handlers = [
        'fetch/$Class/$Before' => 'fetch',
        'fetchOne/$Class/$HailID' => 'fetchOneSync',
        'progress' => 'progress',
        'articles' => 'articles',
    ];

    public function fetch(HTTPRequest $request)
    {
        if (!Security::getCurrentUser()) {
            return $this->makeJsonReponse(401, [
                'message' => 'Unauthorized.'
            ])->setStatusDescription('Unauthorized.');
        }

        $params = $request->params();

        //Check there is no running jobs
        $job_count = FetchJob::get()->filter(['Status:not' => 'Done'])->Count();
        if ($job_count > 0) {
            return $this->makeJsonReponse(400, [
                'message' => 'Only 1 job can be started at a time.'
            ])->setStatusDescription('Only 1 job can be started at a time.');
        }
        //Check we have Configured Hail Organisations
        $config = SiteConfig::current_site_config();
        $orgs_ids = json_decode($config->HailOrgsIDs);
        if (!$orgs_ids) {
            return $this->makeJsonReponse(400, [
                'message' => 'You need at least 1 Hail Organisation configured to be able to fetch.'
            ])->setStatusDescription('You need at least 1 Hail Organisation configured to be able to fetch.');
        }

        if (
            isset($params['Class']) &&
            (
                $params['Class'] === "*" ||
                ApiObject::isFetchable(str_replace("-", "\\", $params['Class']))
            )
        ) {
            //Add a job to the queue, will be processed by a cronjob
            $job = new FetchJob();
            $job->ToFetch = $params['Class'];
            $job->write();

            return $this->makeJsonReponse(200, [
                'message' => 'success'
            ]);
        }

        return $this->makeJsonReponse(400, [
            'message' => 'Invalid fetch request.'
        ])->setStatusDescription('Invalid fetch request.');
    }

    public function progress(HTTPRequest $request)
    {
        if (!Security::getCurrentUser()) {
            return $this->makeJsonReponse(401, [
                'message' => 'Unauthorized.'
            ])->setStatusDescription('Unauthorized.');
        }

        $latest_job = FetchJob::get()->sort('Created DESC')->first();

        if ($latest_job) {
            $map = $latest_job->toMap();

            return $this->makeJsonReponse(200, $map);
        }

        return $this->makeJsonReponse(200, [
            'message' => 'No hail job found.',
            'Status' => 'Done'
        ]);

    }

    public function fetchOneSync(HTTPRequest $request)
    {
        if (!Security::getCurrentUser()) {
            return $this->makeJsonReponse(401, [
                'message' => 'Unauthorized.'
            ])->setStatusDescription('Unauthorized.');
        }

        $params = $request->params();

        if (empty($params['Class']) || empty($params['HailID'])) {
            return $this->makeJsonReponse(400, [
                'message' => 'Invalid request.'
            ]);
        }
        $class_name = str_replace("-", "\\", $params['Class']);
        if (!class_exists($class_name)) {
            return $this->makeJsonReponse(400, [
                'message' => 'Invalid request.'
            ]);
        }
        $object = DataObject::get($class_name)->filter(['HailID' => $params['HailID']])->first();
        if (!$object) {
            return $this->makeJsonReponse(404, [
                'message' => 'Object does not exist.'
            ]);
        }

        //Refresh from Hail API
        $object->refresh();

        return $this->makeJsonReponse(200, [
            'message' => 'Success'
        ]);
    }

    public function articles(HTTPRequest $request)
    {
        if (!Security::getCurrentUser()) {
            return $this->makeJsonReponse(401, [
                'message' => 'Unauthorized.'
            ])->setStatusDescription('Unauthorized.');
        }

        $pages = HailPage::get();
        $articles = [['text' => 'Select an article']];
        foreach ($pages as $page) {
            $list = $page->getFullHailList();
            foreach ($list as $item) {
                $link = $item->getLinkForPage($page);
                if ($item->getType() === 'article') {
                    $link = Director::absoluteURL($link);
                }
                $create_at = isset($item->Date) ? $item->Date : $item->DueDate;
                $date = new \DateTime($create_at);
                $name = $date->format('d/m/Y') . ' - ' . $page->Title . ' - ' . $item->Title;
                $articles[] = [
                    'text' => $name,
                    'value' => $link
                ];
            }
        }

        return $this->makeJsonReponse(200, $articles);
    }

    public function makeJsonReponse($status_code, $body)
    {
        $this->getResponse()->setBody(json_encode($body, JSON_UNESCAPED_SLASHES));
        $this->getResponse()->setStatusCode($status_code);
        $this->getResponse()->addHeader("Content-type", "application/json");

        return $this->getResponse();
    }
}