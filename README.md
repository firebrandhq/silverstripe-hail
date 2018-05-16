# Hail.to Integration for SilverStripe 4

If you need this module for SilverStripe 3 please refer to [this branch](https://github.com/firebrandhq/silverstripe-hail/tree/2.x).

## New Features

* Ready to use with default styling and templates
* Video header
* Hail Page
* TinyMCE plugin
* More configurable
* Simplified code base
* Emoji support as an option
* OpenGraph integration, see firebrandhq/silverstripe-hail-opengraph
* Silverstripe Elemental integration, see firebrandhq/silverstripe-hail-elemental
* ...

## Requirements

* [SilverStripe ^4.1](https://www.silverstripe.org/download)
* [guzzlehttp/guzzle ^6.3](https://github.com/guzzle/guzzle)
* [silverstripe/environmentcheck ^2.0](https://github.com/silverstripe/silverstripe-environmentcheck)
* Access to create cronjob (optional)

## Installation

Run the following command:

```sh
composer require firebrand/silverstripe-hail "^4"
```

Install Silverstripe Sake: 

```sh
cd your-webroot/
sudo ./vendor/bin/sake installsake
```

Add the following lines (adapt them to your environment) to your crontab:

```sh
* * * * * /your-webroot/sake dev/tasks/hail-fetch-queue
*/5 * * * * /your-webroot/sake dev/tasks/hail-check-status
0 * * * * /your-webroot/sake dev/tasks/hail-fetch-recurring
```

You can adapt the frequency of the hail-fetch-recurring job to your needs, it will always fetch up until previous fetch

##### Authorize Silverstripe to fetch from Hail:

1. Go to hail.to and signin, then go to your Developer Settings (https://hail.to/app/user/applications) and create a new application (Add new button)
2. Add you Hail Client ID and Client Secret to your .env file:
    ```
    HAIL_CLIENT_ID=[CLIENTID]
    HAIL_CLIENT_SECRET=[CLIENTSECRET]
    ```
3. Go to SilverStripe admin settings page (/admin/settings/), then on the Hail tab
4. Copy the Callback URL
5. Back to the Hail Developer Settings, Click "Add new" in the redirect URI section and paste the Callback URL
6. You are now ready to authorize your Hail application, go back to the SilverStripe Admin settings and click the "Authorise Silverstripe to Access Hail" button.
7. After the authorization process is complete, you will be able to select the Hail Organisation you want to fetch content from in the Admin Settings of silverstripe.
8. (Optional) You can globally exclude content with specific Public or Private tags in the Admin Settings of Silverstripe
9. Save your Admin Settings once you have selected a Hail Organisation

## Upgrade from older versions

This module has been re written for SilverStripe 4 and includes breaking changes compared to previous version.
Please perform a fresh install if you are upgrading from previous versions by removing and re installing the module.

## Configuration

