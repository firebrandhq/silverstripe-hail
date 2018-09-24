<?php

define('HAIL_DIR', ltrim(Director::makeRelative(realpath(__DIR__)), DIRECTORY_SEPARATOR));

define('HAIL_PATH', BASE_PATH . '/' . CMS_DIR);

// Ensure compatibility with PHP 7.2 ("object" is a reserved word),
// with SilverStripe 3.6 (using Object) and SilverStripe 3.7 (using SS_Object)
if (!class_exists('SS_Object')) {
    class_alias('Object', 'SS_Object');
}