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


    public static function generateMeteogramLink($site, $latitude, $longitude)
    {
        /*
        $link = <<<EOD
            https://nodeserver.cloud3squared.com/getMeteogram/%7B%22token%22%3A%22sub_H74rRdT0YY88ZA%22%2C%22chartWidth%22%3A%221000%22%2C%22placeName%22%3A%22$site%22%2C%22longPlaceName%22%3A%22$site%22%2C%22latitude%22%3A%22$latitude%22%2C%22longitude%22%3A%22$longitude%22%2C%22countryCode%22%3A%22FR%22%2C%22appLocale%22%3A%22fr%22%2C%22updateInterval%22%3A%22manual%22%2C%22timeMachineButton%22%3A%22true%22%2C%22notifications%22%3A%22true%22%2C%22theme%22%3A%22sand-background%22%2C%22chartFontGroup%22%3A%22Roboto%22%2C%22chartFontFamily%22%3A%22Roboto%22%2C%22provider%22%3A%22openweathermap.org%22%2C%22hoursToDisplay%22%3A%2278%22%2C%22hoursToSkip%22%3A%22-48%22%2C%22hoursAvailable%22%3A%2272%22%2C%22timeAxisLabelsAmPm%22%3A%22true%22%2C%22nowLineDashStyle%22%3A%22Solid%22%2C%22nowLineBandColor%22%3A%22%2311000000%22%2C%22temperatureMinMaxLabels%22%3A%22true%22%2C%22temperatureLabelsWindow%22%3A%22chart%22%2C%22precipitationColor%22%3A%22%23dd0080ff%22%2C%22precipitationSnow%22%3A%22true%22%2C%22precipitationSnowColor%22%3A%22%2377c6f2ff%22%2C%22precipitationProb%22%3A%22true%22%2C%22pressure%22%3A%22false%22%2C%22cloudiness%22%3A%22true%22%2C%22cloudinessColor%22%3A%22%23ab999999%22%2C%22windSpeed%22%3A%22true%22%2C%22compressionQuality%22%3A%2290%22%7D
        EOD;
        return $link;*/

        $link = "https://nodeserver.cloud3squared.com/getMeteogram/%7B%22token%22%3A%22sub_H74rRdT0YY88ZA%22%2C%22chartWidth%22%3A%221000%22%2C%22placeName%22%3A%22".$site."%22%2C%22longPlaceName%22%3A%22".$site."%22%2C%22latitude%22%3A%22".$latitude."%22%2C%22longitude%22%3A%22".$longitude."%22%2C%22countryCode%22%3A%22FR%22%2C%22appLocale%22%3A%22fr%22%2C%22updateInterval%22%3A%22manual%22%2C%22timeMachineButton%22%3A%22true%22%2C%22notifications%22%3A%22true%22%2C%22theme%22%3A%22sand-background%22%2C%22chartFontGroup%22%3A%22Roboto%22%2C%22chartFontFamily%22%3A%22Roboto%22%2C%22provider%22%3A%22openweathermap.org%22%2C%22hoursToDisplay%22%3A%2278%22%2C%22hoursToSkip%22%3A%22-48%22%2C%22hoursAvailable%22%3A%2272%22%2C%22timeAxisLabelsAmPm%22%3A%22true%22%2C%22nowLineDashStyle%22%3A%22Solid%22%2C%22nowLineBandColor%22%3A%22%2311000000%22%2C%22temperatureMinMaxLabels%22%3A%22true%22%2C%22temperatureLabelsWindow%22%3A%22chart%22%2C%22precipitationColor%22%3A%22%23dd0080ff%22%2C%22precipitationSnow%22%3A%22true%22%2C%22precipitationSnowColor%22%3A%22%2377c6f2ff%22%2C%22precipitationProb%22%3A%22true%22%2C%22pressure%22%3A%22false%22%2C%22cloudiness%22%3A%22true%22%2C%22cloudinessColor%22%3A%22%23ab999999%22%2C%22windSpeed%22%3A%22true%22%2C%22compressionQuality%22%3A%2290%22%7D";

        return $link;
    }

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

    public static function getCurrentDataWeather($latitude, $longitude, $API_NAME = "DARKSKY")
    {
        //Darksy is now deprecated because has been bought by Apple
        if ($API_NAME == "DARKSKY") {
            $API_KEY = \App\Config::WEATHERDARK_SKY_API_KEY;
            $url = "https://api.darksky.net/forecast/" . $API_KEY . "/" . $latitude . "," . $longitude . "?lang=fr&units=si&exclude=minutely,hourly,daily";
            $responseArr = API::CallAPI("GET", $url);
             return $responseArr;

        } else if ($API_NAME == "VISUALCROSSING") {
            $API_KEY = \App\Config::WEATHER_VISUALCROSSING_API_KEY;
            $url = "https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/weatherdata/forecast?contentType=json&key=" . $API_KEY . "&locations=" . $latitude . "," . $longitude . "&shortColumnNames=False&aggregateHours=24&unitGroup=metric";
            $responseArr = API::CallAPI("GET", $url);
            return Utilities::array_find_value_by_key($responseArr["locations"], "currentConditions");
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
