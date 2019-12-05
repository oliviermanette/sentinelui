<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\InclinometerManager;
use \App\Models\SiteManager;
use \App\Models\EquipementManager;
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

    $inclinometerManager = new InclinometerManager();
    $temperature_data = $inclinometerManager->getLatestTemperatureRecordByIdSensor("6");

    $equipementManager = new EquipementManager();
    $allStructuresBySpecificSite = $equipementManager->getAllStructuresBySiteId(28);

    $allScoreBySpecificSite = $equipementManager->getAllStructuresScoresBySiteId(28);

    View::renderTemplate('Data/Presentation.html', [
      'temperature_data'    => $temperature_data,
      'all_structure' => $allStructuresBySpecificSite,
      'all_score' => $allScoreBySpecificSite,
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
