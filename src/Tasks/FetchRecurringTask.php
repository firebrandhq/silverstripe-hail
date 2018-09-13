<?php

namespace Firebrand\Hail\Tasks;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Models\ApiObject;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\BuildTask;
use SilverStripe\SiteConfig\SiteConfig;


/**
 * Task to fetch all fetchable objects from the Hail API since last fetch
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 1.0
 */
class FetchRecurringTask extends BuildTask
{
    use Configurable;

    /**
     * @inheritdoc
     */
    private static $segment = "hail-fetch-recurring";

    /**
     * @inheritdoc
     */
    protected $title = "Hail Recurring Fetch Task";

    /**
     * @inheritdoc
     */
    protected $description = 'Will fetch all Hail objects since last fetch';

    /**
     * @inheritdoc
     */
    public function run($request)
    {
        $config = SiteConfig::current_site_config();
        $last_fetched = $config->HailLastFetched;
        $now = new \DateTime("now", new \DateTimeZone("UTC"));
        if ($last_fetched) {
            //Build request param
            $fetch_date = new \DateTime($last_fetched, new \DateTimeZone('UTC'));
            //Make sure we respect the Hail API date format
            $last_fetched = [
                "updated_start" => $fetch_date->format('Y-m-d H:i:s'),
                "updated_end" => $now->format('Y-m-d H:i:s'),
            ];
        }
        $fetchables = ApiObject::$fetchables;

        if (count($fetchables) > 0) {
            //Hail Api Client
            $hail_api_client = new Client();

            //Get all Configured Organisations
            $config = SiteConfig::current_site_config();
            $orgs_ids = json_decode($config->HailOrgsIDs);
            if ($orgs_ids) {
                try {
                    foreach ($orgs_ids as $org_id) {
                        foreach ($fetchables as $fetchable) {
                            $fetchable::fetchForOrg($hail_api_client, $org_id, null, $last_fetched, true);
                        }
                    }
                } catch (\Exception $exception) {
                    self::sendException($exception);
                    //Kill the process to be able to retry the same fetch later
                    die();
                }
            }

            //Update last fetched once done, use UTC as Hail stores updated_at in UTC
            $config->HailLastFetched = $now->format('Y-m-d H:i:s');
            $config->write();
        }
    }

    /**
     * Send exception to configured emails
     *
     * @param \Exception $exception
     */
    public static function sendException($exception)
    {
        $emails = Config::inst()->get(self::class, 'Emails');
        if ($emails) {
            $emails = explode(",", $emails);
            $email = new Email();
            $email
                ->setTo($emails)
                ->setSubject('SilverStripe Hail module fetch error on ' . SiteConfig::current_site_config()->getTitle() . ' (' . gethostname() . ')')
                ->setBody("<p>Hi,</p><p>An error occurred while fetching from the Hail API: </p> <p>{$exception->getMessage()}</p><p>Website name: " . SiteConfig::current_site_config()->getTitle() . "</p><p>Website Folder: " . Director::baseFolder() . "</p><p>Server hostname: " .  gethostname() . "</p>");
            $email->send();
        }
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        $description = $this->description;
        if ($last_fetched = SiteConfig::current_site_config()->HailLastFetched) {
            $description .= " (last recorded fetch: " . $last_fetched . ")";
        }

        return $description;
    }

}
