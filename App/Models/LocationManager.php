<?php

namespace App\Models;

use PDO;
use App\Controllers\ControllerDataObjenious;
use \App\Token;
use \App\Mail;
use \Core\View;


class LocationManager extends \Core\Model
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * List all zones. A device may be located in a zone.
     * @return void
     */
    public function listAllZonesFromAPI($device_group)
    {
        $url = "https://api.objenious.com/v1/zones?group=".$device_group;
        $zonesInfoArr = ControllerDataObjenious::CallAPI("GET", $url);

        return $zonesInfoArr;
    }

    /**
     * Get a single zone.
     * @return void
     */
    public function getZoneInfoFromAPI($zone_identifier)
    {
        $url = "https://api.objenious.com/v1/zones/".$zone_identifier;
        $zoneInfo = ControllerDataObjenious::CallAPI("GET", $url);

        return $zoneInfo;
    }


    /**
     * TODO
     * @return void
     */
    public function createZoneFromAPI()
    {

        $url = "https://api.objenious.com/v1/zones";
        $deviceInfo = ControllerDataObjenious::CallAPI("POST", $url);

        return $deviceInfo;
    }

}
