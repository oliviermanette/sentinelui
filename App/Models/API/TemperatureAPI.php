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
    public static function getCurrentTemperature($latitude, $longitude, $API_NAME = "DARKSKY")
    {
        if ($API_NAME == "WEATHERSTACK") {
            $API_KEY = \App\Config::WEATHERSTACK_API_KEY;
            $url = "http://api.weatherstack.com/current?access_key=" . $API_KEY . "&query=" . $latitude . "," . $longitude . "&units=m";
            $resArr = API::CallAPI("GET", $url);
            $currentTemperature = $resArr["current"]["temperature"];
        } else if ($API_NAME == "DARKSKY") {
            $API_KEY = \App\Config::WEATHERDARK_SKY_API_KEY;
            $url = "https://api.darksky.net/forecast/" . $API_KEY . "/" . $latitude . "," . $longitude . "?lang=fr&units=si&exclude=minutely,hourly,daily,flags";
            $resArr = API::CallAPI("GET", $url);
            $currentTemperature = $resArr["currently"]["temperature"];
        }
        return $currentTemperature;
    }

    public static function getDataWeather($latitude, $longitude, $API_NAME = "DARKSKY")
    {
        //Darksy is now deprecated because has been bought by Apple
        if ($API_NAME == "DARKSKY") {
            $API_KEY = \App\Config::WEATHERDARK_SKY_API_KEY;
            $url = "https://api.darksky.net/forecast/" . $API_KEY . "/" . $latitude . "," . $longitude . "?lang=fr&units=si&exclude=minutely,hourly,daily";
            $responseArr = API::CallAPI("GET", $url);
        } else if ($API_NAME == "VISUALCROSSING") {
            $API_KEY = \App\Config::WEATHER_VISUALCROSSING_API_KEY;
            $url = "https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/weatherdata/forecast?contentType=json&key=" . $API_KEY . "&locations=" . $latitude . "," . $longitude . "&shortColumnNames=False&aggregateHours=24&unitGroup=metric";
            $responseArr = API::CallAPI("GET", $url);
        } else {
            $responseArr = array();
        }
        return $responseArr;
    }

    public static function getHistoricalTemperatureData($latitude, $longitude, $startDate, $endDate)
    {
        $stationId = TemperatureAPI::getStation($latitude, $longitude);
        $historicalTemperatureDataArr = TemperatureAPI::getTemperatureDataFromStation($stationId, $startDate, $endDate);

        return $historicalTemperatureDataArr;
    }

    /**
     * Meteostat api
     */
    public static function getStation($latitude, $longitude)
    {
        $limit = 1;
        $API_KEY = \App\Config::WEATHERMETEO_STAT_API_KEY;
        $url = "https://api.meteostat.net/v1/stations/nearby?lat=" . $latitude . "&lon=" . $longitude . "&limit=" . $limit . "&key=" . $API_KEY;
        $response = API::CallAPI("GET", $url);
        $stationsArr = $response["data"];

        foreach ($stationsArr as $station) {
            if (!empty($station)) {
                $stationId = $station["id"];
            }
        }
        return $stationId;
    }
    /**
     * Meteostat api
     */
    private static function getTemperatureDataFromStation($stationId, $startDate, $endDate)
    {
        $API_KEY = \App\Config::WEATHERMETEO_STAT_API_KEY;
        $url = "https://api.meteostat.net/v1/history/daily?station=" . $stationId . "&start=" . $startDate . "&end=" . $endDate . "&key=" . $API_KEY;

        $temperatureDataArr = API::CallAPI("GET", $url);

        $historicalTemperatureDataArr = array();
        foreach ($temperatureDataArr["data"] as $temperatureData) {
            $temperature = $temperatureData["temperature"];
            $date = $temperatureData["date"];
            $tmpDataArr = array("date" => $date, "temperature" => $temperature);
            array_push($historicalTemperatureDataArr, $tmpDataArr);
        }
        //print_r($historicalTemperatureDataArr);
        return $historicalTemperatureDataArr;
    }
}
