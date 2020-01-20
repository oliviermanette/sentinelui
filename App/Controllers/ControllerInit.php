<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\RecordManager;
use \App\Models\SpectreManager;
use \App\Models\TimeSeriesManager;
use \App\Flash;
use App\Utilities;



/**
 * Time Series controller
 * PHP version 7.0
 */

class ControllerInit extends \Core\Controller
{

    public function __construct()
    {
    }

    /**
     * TESTING PURPOSE
     * @return void
     */
    public function goTimeSeriesAction()
    {
        $group_name = "RTE";
        
        //1. INIT POOL
        $recordManager = new RecordManager();
        //$recordManager->initPool($group_name);

        $sensorIdArr = $recordManager->getAllSensorIdFromPool();
        //print_r($sensorIdArr);

        //Test TimeSeries
        $spectreManager = new SpectreManager();
        //Get all the sensor ID from Pool ID 
        $allSpectreArr = $spectreManager->reconstituteAllSpectreForSensor(5);
        //print_r($allSpectreArr);
   
        //Loop over all spectre received by a specific sensor
        $timeSeriesManager = new TimeSeriesManager();
        foreach ($allSpectreArr as $spectreArr){
            print_r($allSpectreArr);
            $timeSeriesManager->createTimeSeriesFromSpectreArr($spectreArr);
            exit();
        }
    }

    


}