<?php

namespace Firebrand\Hail\Tasks;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Jobs\FetchJob;
use Firebrand\Hail\Models\ApiObject;
use SilverStripe\Dev\BuildTask;
use SilverStripe\SiteConfig\SiteConfig;

class FetchQueueTask extends BuildTask
{
    /**
     * @inheritdoc
     */
    private static $segment = "hail-fetch-queue";

    /**
     * @inheritdoc
     */
    protected $title = "Hail Fetch Queue Task";

    /**
     * @inheritdoc
     */
    protected $description = 'Check if there is any Hail Fetch Job in queue and process them';

    /**
     * @inheritdoc
     */
    public function run($request)
    {
        $jobs = FetchJob::get()->filter(['Status' => 'Starting'])->sort('Created ASC');
        //There should only be one job at a time, but we loop just in case
        foreach ($jobs as $job) {
            //To avoid double processing, change status before doing the rest
            $job->Status = "Running";
            $job->write();
            $fetchables = [];

            if ($job->ToFetch === "*") {
                $fetchables = ApiObject::$fetchables;
            } else {
                if (ApiObject::isFetchable(str_replace('-', '\\', $job->ToFetch))) {
                    $fetchables = [str_replace('-', '\\', $job->ToFetch)];
                }
            }
            if (count($fetchables) > 0) {
                //Hail Api Client
                $hail_api_client = new Client();

                //Get all Configured Organisations
                $config = SiteConfig::current_site_config();
                $orgs_ids = json_decode($config->HailOrgsIDs);
                if ($orgs_ids) {
                    //Update job fetchable count so it displays on the frontend
                    $job->GlobalTotal = count($fetchables) * count($orgs_ids);
                    $job->write();

                    foreach ($orgs_ids as $org_id) {
                        foreach ($fetchables as $fetchable) {
                            $fetchable::fetchForOrg($hail_api_client, $org_id, $job);

                            //Update count for frontend
                            $job->GlobalDone++;
                            $job->write();
                        }
                    }
                }
            }

            $job->Status = "Done";
            $job->write();
        }
    }

}