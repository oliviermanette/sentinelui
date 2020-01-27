<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\SensorManager;
use \App\Auth;
use \App\Flash;


/**
 * Sensors controller
 *
 * PHP version 7.0
 */
class ControllerSensors extends Authenticated
{

    public function __construct()
    {
    }
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
}
