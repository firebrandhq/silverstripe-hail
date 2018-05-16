![Hail.to](https://get.hail.to/img/logo-tag.png "hail.to")

# Hail.to Integration for SilverStripe 4 

If you need this module for SilverStripe 3 please refer to [this branch](https://github.com/firebrandhq/silverstripe-hail/tree/2.x).

## New Features

* Ready to use with Bootstrap 4.1 styles and templates
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
* jQuery and Bootstrap 4+ (included), [see jQuery and Boostrap requirements](#jquery-and-bootstrap-requirements)

## Upgrade from older versions

This module has been re written for SilverStripe 4 and includes breaking changes compared to previous version.
Please perform a fresh install if you are upgrading from previous versions by removing and re installing the module.

## Installation

**Run the following command:**

```sh
composer require firebrand/silverstripe-hail "^4"
```

**(Optional) Enable Emojis Support (has to be done before doing the dev/build):**

[See Emojis Support configuration](#emojis-support)

**Perform a dev/build (from sake or from a browser)**

**Install Silverstripe Sake:** 

```sh
cd your-webroot/
sudo ./vendor/bin/sake installsake
```

**Add the following lines (adapt them to your environment) to your crontab:**

```sh
* * * * * /your-webroot/sake dev/tasks/hail-fetch-queue
*/5 * * * * /your-webroot/sake dev/tasks/hail-check-status
0 * * * * /your-webroot/sake dev/tasks/hail-fetch-recurring
```

You can adapt the frequency of the hail-fetch-recurring job to your needs, it will always fetch up until previous fetch

#### Authorize Silverstripe to fetch from Hail:

1. Go to hail.to and signin, then go to your Developer Settings (https://hail.to/app/user/applications) and create a new application (Add new button)
2. Add the generated Hail Client ID and Client Secret to your .env file:

    ```
    HAIL_CLIENT_ID=[CLIENTID]
    HAIL_CLIENT_SECRET=[CLIENTSECRET]
    ```
3. Go to SilverStripe admin settings page (/admin/settings/), then on the Hail tab
4. Copy the Callback URL
5. Back to the Hail Developer Settings, Click "Add new" in the redirect URI section and paste the Callback URL
6. You are now ready to authorize your Hail application, go back to the SilverStripe Admin settings and click the "Authorise SilverStripe to Access Hail" button.
7. After the authorization process is complete, you will be able to select the Hail Organisation(s) you want to fetch content from in the Admin Settings of SilverStripe.
8. (Optional) You can globally exclude content with specific Public or Private tags in the Admin Settings of SilverStripe
9. Save your Admin Settings

You can now either wait for your cron job to fetch the content or force a full fetch from the Hail menu in SilverStripe CMS using the Fetch button (top left in the page).

## jQuery and Bootstrap requirements

We include jQuery 3.3.1 and Bootstrap 4.1 (javascript and css) in our Hail Page and Hail Articles by default.

If you need to include your own jQuery and/or Bootstrap (If you compiled Bootstrap from source or want to include those globally for example), simply block our requirement(s) by adding one or all the following to your PageController init() function:

```php
protected function init()
{
    parent::init();
    // You can include any CSS or JS required by your project here.
    // See: https://docs.silverstripe.org/en/developer_guides/templates/requirements/
    
    \SilverStripe\View\Requirements::block('firebrand/silverstripe-hail: thirdparty/bootstrap/styles/bootstrap.min.css');
    \SilverStripe\View\Requirements::block('firebrand/silverstripe-hail: thirdparty/jquery/js/jquery.min.js');
    \SilverStripe\View\Requirements::block('firebrand/silverstripe-hail: thirdparty/bootstrap/js/bootstrap.bundle.min.js');
}
```


## Configuration

The following yml configuration options are available for overwrite:

**Hail API Client configuration:**
- BaseApiUrl: Base URL of the Hail API
- AuthorizationUrl: Full URL of the Hail authorization
- RefreshRate: Time after which a Hail Object is considered outdated
- EnableEmojiSupport: See [Emojis Support configuration](#emojis-support)

*Default configuration:*
 
```yml
Firebrand\Hail\Api\Client:
  BaseApiUrl: 'https://hail.to/api/v1/'
  AuthorizationUrl: 'https://hail.to/oauth/authorise'
  RefreshRate: 86400
  EnableEmojiSupport: false
```

**Hail Page Controller configuration:**
- UseDefaultCss: true / false Enables the default styles on Hail Pages and articles (Using Bootstrap 4.1)

*Default configuration:*
 
```yml
Firebrand\Hail\Pages\HailPageController:
  UseDefaultCss: true
```

**Hail Recurring Fetch Task configuration:**
- Emails: Fetching errors will be sent to the following email list (comma separated), put to false if you want to disable the emails

*Default configuration:*
 
```yml
Firebrand\Hail\Tasks\FetchRecurringTask:
  Emails: 'developers@firebrand.nz'
```

## Emojis Support

**IMPORTANT:** Enabling Emojis Support will change the charset and collation of your SilverStripe database to **utf8mb4** and **utf8mb4_general_ci** respectively.

utf8mb4 is backward compatible with utf8 so it should work with any existing or new database, we still chose to disable the feature to avoid imposing this change.

To enable Emojis support please add the following to your SilverStripe yml config and perform a dev/build.

```yml
Firebrand\Hail\Api\Client:
  EnableEmojiSupport: true
```