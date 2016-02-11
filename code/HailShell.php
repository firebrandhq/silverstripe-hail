<?php

class HailShell extends Controller {

	private static $allowed_actions = array(
		'index'
	);

	public function init() {
		parent::init();

		// Don't do anything unless this controller is called through the web server
		if (php_sapi_name() !== 'cli') {
			die('This script can only be called via the command line.');
		};


	}

	public function index() {
		echo "Fetching Tags.\n";
		HailTag::fetch();

		echo "Fetching Articles and article content.\n";
		HailArticle::fetch();

		echo "Fetching Images.\n";
		HailImage::fetch();

		echo "Fetching Publications.\n";
		HailPublication::fetch();

		echo "Fetching Videos.\n";
		HailVideo::fetch();
	}

}
