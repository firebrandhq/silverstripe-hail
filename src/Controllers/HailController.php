<?php

namespace Firebrand\Hail\Controllers;

use Firebrand\Hail\Jobs\FetchJob;
use Firebrand\Hail\Models\ApiObject;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class HailController extends Controller
{
    private static $allowed_actions = [
        'fetch',
        'progress',
    ];

    private static $url_handlers = [
        'fetch/$Class/$Before' => 'fetch',
        'progress' => 'progress'
    ];

    public function fetch(HTTPRequest $request)
    {
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
        $member = Security::getCurrentUser() &&
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
        $latest_job = FetchJob::get()->filter(['Status:not' => 'Done'])->sort('Created DESC')->first();
        if ($latest_job) {
            $map = $latest_job->toMap();

            return $this->makeJsonReponse(200, $map);
        }

        return $this->makeJsonReponse(200, [
            'message' => 'No hail job in progress.',
            'Status' => 'Done'
        ]);

    }

    public function makeJsonReponse($status_code, $body)
    {
        $this->getResponse()->setBody(json_encode($body));
        $this->getResponse()->setStatusCode($status_code);
        $this->getResponse()->addHeader("Content-type", "application/json");

        return $this->getResponse();
    }
}