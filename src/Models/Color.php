<?php

namespace Firebrand\Hail\Models;

class Color extends ApiObject
{
    private static $table_name = "HailColor";
    private static $db = [
        'Red' => 'Int',
        'Green' => 'Int',
        'Blue' => 'Int',
    ];

    public function getThumnailField($label)
    {
        return "<div class='form-group field lookup readonly '><label class='form__field-label'>$label color</label><div class='form__field-holder'><p style=\"background-color: rgb({$this->Red},{$this->Green},{$this->Blue}) !important;\" class='form-control-static readonly'>Red: {$this->Red} Green: {$this->Green} Blue: {$this->Blue}</p></div></div>";
    }
}
