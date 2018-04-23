<?php

namespace Firebrand\Hail\Admin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldEditButton;

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
}