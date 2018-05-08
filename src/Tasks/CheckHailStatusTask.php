<?php

namespace Firebrand\Hail\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\EnvironmentCheck\EnvironmentCheckSuite;
use SilverStripe\SiteConfig\SiteConfig;

class CheckHailStatusTask extends BuildTask
{
    /**
     * @inheritdocs
     */
    private static $segment = "hail-check-status";

    /**
     * @inheritdocs
     */
    protected $title = "Hail Check Status Task";

    /**
     * @inheritdocs
     */
    protected $description = 'Check if the Hail API is accessible';

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