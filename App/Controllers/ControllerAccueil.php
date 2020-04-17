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
  public function indexAction()
  {
    $user = Auth::getUser();
    $group_name = $user->getGroupName();

    $brief_data_record = RecordManager::getBriefInfoFromRecord($group_name);
    $nb_active_sensors = SensorManager::getNumberActiveSensor($group_name);
    $nb_inactive_sensors =  SensorManager::getNumberInactiveSensor($group_name);
    $nb_active_alerts = AlertManager::getNumberActiveAlertsForGroup($group_name);

    //Create object txt that will contain the brief records
    Utilities::saveJsonObject($brief_data_record, "public/data/HomepageBriefDataRecord.json");

    View::renderTemplate('Homepage/accueil.html', [
      'nb_active_sensors' => $nb_active_sensors,
      'nb_inactive_sensors' => $nb_inactive_sensors,
      'nb_active_alerts' => $nb_active_alerts,
      'brief_data_record' => $brief_data_record,
    ]);
  }


  /**
   * Handle the map data
   *
   * @return void
   */
  public function loadDataMapAction()
  {
    $user = Auth::getUser();

    $group = UserManager::findGroupById($user->id);

    $data_map = RecordManager::getDataMapForGroup(intval($group["group_id"]));

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



  public function getDataTableAfterSubmitAction()
  {
    $site_id = $_POST["site_request"];
    $equipement_id = $_POST["equipement_request"];

    if (isset($_POST["startDate"])) {
      $startDate = $_POST["startDate"];
    }
    if (isset($_POST["endDate"])) {
      $endDate = $_POST["endDate"];
    }
    $typeMSG = '';

    $recordManager = new RecordManager();
    $all_specific_msg = $recordManager->getAllSpecificMsgForSpecificId($site_id, $equipement_id, $typeMSG, $startDate, $endDate);

    View::renderTemplate('Homepage/viewTableDataSpecific.html', [
      'all_specific_msg'    => $all_specific_msg,
    ]);
  }

  /**
   * Get all charts
   *
   * @return void
   */
  public function getAllChartsAction()
  {
    $site_id = $_POST["site_request"];
    $equipement_id = $_POST["equipement_request"];
    $startDate = $_POST["startDate"];
    $endDate = $_POST["endDate"];

    $all_charts_data = RecordManager::getAllDataForChart($site_id, $equipement_id, $startDate, $endDate);
    print json_encode($all_charts_data);
  }
}
