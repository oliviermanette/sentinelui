<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\SensorManager;
use \App\Auth;
use App\Models\BatteryManager;

/**
 * Sensors controller
 *
 * PHP version 7.0
 */
class ControllerSensors extends Authenticated
{

    /**
     * Show the index page for /sensors
     *
     * @return void
     */
    public function indexAction()
    {
        $group_name = $_SESSION['group_name'];
        //Get some info from the device
        $infoArr = SensorManager::getBriefInfoForGroup($group_name);
        View::renderTemplate('Sensors/index.html', [
            'info_sensors_array' => $infoArr
        ]);
    }

    /**
     * Show the info page for each sensor
     *
     * @return void
     */
    public function infoAction(){
        $label_device = $this->route_params["deviceid"];
        $deveui = SensorManager::getDeveuiFromLabel($label_device);
        $id_objenious = SensorManager::getDeviceIdObjeniousFromLabel($label_device);
        //Get brief info from sensors
        $infoArr = SensorManager::getBriefInfoForSensor($deveui);
        $lastMsgReceived = SensorManager::getLastMessageReceivedFromDeveui($deveui);
        $lastBatteryLevel = SensorManager::getLastBatteryStateFromDeveui($deveui);
        $nbreTotMsg = SensorManager::getNbTotalMessagesFromDeveui($deveui);
        $infoArr['id_objenious'] = $id_objenious;
        $infoArr['lastMsgReceived'] = $lastMsgReceived;
        $infoArr['nbreTotMsg'] = $nbreTotMsg;
        $infoArr['lastBatteryLevel'] = $lastBatteryLevel[0]["battery_level"];

        $data_map_array = json_encode($infoArr);
        //print_r($infoArr);
        
        View::renderTemplate('Sensors/infoDevice.html', [
            'infoArr' => $infoArr,
            'data_map_array' => $data_map_array
        ]);

    }

    /**
     * Handle the map data 
     *
     * @return void
     */
    public function loadDataMapAction()
    {
       
        $json_array = json_encode($arr);
        echo $json_array;
    }
}
