<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\InclinometerManager;
use \App\Models\SiteManager;
use \App\Models\EquipementManager;
use \App\Models\ScoreManager;
use \App\Models\ChocManager;
use \App\Models\RecordManager;


ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

/***

Affichage données structures - TEST

 ***/

class ControllerData extends Authenticated
{

  public $loggedin;

  public function __construct()
  {
  }

  public function testAction()
  {
    View::renderTemplate('card.html');
  }

  public function indexAction()
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
      $temperature = $inclinometerManager->getLatestTemperatureRecordByIdSensor($sensor_id);

      $lastdate = $recordManager->getDateLastReceivedData($equipement_id);
      $score_array = $scoreManager->getLastScoreFromStructure($equipement_id);

      $score = $score_array["score_value"];

      $choc_power_data = $chocManager->getLastChocPowerValueForSensor($sensor_id);

      if (!empty($choc_power_data)){
        $last_choc_power = $choc_power_data[0]['power'];
        $last_choc_date = $choc_power_data[0]['date'];
      }else {
        $last_choc_power = 0;
      }

      $nb_choc_received_today = $chocManager->getNbChocTodayForSensor($sensor_id);
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

  public function refreshDataAction()
  {
    $equipementManager = new EquipementManager();
    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $siteManager = new SiteManager();
    $inclinometerManager = new InclinometerManager();

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
      $temperature = $inclinometerManager->getLatestTemperatureRecordByIdSensor($sensor_id);

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
      $nb_choc_received_today = $chocManager->getNbChocTodayForSensor($sensor_id);
      $nb_choc_received_today = $nb_choc_received_today['nb_choc_today'];

      $allStructureData[$index_array] = array(
        'ligneHT' => $equipement_name,
        'equipement' => $equipement_pylone,
        'lastDate' => $lastdate, 'lastScore' => $score,
        'nb_choc_received_today' => $nb_choc_received_today,
        'lastChocPower' => $last_choc_power, 'temperature' => $temperature
      );

      $count += 1;
    }

    View::renderTemplate('Data/viewDataArray.html', [
      'all_site'    => $all_site,
      'all_structure_data' => $allStructureData
    ]);
  }

  public function getChartsChocAction()
  {
    $equipementManager = new EquipementManager();
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

      $nb_choc_per_day = $chocManager->getNbChocPerDayForSensor($sensor_id);
      $nb_choc_per_week = $chocManager->getNbChocPerWeekForSensor($sensor_id);
      $power_choc_per_day = $chocManager->getPowerChocForSensor($sensor_id);
      

      $allStructureData[$index_array] = array(
        'sensor_id' => $sensor_id,
        'equipement_name' => $equipement_pylone,
        'nb_choc_per_day' => $nb_choc_per_day,
        'nb_choc_per_week' => $nb_choc_per_week,
        'power_choc_per_day' => $power_choc_per_day,
      );

      $count += 1;
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
