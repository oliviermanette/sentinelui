<?php

namespace App\Models\API;

use App\Utilities;


class LocationAPI
{


    /**
     * List all zones. A device may be located in a zone.
     * @return void
     */
    public static function listAllZonesFromAPI($device_group)
    {
        $url = "https://api.objenious.com/v1/zones?group=" . $device_group;
        $zonesInfoArr = API::CallAPI("GET", $url);

        return $zonesInfoArr;
    }

    /**
     * Get a single zone.
     * @return void
     */
    public static function getZoneInfoFromAPI($zone_identifier)
    {
        $url = "https://api.objenious.com/v1/zones/" . $zone_identifier;
        $zoneInfo = API::CallAPI("GET", $url);

        return $zoneInfo;
    }


    /**
     * TODO
     * @return void
     */
    public static function createZoneFromAPI()
    {

        $url = "https://api.objenious.com/v1/zones";
        $deviceInfo = API::CallAPI("POST", $url);

        return $deviceInfo;
    }
}
