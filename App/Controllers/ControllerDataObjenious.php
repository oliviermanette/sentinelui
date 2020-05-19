<?php


namespace App\Controllers;

use \Core\View;
use \App\Utilities;
use \App\Models\RecordManager;
use \App\Models\InclinometerManager;
use \App\Models\EquipementManager;
use \App\Models\SpectreManager;
use \App\Models\SensorManager;
use App\Models\API\TemperatureAPI;
use App\Models\TemperatureManager;

ini_set('error_reporting', E_ALL);
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "./log/error.log");
/*
ControllerDataObjenious.php
author : Lirone Samoun

Briefly : get uplink data (sensors) from Objenious platform. In Objenious, a HTTP request is sent.
This class handle this HTTP request

/data

*/
class ControllerDataObjenious extends \Core\Controller
{


  /**
   * When Objenious send an uplink, it goes here
   * @return void
   */
  public function receiveRawDataFromObjeniousAction()
  {
    //Get the JSON content from the HTTP request
    $data = json_decode(file_get_contents('php://input'), true);
    //Parse the JSON content to insert into the DB
    $message_type = 0;
    error_log("\nData received\n" . json_encode($data), $message_type);
    error_log("\nData received\n" . json_encode($data), 3, "./logs/data_objenious.log");
    if (!empty($data)) {
      RecordManager::parseJsonDataAndInsert($data);
    } else {
      echo "\n No data received. \n";
    }
  }
}
