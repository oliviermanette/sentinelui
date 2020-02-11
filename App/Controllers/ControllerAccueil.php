<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\SiteManager;
use \App\Models\AlertManager;
use \App\Models\EquipementManager;
use App\Models\InclinometerManager;
use \App\Models\RecordManager;
use \App\Models\SensorManager;
use App\Utilities;

/**
 * Controller Accueil
 * Handle the data displayed on the homepage
 * PHP version 7.0
 */


class ControllerAccueil extends Authenticated
{

  public $loggedin;

  /**
  * Show the index page /
  *
  * @return void
  */
  public function indexAction()
  {
    $group_name = $_SESSION['group_name'];
;
    $sensorManager = new SensorManager();
    $recordManager = new RecordManager();
    $alertManager = new AlertManager();
    $brief_data_record = $recordManager->getBriefInfoFromRecord($group_name);
    $nb_active_sensors = $sensorManager->getNumberActiveSensorFromDB("RTE");
    $nb_inactive_sensors =  $sensorManager->getNumberInactiveSensorFromDB("RTE");
    $nb_active_alerts = $alertManager->getNumberActiveAlertsForGroup($group_name);
    
    //Create object txt that will contain the brief records
    Utilities::saveJsonObject($brief_data_record, "public/data/HomepageBriefDataRecord.json");

    View::renderTemplate('Homepage/accueil.html', [
      'nb_active_sensors' => $nb_active_sensors,
      'nb_inactive_sensors' => $nb_inactive_sensors,
      'nb_active_alerts' => $nb_active_alerts,
      'brief_data_record' => $brief_data_record,
    ]);

  }

  /**
   * Change structure when the user select the site in order to show only the structure
   * associated to a specific site
   *
   * @return void
   */
  public function changeEquipementAction(){

    $siteID = $_POST['site_id'];
    $group_name = $_SESSION['group_name'];

    $equipementManager = new EquipementManager();
    $all_equipment = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
    View::renderTemplate('Homepage/formSelect.html', [
      'all_equipment' => $all_equipment,
    ]);

  }

  /**
   * Handle the map data 
   *
   * @return void
   */
  public function loadDataMapAction(){
    $recordManager = new RecordManager();
    $group_name = $_SESSION['group_name'];

    $data_map = $recordManager->getDataMap($group_name);

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

  /**
   * allow the user to download raw data from the homepage
   *
   * @return void
   */
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

  /**
   * Get all charts 
   *
   * @return void
   */
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
