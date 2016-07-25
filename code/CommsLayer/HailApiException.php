<?php

class HailApiException extends Exception {

	/**
	 * A class list of notifiers to use. Must implement HailNotifier
	 * @see HailNotifier
	 * @var array
	 */
	private static $notifiers;

	protected $hailMessage = '';

	public function __construct($message = "", $code = 0, Throwable $previous = NULL) {

		$notifiers = Config::inst()->get('HailApiException', 'notifiers');

		foreach($notifiers as $notifier) {

			if(!class_exists($notifier)) {
				user_error("$notifier class does not exist");
			}

			if(!class_implements('HailNotifier')) {
				user_error("$notifier must implement HailNotifier");
			}

			$obj = new $notifier();
			$obj->sendNotification($message);
		}

	}

}
