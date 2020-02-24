<?php


namespace App\Models\API;

use App\Utilities;
use App\Config;


/**
 * 
 *
 * PHP version 7.0
 */
class TemperatureAPI
{


    /**
     *
     * @return void
     */
    public static function getCurrentTemperature($latitude, $longitude)
    {
        
        $API_KEY = \App\Config::WEATHERSTACK_API_KEY;
        $url = "http://api.weatherstack.com/current?access_key=".$API_KEY."&query=".$latitude.",".$longitude."&units=m";
        $resArr = API::CallAPI("GET", $url);

        $currentTemperature = $resArr["current"]["temperature"];

        return $currentTemperature;
    }


}