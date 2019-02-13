<?php

namespace Firebrand\Hail\Tasks;

use Firebrand\Hail\Api\Client;
use Firebrand\Hail\Models\ApiObject;
use Firebrand\Hail\Models\Organisation;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Output\ConsoleOutput;


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
        $is_cli = php_sapi_name() == "cli";
        if ($is_cli) {
            $output = new ConsoleOutput();
        }
        $config = SiteConfig::current_site_config();

        $fetchables = ApiObject::$fetchables;

        if (count($fetchables) > 0) {
            //Hail Api Client
            $hail_api_client = new Client();

            //Get all Configured Organisations
            $config = SiteConfig::current_site_config();
            $orgs_ids = json_decode($config->HailOrgsIDs);
            $now = new \DateTime("now", new \DateTimeZone("UTC"));

            if ($orgs_ids) {
                try {
                    foreach ($orgs_ids as $org_id) {
                        if ($is_cli) {
                            $output->writeln("<info>----Fetching Organisation $org_id----</info>");
                        }

                        //Last Fetched request limitation per organisation in case you add / remove orgs
                        $last_fetched = null;
                        $org = DataObject::get_one(Organisation::class, ['HailID' => $org_id]);
                        if ($org) {
                            $last_fetched = $org->LastFetched;
                            if ($last_fetched) {
                                //Build request param
                                $fetch_date = new \DateTime($last_fetched, new \DateTimeZone('UTC'));
                                //Make sure we respect the Hail API date format
                                $last_fetched = [
                                    "updated_start" => $fetch_date->format('Y-m-d H:i:s'),
                                    "updated_end" => $now->format('Y-m-d H:i:s'),
                                ];
                            }
                        }

                        foreach ($fetchables as $fetchable) {
                            if ($is_cli) {
                                $class = explode('\\', $fetchable);
                                $output->writeln("<comment>Fetching " . array_pop($class) . "...</comment>");
                            }
                            $fetchable::fetchForOrg($hail_api_client, $org_id, null, $last_fetched, true);
                        }

                        //Update last fetched once done, use UTC as Hail stores updated_at in UTC
                        $org->LastFetched = $now->format('Y-m-d H:i:s');
                        $org->write();

                        if ($is_cli) {
                            $output->writeln("<info>-------------------------------------</info>");
                        }
                    }
                } catch (\Exception $exception) {
                    self::sendException($exception);
                    //Kill the process to be able to retry the same fetch later
                    die();
                }
            }
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
                ->setBody("<p>Hi,</p><p>An error occurred while fetching from the Hail API: </p> <p>{$exception->getMessage()}</p><p>Website name: " . SiteConfig::current_site_config()->getTitle() . "</p><p>Website Folder: " . Director::baseFolder() . "</p><p>Server hostname: " . gethostname() . "</p>");
            $email->send();
        }
    }
}
