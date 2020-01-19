<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
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
   * Show the index page
   *
   * @return void
   */
  public function indexAction(){

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
   * When the user want to retrieve choc data from the form
   *
   * @return void
   */
  public function searchChocAction(){
    $siteManager = new SiteManager();
    $equipementManager = new EquipementManager();
    $inclinometerManager = new InclinometerManager();
    $chocManager = new ChocManager();

    $group_name = $_SESSION['group_name'];

    $sensor_id = $equipementManager->getSensorIdOnEquipement("247");
    $choc_data_arr = $chocManager->getAllChocDataForGroup($group_name);
    
    $all_site = $siteManager->getSites($group_name);

    View::renderTemplate('chocs/index.html', [
      'all_site'    => $all_site,
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
  public function refreshDataAction()
  {
    $equipementManager = new EquipementManager();
    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $siteManager = new SiteManager();
    $inclinometerManager = new InclinometerManager();
    $sensorManager = new SensorManager();

    if (isset($_POST['siteID'])) {
      $siteID = $_POST['siteID'];
    }


    $group_name = $_SESSION['group_name'];

    $all_site = $siteManager->getSites($group_name);

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
      //Get the last score from structure
      $score_array = $scoreManager->getLastScoreFromStructure($equipement_id);
      $score = $score_array["score_value"];
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
        'device_number' => $device_number,
        'ligneHT' => $equipement_name,
        'equipement' => $equipement_pylone,
        'equipementId' => $equipement_id,
        'lastDate' => $lastdate, 'lastScore' => $score,
        'nb_choc_received_today' => $nb_choc_received_today,
        'lastChocPower' => $last_choc_power, 'temperature' => $temperature
      );

      $count += 1;
    }

    View::renderTemplate('Chocs/viewDataChocArray.html', [
      'all_site'    => $all_site,
      'all_structure_data' => $allStructureData
    ]);
  }

  /**
   *Get all the chart data corresponding to choc frequencies
   *
   * @return void
   */
  public function getChartChocFrequencies(){
    $equipementManager = new EquipementManager();
    $chocManager = new ChocManager();

    if (isset($_POST['equipementID'])) {
      $equipementID = $_POST['equipementID'];
    }
    if (isset($_POST['time_data'])) {
      $timeDisplayData = $_POST['time_data'];
    }

    $sensor_id = $equipementManager->getSensorIdOnEquipement($equipementID);

    if ($timeDisplayData == "day"){
      $nb_choc = $chocManager->getNbChocPerDayForSensor($sensor_id);
    }else if($timeDisplayData == "week"){
      $nb_choc = $chocManager->getNbChocPerWeekForSensor($sensor_id);
    }else if ($timeDisplayData == "month") {
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
    $equipementManager = new EquipementManager();
    $inclinometerManager = new InclinometerManager();
    $chocManager = new ChocManager();
    
    if (isset($_POST['siteID'])) {
      $siteID = $_POST['siteID'];
    }
    
    $group_name = $_SESSION['group_name'];

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
    
    print json_encode($allStructureData);

  }

  /**
   * TESTING PURPOSE
   *
   * @return void
   */
  public function indexTestAction()
  {
    $equipementManager = new EquipementManager();
    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $siteManager = new SiteManager();
    $inclinometerManager = new InclinometerManager();

    $siteID = "27";
    if (isset($_POST['siteID'])) {
      $siteID = $_POST['siteID'];
    }

    $group_name = $_SESSION['group_name'];

    $all_site = $siteManager->getSites($group_name);

    $equipements_site = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
    $allStructureData = array();
    $count = 0;
    foreach ($equipements_site as $equipement) {
      $index_array = "equipement_" . $count;

      $equipement_id = $equipement['equipement_id'];
      $equipement_pylone = $equipement['equipement'];
      $equipement_name = $equipement['ligneHT'];

      $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
      $temperature = $inclinometerManager->getLatestTemperatureForSensor($sensor_id);

      $lastdate = $recordManager->getDateLastReceivedData($equipement_id);
      $score_array = $scoreManager->getLastScoreFromStructure($equipement_id);

      $score = $score_array["score_value"];

      $choc_power_data = $chocManager->getLastChocPowerValueForSensor($sensor_id);

      if (!empty($choc_power_data)) {
        $last_choc_power = $choc_power_data[0]['power'];
        $last_choc_date = $choc_power_data[0]['date'];
      } else {
        $last_choc_power = 0;
      }

      $nb_choc_received_today = $chocManager->getNbChocReceivedTodayForSensor($sensor_id);
      //var_dump($nb_choc_received_today);
      $nb_choc_received_today = $nb_choc_received_today['nb_choc_today'];
      //var_dump($nb_choc_received_today);

      $allStructureData[$index_array] = array(
        'ligneHT' => $equipement_name,
        'equipement' => $equipement_pylone,
        'lastDate' => $lastdate, 'lastScore' => $score,
        'nb_choc_received_today' => $nb_choc_received_today,
        'lastChocPower' => $last_choc_power, 'temperature' => $temperature
      );

      $count += 1;
    }

    View::renderTemplate('Data/index.html', [
      'all_site'    => $all_site,
      'all_structure_data' => $allStructureData
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
