<?php

use Firebrand\Hail\Api\Client;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\ORM\Connect\MySQLDatabase;

define('HAIL_DIR', ltrim(Director::makeRelative(realpath(__DIR__)), DIRECTORY_SEPARATOR));

HTMLEditorConfig::get('cms')->enablePlugins(['sshail' => HAIL_DIR . '/client/dist/js/tinymce/hail-plugin.js'])->insertButtonsAfter('sslink', 'sshail');

//Emoji support needs the following charset / collation to work, it is disabled by default, see readme to enable
if (Config::inst()->get(Client::class, 'EnableEmojiSupport')) {
    MySQLDatabase::config()->set('connection_charset', 'utf8mb4');
    MySQLDatabase::config()->set('connection_collation', 'utf8mb4_unicode_ci');
    MySQLDatabase::config()->set('charset', 'utf8mb4');
    MySQLDatabase::config()->set('collation', 'utf8mb4_unicode_ci');
}
