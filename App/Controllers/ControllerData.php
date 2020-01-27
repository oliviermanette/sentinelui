<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use App\Models\ChartsManager;
use \App\Models\InclinometerManager;
use \App\Models\SiteManager;
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

  public function __construct()
  {
  }

  /**
   * Show the index page for when the user want to retrieve spectre data from the form
   *
   * @return void
   */
  public function searchSpectreAction()
  {

    $group_name = $_SESSION['group_name'];

    $siteManager = new SiteManager();
    $all_site = $siteManager->getSites($group_name);

    $equipementManager = new EquipementManager();
    $all_equipment = $equipementManager->getEquipements($group_name);

    $recordManager = new RecordManager();
    $brief_data_record = $recordManager->getBriefInfoFromRecord($group_name);


    $date_min_max = $recordManager->getDateMinMaxFromRecord();

    $min_date = $date_min_max[0];
    $max_date = $date_min_max[1];

    View::renderTemplate('Data/index.html', [
      'all_site'    => $all_site,
      'all_equipment' => $all_equipment,
      'min_date' => $min_date,
      'max_date' => $max_date,
      'brief_data_record' => $brief_data_record,
    ]);
  }

  /**
   * Show the index for When the user want to retrieve choc data from the form
   *
   * @return void
   */
  public function searchChocAction()
  {
    $siteManager = new SiteManager();
    $recordManager = new RecordManager();
    $equipementManager = new EquipementManager();
    $inclinometerManager = new InclinometerManager();
    $chocManager = new ChocManager();

    $group_name = $_SESSION['group_name'];

    $choc_data_arr = $chocManager->getAllChocDataForGroup($group_name);

    $equipementManager = new EquipementManager();
    $all_equipment = $equipementManager->getEquipements($group_name);

    $all_site = $siteManager->getSites($group_name);

    $date_min_max = $recordManager->getDateMinMaxFromRecord();

    $min_date = $date_min_max[0];
    $max_date = $date_min_max[1];


    View::renderTemplate('chocs/index.html', [
      'all_site'    => $all_site,
      'all_equipment' => $all_equipment,
      'min_date' => $min_date,
      'max_date' => $max_date,
      'choc_data_array' => $choc_data_arr,
    ]);
  }


  /*
  public function getResultsFromSpectreFormAction()
  {


    $equipementManager = new EquipementManager();
    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $siteManager = new SiteManager();
    $inclinometerManager = new InclinometerManager();
    $sensorManager = new SensorManager();

    if (isset($_POST['equipement_request'])) {
      $equipement_id = $_POST['equipement_request'];
    }
    if (isset($_POST['site_request'])) {
      $site_id = $_POST['site_request'];
    }
    if (isset($_POST['dateMin'])) {
      $dateMin = $_POST['dateMin'];
    }
    if (isset($_POST['dateMax'])) {
      $dateMax = $_POST['dateMax'];
    }
    $all_charts_data = $recordManager->getAllDataForChart($site_id, $equipement_id, $dateMin, $dateMax);
    
    $equipementInfo = $equipementManager->getEquipementFromId($equipement_id);
    $equipement_pylone = $equipementInfo['equipement'];
    $equipement_name = $equipementInfo['ligneHT'];
    //Get the sensor ID on the associated structure
    $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
    //Get the device number
    $device_number = $sensorManager->getDeviceNumberFromSensorId($sensor_id);

    $allStructureData = array(
      'sensor_id' => $sensor_id,
      'device_number' => $device_number,
      'ligneHT' => $equipement_name,
      'equipement' => $equipement_pylone,
      'equipementId' => $equipement_id,
    );

    View::renderTemplate('Data/viewDataSpectre.html', [
      'all_structure_data' => $allStructureData
    ]);
  }
*/
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
    $group_name = $_SESSION['group_name'];

    $equipementManager = new EquipementManager();
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
    if (isset($_POST['equipmentID'])) {
      $equipement_id = $_POST['equipmentID'];
      $searchSpecificEquipement = true;
    }

    if ($searchSpecificEquipement) {
      $equipementInfo = $equipementManager->getEquipementFromId($equipement_id);

      $equipement_pylone = $equipementInfo['equipement'];
      $equipement_name = $equipementInfo['ligneHT'];
      //Get the sensor ID on the associated structure
      $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
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

      $allStructureData[0] = array(
        'sensor_id' => $sensor_id,
        'status' => $status,
        'device_number' => $device_number,
        'ligneHT' => $equipement_name,
        'equipement' => $equipement_pylone,
        'equipementId' => $equipement_id,
        'lastDate' => $lastdate,
        'nb_choc_received_today' => $nb_choc_received_today,
        'lastChocPower' => $last_choc_power, 'temperature' => $temperature
      );
    } else {
      $equipements_site = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
      $allStructureData = array();
      $count = 0;

      foreach ($equipements_site as $equipement) {
        $index_array = "equipement_" . $count;
        //Get equipement data
        $equipement_id = $equipement['equipement_id'];
        $equipement_pylone = $equipement['equipement'];
        $equipement_name = $equipement['ligneHT'];

        //Get the sensor ID on the associated structure
        $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
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
          'lastChocPower' => $last_choc_power, 'temperature' => $temperature
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
    if (isset($_POST['siteID'])) {
      $siteID = $_POST['siteID'];
    }
    if (isset($_POST['equipmentID'])) {
      $equipement_id = $_POST['equipmentID'];
      $searchSpecificEquipement = true;
    }



    if ($searchSpecificEquipement) {

      $equipementInfo = $equipementManager->getEquipementFromId($equipement_id);

      $equipement_pylone = $equipementInfo['equipement'];
      $equipement_name = $equipementInfo['ligneHT'];
      #Retrieve the sensor id
      $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
      //print_r($equipement_id);
      $nb_choc_per_day = $chocManager->getNbChocPerDayForSensor($sensor_id);
      //print_r($nb_choc_per_day);
      $power_choc_per_day = $chocManager->getPowerChocPerDayForSensor($sensor_id);

      //Get inclinometer data angle
      $angleDataXYZ = $inclinometerManager->getAngleXYZPerDayForSensor($sensor_id);

      $allStructureData["equipement_0"] = array(
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
        #Retrieve the sensor id
        $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
        //print_r($equipement_id);
        $nb_choc_per_day = $chocManager->getNbChocPerDayForSensor($sensor_id);
        //print_r($nb_choc_per_day);
        $power_choc_per_day = $chocManager->getPowerChocPerDayForSensor($sensor_id);

        //Get inclinometer data angle
        $angleDataXYZ = $inclinometerManager->getAngleXYZPerDayForSensor($sensor_id);

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
