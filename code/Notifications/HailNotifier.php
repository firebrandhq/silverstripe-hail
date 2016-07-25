<?php

interface HailNotifier {

	/**
	 * Creates a connection to the notification provider.
	 */
	public function __construct();

	/**
	 * Sends a notification
	 * @param string $message The message to send
	 */
	public function sendNotification($message);
    
}


