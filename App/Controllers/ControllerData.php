<?php

namespace App\Controllers;

use ZipStream;
use \Core\View;
use \App\Auth;
use App\Models\ChartsManager;
use \App\Models\InclinometerManager;
use \App\Models\SiteManager;
use \App\Models\SpectreManager;
use \App\Models\TimeSeriesManager;
use \App\Models\TimeSeries;
use \App\Models\EquipementManager;
use \App\Models\SensorManager;
use \App\Models\ScoreManager;
use \App\Models\ChocManager;
use \App\Models\RecordManager;

/**
 * Controller data
 * Handle the data displayed on http://[...]/search-data and http://[...]/search-choc
 * Basically corresponds to the form when the user want to retrieve the data recevied by the sensors
 * PHP version 7.0
 */


class ControllerData extends Authenticated
{

  public $loggedin;




  /**
   * Change structure when the user select the site in order to show only the structure
   * associated to a specific site
   *
   * @return void
   */
  public function changeEquipementAction()
  {
    $user = Auth::getUser();
    $siteID = $_POST['site_id'];
    $equipementManager = new EquipementManager();

    $all_equipment = $equipementManager->getEquipementsBySiteId($siteID, $user->group_id);
    View::renderTemplate('Others/changeEquipementForm.html', [
      'all_equipment' => $all_equipment,
    ]);
  }

  /**
   * allow the user to download raw data from the homepage
   *
   * @return void
   */
  public function downloadRawDataAction()
  {

    $data = RecordManager::getAllRawRecord();
    //var_dump($raw_data);

    if ($_GET['exportData'] == "csv") {
      $timestamp = time();
      $filename = 'Export_data_sensors_' . $timestamp . '.csv';

      header('Content-Type: text/csv; charset=utf-8');
      header("Content-Disposition: attachment; filename=\"$filename\"");

      $columnNames = array();
      if (!empty($data)) {
        //We only need to loop through the first row of our result
        //in order to collate the column names.
        $firstRow = $data[0];
        foreach ($firstRow as $colName => $val) {
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
    } else if ($_GET['exportData'] == "excel") {
      $timestamp = time();
      $filename = 'Export_data_sensors_' . $timestamp . '.xls';

      header("Content-Type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=\"$filename\"");

      $isPrintHeader = false;

      $columnNames = array();
      if (!empty($data)) {
        //We only need to loop through the first row of our result
        //in order to collate the column names.
        $firstRow = $data[0];
        if (!$isPrintHeader) {
          foreach ($firstRow as $colName => $val) {
            echo $colName . "\t";
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

  public function getDataTableAfterSubmitAction()
  {
    $site_id = $_POST["site_request"];
    $deveui = $_POST["sensor_deveui_request"];

    if (isset($_POST["startDate"])) {
      $startDate = $_POST["startDate"];
    }
    if (isset($_POST["endDate"])) {
      $endDate = $_POST["endDate"];
    }
    $typeMSG = '';
    $all_specific_msg = RecordManager::getAllSpecificMsgFromSensor($deveui, $startDate, $endDate);

    View::renderTemplate('Homepage/viewTableDataSpecific.html', [
      'all_specific_msg'    => $all_specific_msg,
    ]);
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
