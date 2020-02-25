<?php


namespace App\Controllers;

use \Core\View;
use \App\Models\RecordManager;
use App\Models\API\TemperatureAPI;

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
class ControllerDataObjenious extends \Core\Controller{


  /**
   * When Objenious send an uplink, it goes here
   * @return void
   */
  public function receiveRawDataFromObjeniousAction(){
    //Get the JSON content from the HTTP request
    $data = json_decode(file_get_contents('php://input'), true);
    //Parse the JSON content to insert into the DB
    error_log("\nData received\n" . json_encode($data));
    $historicalTemperatureDataArr = TemperatureAPI::getHistoricalTemperatureData("42.98812", "-0.42624", "2017-01-01", "2017-12-31");
    print_r($historicalTemperatureDataArr);
    //$stationId = TemperatureAPI::getStation("42.98812", "-0.42624");
    //TemperatureAPI::getTemperatureDataFromStation($stationId, "2017-01-01", "2017-12-31");
    //RecordManager::parseJsonDataAndInsert($data);
  }


  /**
   * TESTING PURPOSE
   * @return void
   */
  public function testChocAction()
  {
    //Get the JSON content from the HTTP request
    $data = json_decode(file_get_contents('php://input'), true);
    //Parse the JSON content to insert into the DB
    $recordManager = new RecordManager();
    $recordManager->parseJsonDataAndInsert($data);
  }



  
}
