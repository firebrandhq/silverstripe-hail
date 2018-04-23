<?php

namespace Firebrand\Hail\Models;

class Color extends ApiObject
{
    private static $table_name = "HailColor";
    private static $db = [
        'Red' => 'Int',
        'Green' => 'Int',
        'Blue' => 'Int',
    ];
}
