<?php

namespace Firebrand\Hail\Admin;

use Firebrand\Hail\Forms\GridFieldAttachmentDownloadButton;
use Firebrand\Hail\Forms\GridFieldFetchButton;
use Firebrand\Hail\Models\ApiObject;
use Firebrand\Hail\Models\Article;
use Firebrand\Hail\Models\Attachment;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\View\Requirements;

/**
 * Hail Model Admin
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 */
class HailModelAdmin extends ModelAdmin
{
    private static $managed_models = [
        'Firebrand\Hail\Models\Article',
        'Firebrand\Hail\Models\Publication',
        'Firebrand\Hail\Models\Attachment',
        'Firebrand\Hail\Models\Image',
        'Firebrand\Hail\Models\Video',
        'Firebrand\Hail\Models\PublicTag',
        'Firebrand\Hail\Models\PrivateTag',
    ];
    private static $url_segment = 'hail';
    private static $menu_title = 'Hail';
    private static $menu_icon = 'vendor/firebrandhq/silverstripe-hail/client/dist/images/admin-icon.png';

    public function getEditForm($id = null, $fields = null)
    {
        Requirements::javascript(HAIL_DIR . '/client/dist/js/hail.bundle.js');

        $form = parent::getEditForm($id, $fields);
        //Check if Hail API is down
        $config = $this->SiteConfig();
        $hail_down = (!empty($config->HailAPIStatusCurrent) && $config->HailAPIStatusCurrent !== "OK");

        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName)->getConfig();

        $gridField->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldAddNewButton')
                  ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldDeleteAction')
                  ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldExportButton')
                  ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldPrintButton')
                  ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldImportButton');
        //Only show Fetch button for fetchable objects
        if (ApiObject::isFetchable($this->modelClass) && !$hail_down) {
            $gridField->addComponent(new GridFieldFetchButton('buttons-before-left'));
        }

        if ($this->modelClass === Article::class) {
            $gridField->addComponent(new GridFieldDeleteAction());
        }

        if ($this->modelClass === Attachment::class) {
            $gridField->addComponent(new GridFieldAttachmentDownloadButton());
        }

        if ($hail_down) {
            $form->sessionMessage('The Hail API is down, nothing can be fetched at the moment.');
        }

        return $form;
    }
}