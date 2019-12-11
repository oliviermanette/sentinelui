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

  public function __construct() {

  }

  public function testAction(){
    View::renderTemplate('card.html');
  }

  public function indexAction(){
    $equipementManager = new EquipementManager();
    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $siteManager = new SiteManager();
    $inclinometerManager = new InclinometerManager();

    $siteID = "27";
    if (isset($_POST['siteID'])){
       $siteID = $_POST['siteID'];
    }

    $group_name = $_SESSION['group_name'];

    $all_site = $siteManager->getSites($group_name);

    $equipements_site = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
    $allStructureData = array();
    $count = 0;
    foreach($equipements_site as $equipement){
      $index_array = "equipement_". $count;

      $equipement_id = $equipement['equipement_id'];
      $equipement_pylone = $equipement['equipement'];
      $equipement_name = $equipement['ligneHT'];

      $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
      $temperature = $inclinometerManager->getLatestTemperatureRecordByIdSensor($sensor_id);

      $lastdate = $recordManager->getDateLastReceivedData($equipement_id);
      $score_array = $scoreManager->getLastScoreFromStructure($equipement_id);

      $score = $score_array["score_value"];

      $choc_power_data = $chocManager->getLastChocbyIdSensor($sensor_id);
      $last_choc_power = $choc_power_data['power'];
      $last_choc_date = $choc_power_data['date'];

      $allStructureData[$index_array] = array('ligneHT' => $equipement_name,
      'equipement' => $equipement_pylone,
       'lastDate' =>$lastdate, 'lastScore' =>$score,
        'lastChocPower' =>$last_choc_power, 'temperature' =>$temperature);

      $count += 1;
    }

    View::renderTemplate('Data/index.html', [
      'all_site'    => $all_site,
      'all_structure_data' => $allStructureData
    ]);
  }

  public function refreshDataAction(){
    $equipementManager = new EquipementManager();
    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $siteManager = new SiteManager();
    $inclinometerManager = new InclinometerManager();

    if (isset($_POST['siteID'])){
      $siteID = $_POST['siteID'];
    }


    $group_name = $_SESSION['group_name'];

    $all_site = $siteManager->getSites($group_name);

    $equipements_site = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
    $allStructureData = array();
    $count = 0;
    foreach($equipements_site as $equipement){
      $index_array = "equipement_". $count;

      $equipement_id = $equipement['equipement_id'];
      $equipement_pylone = $equipement['equipement'];
      $equipement_name = $equipement['ligneHT'];

      $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
      $temperature = $inclinometerManager->getLatestTemperatureRecordByIdSensor($sensor_id);

      $lastdate = $recordManager->getDateLastReceivedData($equipement_id);
      $score_array = $scoreManager->getLastScoreFromStructure($equipement_id);

      $score = $score_array["score_value"];

      $choc_power_data = $chocManager->getLastChocbyIdSensor($sensor_id);
      $last_choc_power = $choc_power_data['power'];
      $last_choc_date = $choc_power_data['date'];

      $allStructureData[$index_array] = array('ligneHT' => $equipement_name,
      'equipement' => $equipement_pylone,
       'lastDate' =>$lastdate, 'lastScore' =>$score,
        'lastChocPower' =>$last_choc_power, 'temperature' =>$temperature);

      $count += 1;
    }

    View::renderTemplate('Data/viewDataArray.html', [
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
