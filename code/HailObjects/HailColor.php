<?php

/**
 * Color representation used by some Hail Objects.
 *
 * @author Maxime Rainville, Firebrand
 *
 * @version 1.0
 *
 * @property int Red
 * @property int Green
 * @property int Blue
 */
class HailColor extends DataObject
{
    private static $db = array(
        'Red' => 'Int',
        'Green' => 'Int',
        'Blue' => 'Int',
    );

    private static $api_access = true;

    /**
     * Map Hail Color data to local variable name.
     *
     * @param [type] $data [description]
     *
     * @return [type] [description]
     */
    public function import($data)
    {
        $this->Red = isset($data->red) ? $data->red : 0;
        $this->Green = isset($data->green) ? $data->green : 0;
        $this->Blue = isset($data->blue) ? $data->blue : 0;

        $this->write();
    }
}
