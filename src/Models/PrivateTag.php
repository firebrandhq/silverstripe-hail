<?php

namespace Firebrand\Hail\Models;

class PrivateTag extends PublicTag
{
    protected static $object_endpoint = "private-tags";
    private static $table_name = "HailPrivateTag";
    private static $many_many = [
        'Articles' => 'Firebrand\Hail\Models\Article',
        'Images' => 'Firebrand\Hail\Models\Image',
        'Videos' => 'Firebrand\Hail\Models\Video',
    ];
}
