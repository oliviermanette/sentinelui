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
    error_log("\nData received\n" . json_encode($data));

    RecordManager::parseJsonDataAndInsert($data);
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


  public function testSQLAction()
  {

    $deveui = '0004A30B00E829A7';
    $date_time_first_measure = '2020-03-29 20:49:36';
    //$variationArr = InclinometerManager::computeAverageDailyVariationPercentageAngleForLast($deveui, false, -1);
    //$height = EquipementManager::getEquipementHeightBySensorDeveui($deveui);
    $dataArr = TemperatureAPI::getCurrentDataWeather('43.86801','4.568677', $API_NAME = "DARKSKY");
    //print_r($dataArr["locations"]);
    //var_dump($dataArr);
    /*foreach ($dataArr as $data){
      print_r($data);
    }*/
    $res = Utilities::array_find_deep($dataArr, 'currently');
    print_r($res);
    $spectreManager = new SpectreManager();
    //$spectreManager->getActivityData($deveui);
    //$spectreManager->reconstituteAllSpectreForSensorFirstGeneration($deveui);
    //$spectreManager->getAllSubspectres($deveui, $date_time_first_measure);
  }
}
