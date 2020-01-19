<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\SiteManager;
use \App\Models\AlertManager;
use \App\Models\EquipementManager;
use \App\Models\RecordManager;
use \App\Models\SensorManager;


ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);


class ControllerAccueil extends Authenticated
{

  public $loggedin;

  public function __construct() {

  }


  /**
  * Show the index page
  *
  * @return void
  */

  public function indexAction()
  {
    $group_name = $_SESSION['group_name'];

    $sensorManager = new SensorManager();
    $recordManager = new RecordManager();
    $alertManager = new AlertManager();
    $brief_data_record = $recordManager->getBriefInfoFromRecord($group_name);
    $nb_active_sensors = $sensorManager->getNumberActifSensor($group_name);
    $nb_inactive_sensors = $sensorManager->getNumberInactifSensor($group_name);
    $nb_active_alerts = $alertManager->getNumberActiveAlertsForGroup($group_name);


    View::renderTemplate('Homepage/accueil.html', [
      'nb_active_sensors' => $nb_active_sensors,
      'nb_inactive_sensors' => $nb_inactive_sensors,
      'nb_active_alerts' => $nb_active_alerts,
      'brief_data_record' => $brief_data_record,
    ]);

  }

  public function getSpecificInfoCardAction(){
    $recordManager = new RecordManager();
    //$temperature_data = $recordManager->getLatestTemperatureRecordByIdSensor("6");
    #var_dump($temp["temperature"]);
  }

  public function changeEquipementAction(){

    $siteID = $_POST['site_id'];
    $group_name = $_SESSION['group_name'];

    $equipementManager = new EquipementManager();
    $all_equipment = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
    View::renderTemplate('Homepage/formSelect.html', [
      'all_equipment' => $all_equipment,
    ]);

  }

  public function loadDataMapAction(){
    $recordManager = new RecordManager();
    $group_name = $_SESSION['group_name'];

    $data_map = $recordManager-> getDataMap($group_name);

    $arr = [];
    $inc = 0;
    foreach ($data_map as $row){
      $jsonArrayObject = (array('sensor_id' => $row["sensor_id"],'latitude_site' => $row["latitude_site"],
      'longitude_site' => $row["longitude_site"], 'latitude_sensor' => $row["latitude_sensor"], 'longitude_sensor' => $row["longitude_sensor"],
      'site' => $row["site"], 'equipement' => $row["equipement"]));
      $arr[$inc] = $jsonArrayObject;
      $inc++;
    }
    $json_array = json_encode($arr);
    echo $json_array;

  }

  public function downloadRawDataAction(){
    $recordManager = new RecordManager();
    $data = $recordManager->getAllRawRecord();
    //var_dump($raw_data);

    if ($_GET['exportData'] == "csv"){
      $timestamp = time();
      $filename = 'Export_data_sensors_' . $timestamp . '.csv';

      header('Content-Type: text/csv; charset=utf-8');
      header("Content-Disposition: attachment; filename=\"$filename\"");

      $columnNames = array();
      if(!empty($data)){
        //We only need to loop through the first row of our result
        //in order to collate the column names.
        $firstRow = $data[0];
        foreach($firstRow as $colName => $val){
          $columnNames[] = $colName;
        }
      }

      $output = fopen("php://output", "w");
      //Start off by writing the column names to the file.
      fputcsv($output, $columnNames);
      //If we want to personalize the names
      /*fputcsv($output, array('Deveui', 'Site', 'Equipement', 'Date Time',
      'payload', 'Type message', 'payload', 'Amplitude 1', 'Amplitude 2',
      'Time 1', 'Time 2', 'X', 'Y', 'Z', 'Temperature', 'Batterie'));*/
      //Then, loop through the rows and write them to the CSV file.
      foreach ($data as $row) {
        fputcsv($output, $row);
      }

      //Close the file pointer.
      fclose($output);
      exit();
    }
    else if ($_GET['exportData'] == "excel"){
      $timestamp = time();
      $filename = 'Export_data_sensors_' . $timestamp . '.xls';

      header("Content-Type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=\"$filename\"");

      $isPrintHeader = false;

      $columnNames = array();
      if(!empty($data)){
        //We only need to loop through the first row of our result
        //in order to collate the column names.
        $firstRow = $data[0];
        if (! $isPrintHeader) {
          foreach($firstRow as $colName => $val){
            echo $colName ."\t" ;
            //echo implode("\t", array_keys($colName)) . "\n";
            $isPrintHeader = true;
          }
          echo "\n";
        }
        foreach ($data as $row) {
          echo implode("\t", array_values($row)) . "\n";
        }
        echo "\n";
      }
    }

  }

  public function getDataTableAfterSubmitAction(){


    $site_id = $_POST["site_request"];
    $equipement_id = $_POST["equipement_request"];
    $dateMin = '';
    $dateMax = '';

    if (isset($_POST["dateMin"])){
      $dateMin = $_POST["dateMin"];
    }
    if (isset($_POST["dateMax"])){
      $dateMax = $_POST["dateMax"];
    }
    $typeMSG = '';

    $recordManager = new RecordManager();
    $all_specific_msg = $recordManager->getAllSpecificMsgForSpecificId($site_id, $equipement_id, $typeMSG, $dateMin, $dateMax );

    View::renderTemplate('Homepage/viewTableDataSpecific.html', [
      'all_specific_msg'    => $all_specific_msg,
    ]);
  }

  public function getAllChartsAction(){
    $site_id = $_POST["site_request"];
    $equipement_id = $_POST["equipement_request"];


    $drawAll = $_POST["drawAll"];
    $recordManager = new RecordManager();
    if ($drawAll == "true"){
      $getAll = true;
      $dateMin = $_POST["dateMin"];
      $dateMax = $_POST["dateMax"];

      $all_charts_data = $recordManager->getAllDataForChart($site_id, $equipement_id, $dateMin, $dateMax );
      print json_encode($all_charts_data);
    }else {
      $type_msg = $_POST["type_msg_request"];
      $sensor_id = $_POST["id_sensor_request"];
      $time_data =  $_POST['time_data_request'];

      $all_charts_data = $recordManager->getDataForSpecificChart($time_data, $type_msg, $sensor_id );
      print json_encode($all_charts_data);
    }

  }



  /**
  * After filter
  *
  * @return void
  */
  protected function after()
  {
    //echo " (after)";
  }
}
