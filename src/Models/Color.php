<?php

namespace Firebrand\Hail\Models;

/**
 * Hail Color DataObject
 *
 * There is no endpoint on Hail API to get colors, they come from other objects
 *
 * @package silverstripe-hail
 * @author Maxime Rainville, Firebrand
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 * @property int $Red
 * @property int $Green
 * @property int $Blue
 */
class Color extends ApiObject
{
    private static $table_name = "HailColor";
    private static $db = [
        'Red' => 'Int',
        'Green' => 'Int',
        'Blue' => 'Int',
    ];

    /**
     * Get the Color thumbnail
     *
     * Used in CMS for the Color LiteralField
     *
     * @param string $label
     *
     * @return string
     */
    public function getThumnailField($label)
    {
        return "<div class='form-group field lookup readonly '><label class='form__field-label'>$label color</label><div class='form__field-holder'><p style=\"background-color: rgb({$this->Red},{$this->Green},{$this->Blue}) !important;\" class='form-control-static readonly'>Red: {$this->Red} Green: {$this->Green} Blue: {$this->Blue}</p></div></div>";
    }
}
