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

    public static function getHistoricalTemperatureData($latitude, $longitude, $startDate, $endDate){
        $stationId = TemperatureAPI::getStation($latitude, $longitude);
        $historicalTemperatureDataArr = TemperatureAPI::getTemperatureDataFromStation($stationId, $startDate, $endDate);

        return $historicalTemperatureDataArr;
    }

    private static function getStation($latitude, $longitude){
        $limit = 1;
        $API_KEY = \App\Config::WEATHERMETEO_STAT_API_KEY;
        $url = "https://api.meteostat.net/v1/stations/nearby?lat=".$latitude."&lon=".$longitude."&limit=".$limit."&key=".$API_KEY;
        $stationsArr = API::CallAPI("GET", $url);

        foreach ($stationsArr as $station){
            //$stationId = $station["id"];
            if (!empty($station)){
                $stationId= $station[0]["id"];
            }
        }
        return $stationId;

    }

    private static function getTemperatureDataFromStation($stationId, $startDate, $endDate){
        $API_KEY = \App\Config::WEATHERMETEO_STAT_API_KEY;
        $url = "https://api.meteostat.net/v1/history/daily?station=" . $stationId . "&start=" . $startDate . "&end=" . $endDate . "&key=" . $API_KEY;
        
        $temperatureDataArr = API::CallAPI("GET", $url);

        $historicalTemperatureDataArr = array();
        foreach ($temperatureDataArr["data"] as $temperatureData){
            $temperature = $temperatureData["temperature"];
            $date = $temperatureData["date"];
            $tmpDataArr = array("date" => $date, "temperature"=>$temperature);
            array_push($historicalTemperatureDataArr, $tmpDataArr);
            
        }
        //print_r($historicalTemperatureDataArr);
        return $historicalTemperatureDataArr;
    }



}