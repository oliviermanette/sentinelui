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

  public function indexAction(){
    $equipementManager = new EquipementManager();
    $recordManager = new RecordManager();
    $scoreManager = new ScoreManager();
    $chocManager = new ChocManager();
    $inclinometerManager = new InclinometerManager();

    $equipements_site = $equipementManager->getEquipementsBySiteId("28", "RTE");
    $allStructureData = array();
    $count = 0;
    foreach($equipements_site as $equipement){
      $index_array = "equipement_". $count;

      $equipement_id = $equipement['equipement_id'];
      $equipement_name = $equipement['ligneHT'];

      $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
      $temperature = $inclinometerManager->getLatestTemperatureRecordByIdSensor($sensor_id);

      $lastdate = $recordManager->getDateLastReceivedData($equipement_id);
      $score_array = $scoreManager->getLastScoreFromStructure($equipement_id);

      $score = $score_array["score_value"];

      $choc_power_data = $chocManager->getLastChocbyIdSensor($sensor_id);
      $last_choc_power = $choc_power_data['power'];
      $last_choc_date = $choc_power_data['date'];
      echo $last_choc_power ."\n";
      $allStructureData[$index_array] = array('ligneHT' => $equipement_name,
       'lastDate' =>$lastdate, 'lastScore' =>$score,
        'lastChocPower' =>$last_choc_power, 'temperature' =>$temperature);

      $count += 1;
    }

    View::renderTemplate('Data/presentation.html', [
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
