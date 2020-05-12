<?php

namespace App\Models\API;

use App\Utilities;




class SentiveAPI
{

    private static $BASE_URL = "http://92.243.19.37:1807";

    /**
     * Reset all data
     * @return void
     */
    public static function reset($networkId = 0)
    {
        $url = self::$BASE_URL . "/reset/" . $networkId;
        $reset = API::CallAPI2("GET", $url);
        return $reset;
    }

    /**
     * get Version Sentive
     * @return string version sentive AI
     */
    public static function getVersion()
    {
        $url = self::$BASE_URL . "/version";
        $version = API::CallAPI2("GET", $url);
        return $version;
    }

    public static function addTimeSeries($networkId, $payload, $name = "DbTimeSeries")
    {
        $url = self::$BASE_URL . "/appendDf/" . $name . "/" . $networkId;

        $data = API::CallAPI2("POST", $url, $payload);

        return $data;
    }

    public static function runUnsupervised($networkId)
    {
        $url = self::$BASE_URL . "/runU/" . $networkId;
        $run = API::CallAPI2("GET", $url);

        return $run;
    }
}
