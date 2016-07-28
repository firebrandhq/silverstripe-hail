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

		if($notifiers) {
			foreach($notifiers as $notifier) {

				if(!class_exists($notifier)) {
					user_error("$notifier class does not exist");
				}

				$obj = new $notifier();
				$obj->sendNotification($message);
			}
		}

	}

}
