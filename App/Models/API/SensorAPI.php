<?php


namespace App\Models\API;

use App\Utilities;


/**
 * 
 *
 * PHP version 7.0
 */
class SensorAPI
{

    /** 
     * Get the status of all the sensors (active/inactive)
     *
     * @param string $group_name the name of the group
     * @return array status (active/inactive)
     * 
     */
    public static function getNbStatutsSensorsFromApi($group_name)
    {
        $countActive = 0;
        $countInactive = 0;
        $listDeviceArr = SensorAPI::getListOfDevicesFromAPI();
        foreach ($listDeviceArr as $deviceArr) {
            $groupInfoArr = $deviceArr["group"];
            $link = $groupInfoArr["link"];
            //Take long time TODO
            $groupInfoArr = API::CallAPI("GET", $link);
            $nameInfo = $groupInfoArr["name"];
            //Because we get RTE (Reseau Transport Electricité) and we want just RTE
            $nameArr = explode(" ", $nameInfo);
            $name = $nameArr[0];
            if (strcmp($name, $group_name) == 0) {
                $status = $deviceArr["status"];
                if (strcmp($status, "active") == 0) {
                    $countActive++;
                }
                if (strcmp($status, "inactive") == 0) {
                    $countInactive++;
                }
            }
        }
        $statusArr = array("active" => $countActive, "inactive" => $countInactive);
        return $statusArr;
    }

    /** 
     * Get the device id on Objenious given the deveui of a sensor
     *
     * @param string $deveuiAsked the deveui of the sensor
     * @return int id of the device
     * 
     */
    public static function getDeviceIdObjeniousFromDeveui($deveuiAsked)
    {
        $listDeviceArr = SensorAPI::getListOfDevicesFromAPI();
        //print_r($listDeviceArr);
        foreach ($listDeviceArr as $deviceArr) {
            //print_r($deviceArr);
            $propertiesArr = $deviceArr["properties"];
            $deveui = $propertiesArr["deveui"];
            if (strcmp($deveui, $deveuiAsked) == 0) {
                $deviceIDObjenious = $deviceArr["id"];
                return $deviceIDObjenious;
            }
        }
    }

    /** 
     * Get the device id on Objenious given a label (name of the sensor)
     *
     * @param string $labelAsked the name of the sensor
     * @return int id of the device
     * 
     */
    public static function getDeviceIdObjeniousFromLabel($labelAsked)
    {
        $listDeviceArr = SensorAPI::getListOfDevicesFromAPI();
        foreach ($listDeviceArr as $deviceArr) {
            $label = $deviceArr["label"];
            if (strcmp($label, $labelAsked) == 0) {
                $deviceIDObjenious = $deviceArr["id"];
                return $deviceIDObjenious;
            }
        }
    }


    /** 
     * Get Deveui on Objenious given a label (name of the sensor)
     *
     * @param string $labelAsked the name of the sensor
     * @return int deveui of the device
     * 
     */
    public static function getDeveuiFromLabel($labelAsked)
    {
        $listDeviceArr = SensorAPI::getListOfDevicesFromAPI();
        foreach ($listDeviceArr as $deviceArr) {
            $label = $deviceArr["label"];
            if (strcmp($label, $labelAsked) == 0) {
                $propertiesArr = $deviceArr["properties"];
                $deveui = $propertiesArr["deveui"];
                return $deveui;
            }
        }
    }

    /**
     *
     * @return void
     */
    public static function getListOfDevicesFromAPI()
    {

        $url = "https://api.objenious.com/v1/devices";
        $listDevicesArr = API::CallAPI("GET", $url);

        return $listDevicesArr;
    }

    /**
     *
     * @return void
     */
    public static function getDeviceInfoFromAPI($device_id)
    {

        $url = "https://api.objenious.com/v1/devices/" . $device_id;
        $deviceInfo = API::CallAPI("GET", $url);

        return $deviceInfo;
    }



    /**
     * Reactivate a deactivated device. 
     * The reactivated device will be able to receive/send messages.
     * @return void
     */
    public static function reactivateDeviceFromAPI($device_id)
    {

        $url = "https://api.objenious.com/v1/devices/" . $device_id . "/reactivate";
        $resultAPI = API::CallAPI("POST", $url);

        return $resultAPI;
    }

    /**
     * Deactivate a device. 
     * Message sent to/from a deactivated device will not be processed.
     * @return void
     */
    public static function deactivateDeviceFromAPI($device_id)
    {

        $url = "https://api.objenious.com/v1/devices/" . $device_id . "/deactivate";
        $deviceInfo = API::CallAPI("POST", $url);

        return $deviceInfo;
    }

    /**
     *It archives the device with his data, and it creates a new device 
     *with the new deveui/appeui/appkey.
     * @return void
     */
    public static function replaceDeviceFromAPI($device_id)
    {

        $url = "https://api.objenious.com/v1/devices/" . $device_id . "/replace";
        $deviceInfo = API::CallAPI("POST", $url);

        return $deviceInfo;
    }


    /**
     * Display the state of a list of devices
     * The state of a device includes the following information : uplink/downlink counters, 
     * latest data sent by the device, timestamps of last messages & various network information..
     *
     * @return void
     */
    public static function getStateListOfDevicesStatesFromAPI($device_id)
    {

        $url = "https://api.objenious.com/v1/devices/states?id=" . $device_id;
        $results_api = API::CallAPI("GET", $url);
        $state_device = $results_api["states"];

        return $state_device;
    }

    /**
     * Display the state of a list of devices
     * The state of a device includes the following information : uplink/downlink counters, 
     * latest data sent by the device, timestamps of last messages & various network information..
     *
     * @return void
     */
    public static function getStateDeviceUsingIdFromAPI($device_id)
    {

        $url = "https://api.objenious.com/v1/devices/" . $device_id . "/state";
        $results_api = API::CallAPI("GET", $url);
        $state_device = $results_api["states"];

        return $state_device;
    }

    public static function getStateDeviceUsingDeveuiFromAPI($deveui)
    {

        $url = "https://api.objenious.com/v1/devices/lora:" . $deveui . "/state";
        $results_api = API::CallAPI("GET", $url);
        $state_device = $results_api["states"];

        return $state_device;
    }

    public static function getLocationDeviceFromAPI($device_id, $since = null, $until = null)
    {
        $url = "https://api.objenious.com/v1/devices/" . $device_id . "/locations";
        if (isset($since) && isset($until)) {
            $url .= "?since=" . $since . "&until=" . $until;
        }
        $results_api = API::CallAPI("GET", $url);
        $location_device = $results_api["locations"];

        return $location_device;
    }

    public static function getListGatewayDeviceForGroupFromAPI($device_group)
    {
        $url = "https://api.objenious.com/v1/gateways?group=" . $device_group;
        $results_api = API::CallAPI("GET", $url);

        return $results_api;
    }

    public static function getListDevicesProfileTemplateFromAPI()
    {
        $url = "https://api.objenious.com/v1/templates";
        $results_api = API::CallAPI("GET", $url);

        return $results_api;
    }

    public static function getListDevicesProfileFromAPI()
    {
        $url = "https://api.objenious.com/v1/profiles";
        $results_api = API::CallAPI("GET", $url);

        return $results_api;
    }

    public static function getDeviceProfileFromAPI($device_id)
    {
        $url = "https://api.objenious.com/v1/profiles/" . $device_id;
        $results_api = API::CallAPI("GET", $url);

        return $results_api;
    }


}