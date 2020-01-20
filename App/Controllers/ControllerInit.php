<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\RecordManager;
use \App\Models\SpectreManager;
use \App\Models\TimeSeriesManager;
use \App\Models\TimeSeries;
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

        //Get all the sensor ID from Pool ID 
        $sensorIdArr = $recordManager->getAllSensorIdFromPool();
        //Test TimeSeries
        $spectreManager = new SpectreManager();
        foreach ($sensorIdArr as $sensorID){
            //print_r($sensorID);
            $sensor_id = $sensorID["sensor_id"];
            
        }

        $allSpectreArr = $spectreManager->reconstituteAllSpectreForSensor(5);
        //print_r($allSpectreArr);
   
        //Loop over all spectre received by a specific sensor
        foreach ($allSpectreArr as $spectreArr){
            echo "\n==> NEW SPECTRE <== \n";
 
            $timeSerie = new TimeSeries();
            $timeSerie->createFromSpectreArr($spectreArr);
            $timeSerie->save();
        }
    }

    


}