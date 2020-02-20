<?php

namespace App\Controllers;

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
   * Show the index page for when the user want to retrieve spectre data from the form
   *  /search-data
   *
   * @return void
   */
  public function searchSpectreAction()
  {

    $user = Auth::getUser();
    $group_name = $user->getGroupName();

    $all_site = SiteManager::getSites($group_name);
    $all_equipment = EquipementManager::getEquipements($group_name);
    $date_min_max = RecordManager::getDateMinMaxFromRecord();

    $min_date = $date_min_max[0];
    $max_date = $date_min_max[1];

    View::renderTemplate('Data/index.html', [
      'all_site'    => $all_site,
      'all_equipment' => $all_equipment,
      'min_date' => $min_date,
      'max_date' => $max_date,
    ]);
  }



  /**
   * Show the index for When the user want to retrieve choc data from the form
   * /search-choc
   *
   * @return void
   */
  public function searchChocAction()
  {
    $user = Auth::getUser();
    $group_name = $user->getGroupName();

    $choc_data_arr = ChocManager::getAllChocDataForGroup($group_name);
    $all_equipment = EquipementManager::getEquipements($group_name);
    $all_site = SiteManager::getSites($group_name);
    $date_min_max = RecordManager::getDateMinMaxFromRecord();

    $min_date = $date_min_max[0];
    $max_date = $date_min_max[1];

    View::renderTemplate('Chocs/index.html', [
      'all_site'    => $all_site,
      'all_equipment' => $all_equipment,
      'min_date' => $min_date,
      'max_date' => $max_date,
      'choc_data_array' => $choc_data_arr,
    ]);
  }



  /**
   * When the user perform the search through the form, display basic infos
   * sensor_id, device_number ,ligneHT, equipement, equipementId
   * last message received date, lastScore, nb_choc_received_today,
   * lastChocPower, temperature
   *
   * @return void
   */
  public function getResultsFromChocFormAction()
  {
    $user = Auth::getUser();
    $group_name = $user->getGroupName();

    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $siteManager = new SiteManager();
    $inclinometerManager = new InclinometerManager();
    $sensorManager = new SensorManager();

    $searchSpecificEquipement = false;
    if (isset($_POST['siteID'])) {
      $siteID = $_POST['siteID'];
    }
    if (!empty($_POST['equipmentID'])) {
      $equipement_id = $_POST['equipmentID'];
      $searchSpecificEquipement = true;
    }
    $startDate="";
    $endDate = "";
    if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {
      $startDate = $_POST['startDate'];
      $endDate = $_POST['endDate'];
      $searchByDate = true;
    }

    if ($searchSpecificEquipement) {
      $equipementInfo = EquipementManager::getEquipementFromId($equipement_id);

      $equipement_pylone = $equipementInfo['equipement'];
      $equipement_name = $equipementInfo['ligneHT'];
      //Get the sensor ID on the associated structure
      $sensor_id = EquipementManager::getSensorIdOnEquipement($equipement_id);
      //Get the device number
      $device_number = SensorManager::getDeviceNumberFromSensorId($sensor_id);
      //Get the latest temperature received
      $temperature = InclinometerManager::getLatestTemperatureForSensor($sensor_id);
      //Get the last date where the sensor received
      $lastdate = RecordManager::getDateLastReceivedData($equipement_id);
      //Get the status of the device
      $status = SensorManager::getStatusDevice($sensor_id);
      //Get the choc data
      $choc_power_data = ChocManager::getLastChocPowerValueForSensor($sensor_id);
      if (!empty($choc_power_data)) {
        $last_choc_power = $choc_power_data[0]['power'];
        $last_choc_date = $choc_power_data[0]['date'];
      } else {
        $last_choc_power = 0;
      }

      $nb_choc_received_today = $chocManager->getNbChocReceivedTodayForSensor($sensor_id);
      $nb_choc_received_today = $nb_choc_received_today['nb_choc_today'];

      $allStructureData[0] = array(
        'sensor_id' => $sensor_id,
        'status' => $status,
        'device_number' => $device_number,
        'ligneHT' => $equipement_name,
        'equipement' => $equipement_pylone,
        'equipementId' => $equipement_id,
        'lastDate' => $lastdate,
        'nb_choc_received_today' => $nb_choc_received_today,
        'lastChocPower' => $last_choc_power, 'temperature' => $temperature,
        'startDate' => $startDate, 'endDate' => $endDate
      );
    } else {
      $equipements_site = EquipementManager::getEquipementsBySiteId($siteID, $group_name);
      $allStructureData = array();
      $count = 0;

      foreach ($equipements_site as $equipement) {
        $index_array = "equipement_" . $count;
        //Get equipement data
        $equipement_id = $equipement['equipement_id'];
        $equipement_pylone = $equipement['equipement'];
        $equipement_name = $equipement['ligneHT'];
        
        //Get the sensor ID on the associated structure
        $sensor_id = EquipementManager::getSensorIdOnEquipement($equipement_id);
        //Get the device number
        $device_number = $sensorManager->getDeviceNumberFromSensorId($sensor_id);

        //Get the latest temperature received
        $temperature = $inclinometerManager->getLatestTemperatureForSensor($sensor_id);
        //Get the last date where the sensor received
        $lastdate = $recordManager->getDateLastReceivedData($equipement_id);
        //Get the status of the device
        $status = $sensorManager->getStatusDevice($sensor_id);
        //Get the choc data
        $choc_power_data = $chocManager->getLastChocPowerValueForSensor($sensor_id);
        if (!empty($choc_power_data)) {
          $last_choc_power = $choc_power_data[0]['power'];
          $last_choc_date = $choc_power_data[0]['date'];
        } else {
          $last_choc_power = 0;
        }

        $nb_choc_received_today = $chocManager->getNbChocReceivedTodayForSensor($sensor_id);
        $nb_choc_received_today = $nb_choc_received_today['nb_choc_today'];

        $allStructureData[$index_array] = array(
          'sensor_id' => $sensor_id,
          'status' => $status,
          'device_number' => $device_number,
          'ligneHT' => $equipement_name,
          'equipement' => $equipement_pylone,
          'equipementId' => $equipement_id,
          'lastDate' => $lastdate,
          'nb_choc_received_today' => $nb_choc_received_today,
          'lastChocPower' => $last_choc_power, 'temperature' => $temperature,
          'startDate' => $startDate, 'endDate' => $endDate
        );

        $count += 1;
      }
    }

    View::renderTemplate('Chocs/viewDataChocArray.html', [
      'all_structure_data' => $allStructureData
    ]);
  }

  public function getChartsSpectre(){
    $group_name = $_SESSION['group_name'];
    $site_id = $_POST["site_request"];
    $equipement_id = $_POST["equipement_request"];

    $drawAll = $_POST["drawAll"];
    $recordManager = new RecordManager();
    if ($drawAll == "true") {
      $getAll = true;
      $dateMin = $_POST["dateMin"];
      $dateMax = $_POST["dateMax"];

      $all_charts_data = $recordManager->getAllDataForChart($site_id, $equipement_id, $dateMin, $dateMax);
      print json_encode($all_charts_data);
    } else {
      $type_msg = $_POST["type_msg_request"];
      $sensor_id = $_POST["id_sensor_request"];
      $time_data =  $_POST['time_data_request'];

      $all_charts_data = $recordManager->getDataForSpecificChart($time_data, $type_msg, $sensor_id);
      print json_encode($all_charts_data);
    }
  }
  /**
   *Get all the chart data corresponding to choc frequencies
   *
   * @return void
   */
  public function getChartChocFrequencies()
  {
    $equipementManager = new EquipementManager();
    $chocManager = new ChocManager();

    if (isset($_POST['equipementID'])) {
      $equipementID = $_POST['equipementID'];
    }
    if (isset($_POST['time_data'])) {
      $timeDisplayData = $_POST['time_data'];
    }

    $sensor_id = $equipementManager->getSensorIdOnEquipement($equipementID);

    if ($timeDisplayData == "day") {
      $nb_choc = $chocManager->getNbChocPerDayForSensor($sensor_id);
    } else if ($timeDisplayData == "week") {
      $nb_choc = $chocManager->getNbChocPerWeekForSensor($sensor_id);
    } else if ($timeDisplayData == "month") {
      $nb_choc = $chocManager->getNbChocPerMonthForSensor($sensor_id);
    }

    print json_encode($nb_choc);
  }

  /**
   *Get all the chart data corresponding to the power of choc
   *
   * @return void
   */
  public function getChartPowerChocFrequencies()
  {
    $equipementManager = new EquipementManager();
    $chocManager = new ChocManager();

    if (isset($_POST['equipementID'])) {
      $equipementID = $_POST['equipementID'];
    }
    if (isset($_POST['time_data'])) {
      $timeDisplayData = $_POST['time_data'];
    }

    $sensor_id = $equipementManager->getSensorIdOnEquipement($equipementID);

    if ($timeDisplayData == "day") {
      $nb_choc = $chocManager->getPowerChocPerDayForSensor($sensor_id);
    } else if ($timeDisplayData == "week") {
      $nb_choc = $chocManager->getPowerChocPerWeekForSensor($sensor_id);
    } else if ($timeDisplayData == "month") {
      $nb_choc = $chocManager->getPowerChocPerMonthForSensor($sensor_id);
    }

    print json_encode($nb_choc);
  }


  /**
   * Get all the chart data corresponding to choc
   * number of shocks per day
   * Shock power per day
   * Angle of each sensor per day
   *
   * @return void
   */
  public function getChartsChocAction()
  {
    $group_name = $_SESSION['group_name'];

    $equipementManager = new EquipementManager();
    $inclinometerManager = new InclinometerManager();
    $chocManager = new ChocManager();

    $searchSpecificEquipement = false;
    $searchByDate = false;
    if (!empty($_POST['siteID'])) {
      $siteID = $_POST['siteID'];
    }
    if (!empty($_POST['equipmentID'])) {
      $equipement_id = $_POST['equipmentID'];
      $searchSpecificEquipement = true;
    }
    if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {
      $startDate = $_POST['startDate'];
      $endDate = $_POST['endDate'];
      $searchByDate = true;
      
    }

    //Attention à la date valide (inferieur data d'activité et installation)

    if ($searchSpecificEquipement) {

      $equipementInfo = $equipementManager->getEquipementFromId($equipement_id);

      $equipement_pylone = $equipementInfo['equipement'];
      $equipement_name = $equipementInfo['ligneHT'];
      #Retrieve the sensor id
      $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
      $deveui = EquipementManager::getDeveuiSensorOnEquipement($equipement_id);
      if ($searchByDate){
        $nb_choc_per_day = ChocManager::getNbChocPerDayForDates($deveui, $startDate, $endDate);
        $power_choc_per_day = ChocManager::getPowerChocPerDayForDates($deveui, $startDate, $endDate);
        $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui, $startDate, $endDate);
      }else {
        $nb_choc_per_day = $chocManager->getNbChocPerDayForSensor($sensor_id);
        $power_choc_per_day = $chocManager->getPowerChocPerDayForSensor($sensor_id);
        $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui);
      }
            
      $allStructureData["equipement_0"] = array(
        
        'deveui' => $deveui,
        'sensor_id' => $sensor_id,
        'equipement_name' => $equipement_pylone,
        'equipementId' => $equipement_id,
        'nb_choc_per_day' => $nb_choc_per_day,
        'angleXYZ_per_day' => $angleDataXYZ,
        'power_choc_per_day' => $power_choc_per_day,
      );
    } else {
      $equipements_site = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
      $allStructureData = array();
      $count = 0;
      foreach ($equipements_site as $equipement) {
        $index_array = "equipement_" . $count;

        $equipement_id = $equipement['equipement_id'];
        $equipement_pylone = $equipement['equipement'];
        $equipement_name = $equipement['ligneHT'];

        $equipement_id = $equipements_site[$count]['equipement_id'];
        $deveui = EquipementManager::getDeveuiSensorOnEquipement($equipement_id);
        #Retrieve the sensor id
        $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
        //print_r($equipement_id);
       if ($searchByDate) {
          $nb_choc_per_day = ChocManager::getNbChocPerDayForDates($deveui, $startDate, $endDate);
          $power_choc_per_day = ChocManager::getPowerChocPerDayForDates($deveui, $startDate, $endDate);
          $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui, $startDate, $endDate);
        } else {
          $nb_choc_per_day = $chocManager->getNbChocPerDayForSensor($sensor_id);
          $power_choc_per_day = $chocManager->getPowerChocPerDayForSensor($sensor_id);
          //Get inclinometer data angle
          $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui);
        }

        $allStructureData[$index_array] = array(
          'sensor_id' => $sensor_id,
          'equipement_name' => $equipement_pylone,
          'equipementId' => $equipement_id,
          'nb_choc_per_day' => $nb_choc_per_day,
          'angleXYZ_per_day' => $angleDataXYZ,
          'power_choc_per_day' => $power_choc_per_day,
        );

        $count += 1;
      }
    }

    print json_encode($allStructureData);
  }

  public function downloadSpectreAction(){
    $equipement_id = $_GET['equipementID'];
    $site_id= $_GET['siteID'];
    $requestedDate = $_GET['requestedDate'];

    $allSubSpectresArr = SpectreManager::reconstituteSpectre($site_id, $equipement_id, $requestedDate);
    
    $timeSerie = new TimeSeries();
    $timeSerie->createFromSpectreArr($allSubSpectresArr);
    //print_r($timeSerie->getTimeSerieData());
    ControllerInit::downloadCSV($timeSerie->getTimeSerieData(), $requestedDate);

  }

  /**
   * Change structure when the user select the site in order to show only the structure
   * associated to a specific site
   *
   * @return void
   */
  public function changeEquipementAction()
  {

    $siteID = $_POST['site_id'];
    $group_name = $_SESSION['group_name'];

    $equipementManager = new EquipementManager();
    $all_equipment = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
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
    $recordManager = new RecordManager();
    $data = $recordManager->getAllRawRecord();
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
