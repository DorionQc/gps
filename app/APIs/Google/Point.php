<?php

namespace App\APIs\Google;

class Point
{
    public $lat;
    public $lng;
    
    function __construct($_lat, $_lng) {
        $this->lat = $_lat;
        $this->lng = $_lng;
    }
}