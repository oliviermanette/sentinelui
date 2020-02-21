<?php


namespace App\Models\APIObjenious;

use App\Utilities;


/**
 * 
 *
 * PHP version 7.0
 */
class MessagesAPI
{

    public static function getMessagesExchangedDeviceUsingIdFromAPI($device_id, $since = null, $until = null)
    {

        $url = "https://api.objenious.com/v1/devices/" . $device_id . "/messages";
        if (isset($since) && isset($until)) {
            $url .= "?since=" . $since . "&until=" . $until;
        }
        $results_api = API::CallAPI("GET", $url);
        $messages_device = $results_api["messages"];

        return $messages_device;
    }


    public static function getMessagesExchangedDeviceUsingDeveuiFromAPI($deveui, $since = null, $until = null)
    {

        $url = "https://api.objenious.com/v1/devices/lora:" . $deveui . "/messages";
        if (isset($since) && isset($until)) {
            $url .= "?since=" . $since . "&until=" . $until;
        }
        $results_api = API::CallAPI("GET", $url);
        $messages_device = $results_api["messages"];

        return $messages_device;
    }


}