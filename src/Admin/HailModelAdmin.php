<?php

namespace Firebrand\Hail\Admin;

use Firebrand\Hail\Forms\GridFieldFetchButton;
use Firebrand\Hail\Models\ApiObject;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\View\Requirements;

class HailModelAdmin extends ModelAdmin
{
    private static $managed_models = [
        'Firebrand\Hail\Models\Article',
        'Firebrand\Hail\Models\Publication',
        'Firebrand\Hail\Models\Image',
        'Firebrand\Hail\Models\Video',
        'Firebrand\Hail\Models\PublicTag',
        'Firebrand\Hail\Models\PrivateTag',
        'Firebrand\Hail\Lists\HailList',
    ];
    private static $url_segment = 'hail';

    private static $menu_title = 'Hail';

    private static $menu_icon = 'vendor/firebrand/silverstripe-hail/client/dist/images/admin-icon.png';

    public function getEditForm($id = null, $fields = null)
    {
        Requirements::javascript(HAIL_DIR . '/client/dist/js/hail.bundle.js');

        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName)->getConfig();

        $gridField
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldAddNewButton')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldDeleteAction')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldExportButton')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldPrintButton')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldImportButton');
        //Only show Fetch button for fetchable objects
        if (ApiObject::isFetchable($this->modelClass)) {
            $gridField->addComponent(new GridFieldFetchButton('buttons-before-left'));
        }

        return $form;
    }
}