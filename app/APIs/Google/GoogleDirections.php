<?php

namespace App\APIs\Google;

use App\APIs\CurlHelper;

class GoogleDirections 
{
    private static $default_options = [
        'alternatives' => true,
        'mode' => 'driving',
        'language' => 'fr',
        'units' => 'metric',
        'region' => 'ca',
        'departure_time' => 'now',
    ];
    
    public static function getDirections(Point $begin, Point $end, array $waypoints = [], array $options = [], array $curl_opts = []) 
    {
        $API_KEY = env('GOOGLE_API_KEY');
        
        // start and end
        $urlBase = "https://maps.googleapis.com/maps/api/directions/json".
                   "?origin=".$begin->lat.",".$begin->lng.
                   "&destination=".$end->lat.",".$end->lng;
        
        // waypoints
        if (count($waypoints) > 0) {
            $urlBase .= '&waypoints='.$waypoints[0]->lat.",".$waypoints[0]->lng;
            for ($i = 1; $i < count($waypoints); $i++) {
                $urlBase .= "|".$waypoints[$i]->lat.",".$waypoints[$i]->lng;
            }
        }
        
        // $options will override $default_options if there are any conflicts
        $opts = array_merge(static::$default_options, $options);
        
        // options
        foreach ($opts as $key => $val) {
            $urlBase .= "&$key=$val";
        }
        
        $urlBase .= "&key=$API_KEY";
        
        return CurlHelper::getPageContent($urlBase, $curl_opts);
    }
}