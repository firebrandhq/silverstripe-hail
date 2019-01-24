<?php

namespace Firebrand\Hail\Tasks;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Jobs\FetchJob;
use Firebrand\Hail\Models\ApiObject;
use SilverStripe\Dev\BuildTask;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Task to fetch the jobs currently in queue
 *
 * @link {FetchJob}
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 */
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
        $is_cli = php_sapi_name() == "cli";
        if($is_cli){
            $output = new ConsoleOutput();
        }

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
                    try {
                        foreach ($orgs_ids as $org_id) {
                            if($is_cli){
                                $output->writeln("<info>----Fetching Organisation $org_id----</info>");
                            }
                            foreach ($fetchables as $fetchable) {
                                if($is_cli){
                                    $class = explode('\\', $fetchable);
                                    $output->writeln("<comment>Fetching " . array_pop($class) . "...</comment>");
                                }
                                $fetchable::fetchForOrg($hail_api_client, $org_id, $job, null, true);

                                //Update count for frontend
                                $job->GlobalDone++;
                                $job->write();
                            }
                            if($is_cli){
                                $output->writeln("<info>-------------------------------------</info>");
                            }
                        }
                    } catch (\Exception $exception) {
                        FetchRecurringTask::sendException($exception);
                        //Kill the process to be able to retry the same fetch later
                        $job->Status = "Error";
                        $job->write();
                        die();
                    }
                }
            }

            $job->Status = "Done";
            $job->write();
        }
    }

}