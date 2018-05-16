<?php

namespace Firebrand\Hail\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\EnvironmentCheck\EnvironmentCheckSuite;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Task to Check the status of the Hail API using Silverstripe Environment Check module
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 */
class CheckHailStatusTask extends BuildTask
{
    /**
     * @inheritdoc
     */
    private static $segment = "hail-check-status";
    /**
     * @inheritdoc
     */
    protected $title = "Hail Check Status Task";
    /**
     * @inheritdoc
     */
    protected $description = 'Check if the Hail API is accessible';

    /**
     * @inheritdoc
     */
    public function run($request)
    {
        $results = EnvironmentCheckSuite::inst('check')->run();
        $details = $results->Details();
        foreach ($details as $detail) {
            if ($detail->Check === "hail_status") {
                //Write current status to config to be able to fetch and display in CMS
                $config = SiteConfig::current_site_config();
                $config->HailAPIStatusCurrent = $detail->Status;
                $config->HailAPIStatusLastChecked = (new \DateTime())->format('Y-m-d H:i:s');
                $config->write();
            }
        }
    }
}