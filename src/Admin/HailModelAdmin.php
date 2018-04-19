<?php

namespace Firebrand\Hail\Admin;


use SilverStripe\Admin\ModelAdmin;

class HailModelAdmin extends ModelAdmin
{
    private static $managed_models = [
        'Firebrand\Hail\Models\Article',
    ];
    private static $url_segment = 'hail';

    private static $menu_title = 'Hail';
}