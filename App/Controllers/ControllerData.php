<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\SiteManager;
use \App\Models\EquipementManager;
use \App\Models\RecordManager;


ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

// Initialize the session
//session_start();

class ControllerData extends Authenticated
{

  public $loggedin;

  public function __construct() {

  }

  public function indexAction(){

    $recordManager = new RecordManager();
    $temperature_data = $recordManager->getLatestTemperatureRecordByIdSensor("6");

    $allStructuresBySpecificSite = $recordManager->getAllStructuresBySiteId(28);

    $allScoreBySpecificSite = $recordManager->getAllStructuresScoresBySiteId(28);
    var_dump($allScoreBySpecificSite);
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
