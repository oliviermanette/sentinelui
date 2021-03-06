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
use App\Models\SpectreManager;
use App\Models\UserManager;
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
  public function indexViewAction()
  {
    $user = Auth::getUser();

    //If part of superadmin, display all sensors
    if ($user->isSuperAdmin()) {
      $brief_data_record = RecordManager::getBriefInfoFromAllRecords();
      $nb_active_sensors = SensorManager::getNumberAllActiveSensors();
      $nb_inactive_sensors =  SensorManager::getNumberAllInactiveSensors();
      $nb_active_alerts = AlertManager::getNumberAllActiveAlerts();
    } else {
      $brief_data_record = RecordManager::getBriefInfoFromRecord($user->group_id);
      $nb_active_sensors = SensorManager::getNumberActiveSensor($user->group_id);
      $nb_inactive_sensors =  SensorManager::getNumberInactiveSensor($user->group_id);
      $nb_active_alerts = AlertManager::getNumberActiveAlertsForGroup($user->group_id);
    }


    //Create object txt that will contain the brief records
    Utilities::saveJsonObject($brief_data_record, "public/data/HomepageBriefDataRecord.json");

    $context = [
      'nb_active_sensors' => $nb_active_sensors,
      'nb_inactive_sensors' => $nb_inactive_sensors,
      'nb_active_alerts' => $nb_active_alerts,
      'brief_data_record' => $brief_data_record,
      'group_parent' => $user->group_parent,
    ];

    View::renderTemplate('Homepage/index.html', $context);
  }


  /**
   * Handle the map data
   *
   * @return void
   */
  public function loadDataMapAction()
  {
    $user = Auth::getUser();
    if ($user->isSuperAdmin()) {
      $data_map = RecordManager::getAllDataMap();
    } else {
      $data_map = RecordManager::getDataMapForGroup($user->group_id);
    }

    $arr = [];
    $inc = 0;
    foreach ($data_map as $row) {
      if (isset($row["latitude_sensor"]) && isset($row["longitude_sensor"])) {
        $jsonArrayObject = (array(
          'device_number' => $row["device_number"], 'deveui' => $row["deveui"], 'latitude_site' => $row["latitude_site"],
          'longitude_site' => $row["longitude_site"], 'latitude_sensor' => $row["latitude_sensor"], 'longitude_sensor' => $row["longitude_sensor"],
          'site' => $row["site"], 'transmission_line_name' => $row["transmission_line_name"], 'equipement' => $row["equipement"]
        ));
        $arr[$inc] = $jsonArrayObject;
        $inc++;
      }
    }
    $json_array = json_encode($arr);
    echo $json_array;
  }
}
