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

    /**
     * get all activities related to a specific type of  neuron
     * @param string $networkId the networkID
     * @param string $neuronType SENSORS, RELATION, SEGMENT OR CATEGORY
     * @return tab 
     */
    public static function getAllactivities($networkId, $neuronType)
    {
        $url = self::$BASE_URL . "/getAllActivityNeuronType/" . $neuronType . "/" . $networkId;
        $data = API::CallAPI2("GET", $url);
        return $data;
    }


    /**
     * get specific timeserie from a specific network
     * @param string $networkId the networkID
     * @param int $nb number of the timeserie we wantto see
     * @return img obj time serie chart
     */
    public static function getChartTimeSerie($networkId, $nb)
    {
        $url = self::$BASE_URL . "/showTimeSerieOrder/" . $nb . "/" . $networkId;
        $img = API::CallAPI2("GET", $url);
        return $img;
    }

    /**
     * compute and save the network input graph 
     * @param string $networkId the networkID
     * @return void
     */
    public static function setChartNetworkGraph($networkId)
    {
        $url = self::$BASE_URL . "/setImageBuffer/getNetworkGraph/" . $networkId;
        $img = API::CallAPI2("GET", $url);
    }

    /**
     * get the image of the network input graph 
     * @param string $networkId the networkID
     * @return void
     */
    public static function getChartNetworkGraph($networkId)
    {
        $url = self::$BASE_URL . "/getImageBuffer/getNetworkGraph/" . $networkId;
        $img = API::CallAPI2("GET", $url);

        return $img;
    }

    /**
     * compute and save the activity of a specific neuron type
     * @param string $networkId the networkID
     * @param string $neuronType SENSORS, RELATION, SEGMENT OR CATEGORY
     * @return void
     */
    public static function setChartActivityNeuron($networkId, $neuronType)
    {
        $url = self::$BASE_URL . "/setImageBuffer/drawActivityNeuronType/" . $neuronType . "/" . $networkId;
        $img = API::CallAPI2("GET", $url);
    }

    /**
     * get the activity of a specific neuron type
     * @param string $networkId the networkID
     * @param string $neuronType SENSORS, RELATION, SEGMENT OR CATEGORY
     * @return void
     */
    public static function getChartActivityNeuron($networkId, $neuronType)
    {
        $url = self::$BASE_URL . "/getImageBuffer/drawActivityNeuronType/" . $neuronType . "/" . $networkId;
        $img = API::CallAPI2("GET", $url);

        return $img;
    }


    /**
     * compute and save the detected category of a specific neuron type
     * @param string $networkId the networkID
     * @param string $neuronType SENSORS, RELATION, SEGMENT OR CATEGORY
     * @return void
     */
    public static function setChartDetectedCategory($networkId, $neuronType)
    {
        $url = self::$BASE_URL . "/setImageBuffer/drawDetectedCat/" . $neuronType . "/" . $networkId;
        $img = API::CallAPI2("GET", $url);
    }

    /**
     * get the detected category of a specific neuron type
     * @param string $networkId the networkID
     * @param string $neuronType SENSORS, RELATION, SEGMENT OR CATEGORY
     * @return void
     */
    public static function getChartDetectedCategory($networkId, $neuronType)
    {
        $url = self::$BASE_URL . "/getImageBuffer/drawDetectedCat/" . $neuronType . "/" . $networkId;
        $img = API::CallAPI2("GET", $url);

        return $img;
    }

    /**
     * get info for a specific neuron
     * @param string $networkId the networkID
     * @param int $neuronId if od the neuron. Must exist
     * @return string 
     */
    public static function getNeuronInfo($networkId, $neuronId)
    {
        $url = self::$BASE_URL . "/getNeuronInfo/" . $neuronId . "/" . $networkId;
        $str = API::CallAPI2("GET", $url);
        return $str;
    }


    public static function getSensorActivity($networkId, $TimeStamp)
    {
        $url = self::$BASE_URL . "/showSensorActivity/" . $TimeStamp . "/" . $networkId;
        $str = API::CallAPI2("GET", $url);
        return $str;
    }
}
