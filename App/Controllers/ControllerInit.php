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
        ini_set('max_execution_time', 0);
        $group_name = "RTE";
        
        //1. INIT POOL
        $recordManager = new RecordManager();
        //$recordManager->initPool($group_name);

        //Get all the sensor ID from Pool ID 
        $sensorIdArr = $recordManager->getAllSensorIdFromPool();
        //Test TimeSeries
        $spectreManager = new SpectreManager();
        /*
        foreach ($sensorIdArr as $sensorID){
            
            $sensor_id = $sensorID["sensor_id"];
            echo "\n SENSOR ID : " . $sensor_id . "\n"; 
            $allSpectreArr = $spectreManager->reconstituteAllSpectreForSensor($sensor_id);
            //print_r($allSpectreArr);

            //Loop over all spectre received by a specific sensor
            foreach ($allSpectreArr as $spectreArr) {
                echo "\n==> NEW SPECTRE <== \n";

                $timeSerie = new TimeSeries();
                $timeSerie->createFromSpectreArr($spectreArr);
                $timeSerie->save();
                echo "Spectre saved \n"; 
            }
            
        }*/

    }

    


}