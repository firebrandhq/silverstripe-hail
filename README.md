# SilverStripe Hail Integration

## Requirements

* SilverStripe 3.1
* [silverstripe/queuedjobs 2.8](https://github.com/silverstripe-australia/silverstripe-queuedjobs)
* [firebrandhq/searchable-dataobjects](https://github.com/firebrandhq/silverstripe-searchable-dataobjects)
* [silverstripe-australia/gridfieldextensions](https://github.com/silverstripe-australia/silverstripe-gridfieldextensions)
* [undefinedoffset/sortablegridfield](https://github.com/UndefinedOffset/SortableGridField)
* [colymba/gridfield-bulk-editing-tools](https://github.com/colymba/GridFieldBulkEditingTools)
* [nategood/httpful](https://github.com/nategood/httpful)
* [silverstripe/restfulserver](https://github.com/silverstripe/silverstripe-restfulserver)
* [league/oauth2-client](https://github.com/silverstripe/silverstripe-restfulserver)
* Access to create cronjob

## Installation
Run the following command:

```sh
composer require firebrandhq/silverstripe-hail "1.*"
```

## Configuring access to Hail
* Log in to the back end of Silverstripe and go to _Settings > Hail_.
* In a separate browser tab, log in to your Hail Account and go to _Account > Manage Developer Settings_.
  * Add a new application
  * Report the _client_id_ and _client_secret_ into the SilverStripe settings and save.
* In the Hail Application Developer settings, you must register which URLs will be used to callback SilverStripe.
  * In Hail, click the _Add new_ button in the _URI_ section of your Application's _developer settings_ page
  * In SilverStripe, copy _Redirect URL_ (e.g.: http://example.com/HailCallbackController) and paste that value in Hail.
* You'll want to repeat the previous step for all the environments those API credentials will be used.
* In the SilverStripe Hail Settings page, you should now be able to click the _Authorise SilverSrtipe to access Hail_ link.
  * This will redirect you to Hail, where you can authorise SilverStripe to access Hail. Just follow the steps.
* After authorising Hail, you can select an Organisation from which the content will be fetch in the SilverStripe Hail Settings.

### Defining a default HailHolder
Hail Holder pages are used to display your Hail Content. You can define multiple Hail Holders for your site and personalise them to display different content.

However, your Hail Content doesn't belong to any specific pages in SilverStripe. So in some context, SilverStripe won't know under which Hail Holder the articles need to be displayed. E.g: If a Hail Page is return in search results.

In the Hail Settings page of your SilverStripe site, you can choose a default HailHolder used to display Hail Harticle.

## Configuring Cronjob to fetch Hail Content
Fetching HailContent can be quite a long process if you have a lots of article. To simplify this process, several background task have been created. However, they require a few appropriate cronjobs to be defined.

The following instructions assumed your site is running in Apache on an Ubuntu/Debian system, but it should be similar on most other \*nix system.

To edit your crontab use the following command:
```
sudo crontab -u www-data -e
```

This will configure your cronjob to run under the web server user. This has the benefit of avoiding potential file conflicts.

### Periodic automatic full fetch
A specific dev task (HailFetchTask) has been created to fetch all your Hail content. You can access this task in your browser (e.g.: http://example.com/dev/tasks/HailFetchTask) however this request is likely to timeout. In most cases, you will want to schedule a regular cronjob to refresh your content.

Add the following entry to your crontab:
```
0 23 * * * /usr/bin/nice -n 19  /usr/bin/php /var/www/SS_lphs/framework/cli-script.php dev/tasks/HailFetchTask > /dev/null 2>&1
```

This will fetch all your Hail Content every day at 11PM. If you're running multiple SilverStripe sites with the Hail plugin on your server, try scheduling your cronjobs at slightly different time to avoid having all the jobs running simultaneously.

### Manual fetch
CMS users can trigger an immediate fetch if they so desire. This will schedule a background job using the [silverstripe/queuedjobs](https://github.com/silverstripe-australia/silverstripe-queuedjobs) plugin.

This plug-in also require a few cronjobs to run. Add the following entries to your crontab.

```
* * * * * /usr/bin/nice -n 19  php /var/www/SS_lphs/framework/cli-script.php dev/tasks/ProcessJobQueueTask > /dev/null 2>&1
* * * * * /usr/bin/nice -n 19  php /var/www/SS_lphs/framework/cli-script.php dev/tasks/ProcessJobQueueTask "queue=2" > /dev/null 2>&1
*/5 * * * * /usr/bin/nice -n 19  php /var/www/SS_lphs/framework/cli-script.php dev/tasks/ProcessJobQueueTask "queue=3" > /dev/null 2>&1
```

[View SilverStripe Queue Jobs - Installing and configuring](https://github.com/silverstripe-australia/silverstripe-queuedjobs/wiki/Installing-and-configuring) for more details
