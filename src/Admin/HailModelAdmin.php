<?php

namespace Firebrand\Hail\Admin;

use Firebrand\Hail\Forms\GridFieldFetchButton;
use SilverStripe\Admin\ModelAdmin;

class HailModelAdmin extends ModelAdmin
{
    private static $managed_models = [
        'Firebrand\Hail\Models\Article',
        'Firebrand\Hail\Models\Publication',
        'Firebrand\Hail\Models\Image',
        'Firebrand\Hail\Models\Video',
        'Firebrand\Hail\Models\PublicTag',
        'Firebrand\Hail\Models\PrivateTag',
    ];
    private static $url_segment = 'hail';

    private static $menu_title = 'Hail';

    private static $menu_icon = 'vendor/firebrand/silverstripe-hail/client/dist/images/admin-icon.png';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName)->getConfig();

        $gridField
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldAddNewButton')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldDeleteAction')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldExportButton')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldPrintButton')
            ->removeComponentsByType('SilverStripe\Forms\GridField\GridFieldImportButton')
            ->addComponent(new GridFieldFetchButton('buttons-before-left'));

        return $form;
    }
}