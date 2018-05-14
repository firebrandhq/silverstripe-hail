<?php

define('HAIL_DIR', ltrim(\SilverStripe\Control\Director::makeRelative(realpath(__DIR__)), DIRECTORY_SEPARATOR));

\SilverStripe\Forms\HTMLEditor\HTMLEditorConfig::get('cms')->enablePlugins(['sshail' => HAIL_DIR .'/client/dist/js/tinymce/hail-plugin.js'])->insertButtonsAfter('sslink', 'sshail');