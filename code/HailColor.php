<?php

/**
 * Color representation used by some Hail Objects.
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 * @property int Red
 * @property int Green
 * @property int Blue
 */
class HailColor extends DataObject {

	private static $db = array(
		'Red' => 'Int',
		'Green' => 'Int',
		'Blue' => 'Int',
	);

	private static $api_access = true;

	/**
	 * Map Hail Color data to local variable name
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function import($data) {
		$this->Red = $data->red;
		$this->Green = $data->green;
		$this->Blue = $data->blue;

		$this->write();
	}

}
