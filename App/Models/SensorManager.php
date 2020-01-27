<?php

/*
SensorManager.php
author : Lirone Samoun

Briefly : 

*/

namespace App\Models;

use App\Config;
use App\Utilities;
use App\Controllers\ControllerDataObjenious;
use PDO;

class SensorManager extends \Core\Model
{

  public function __construct()
  {
  }

  /** 
   * Get the status of all the sensors (active/inactive)
   *
   * @param string $group_name the name of the group
   * @return array status (active/inactive)
   * 
   */
  public static function getNbStatutsSensorsFromApi($group_name){
    $countActive = 0;
    $countInactive = 0;
    $listDeviceArr = SensorManager::getListOfDevicesFromAPI();
    foreach ($listDeviceArr as $deviceArr) {
      $groupInfoArr = $deviceArr["group"];
      $link = $groupInfoArr["link"];
      //Take long time TODO
      $groupInfoArr = ControllerDataObjenious::CallAPI("GET", $link);
      $nameInfo = $groupInfoArr["name"];
      //Because we get RTE (Reseau Transport Electricité) and we want just RTE
      $nameArr = explode(" ", $nameInfo);
      $name = $nameArr[0];
      if (strcmp($name, $group_name) == 0) {
        $status = $deviceArr["status"];
        if (strcmp($status, "active") == 0){
          $countActive++;
        }
        if (strcmp($status, "inactive") == 0) {
          $countInactive++;
        }
      }
    }
    $statusArr = array("active"=>$countActive, "inactive"=>$countInactive);
    return $statusArr;
  }

  /** 
   * Get the deveui of a device given his id
   *
   * @param int $sensor_id
   * @return string deveui of the sensor
   * 
   */
  public function getDeveuiFromSensorId($sensor_id){
    $db = static::getDB();

    $sql_deveui_sensor = "SELECT deveui FROM `sensor` 
      WHERE id = :sensor_id ";

    $stmt = $db->prepare($sql_deveui_sensor);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $id_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $id_sensor[0];
    }
  }

  /** 
   * Get the device number of a device given his id
   *
   * @param int $sensor_id
   * @return string device number of the sensor
   * 
   */
  public function getDeviceNumberFromSensorId($sensor_id)
  {
    $db = static::getDB();

    $sql_deviceNb_sensor = "SELECT device_number FROM `sensor` 
      WHERE id = :sensor_id ";

    $stmt = $db->prepare($sql_deviceNb_sensor);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $id_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $id_sensor[0];
    }
  }
  /** 
   * Get the sensor id of a device given his deveui
   *
   * @param string $deveui
   * @return int sensor id
   * 
   */
  public function getSensorIdFromDeveui($deveui)
  {
    $db = static::getDB();

    $sql_id_sensor = "SELECT id FROM `sensor` 
      WHERE deveui = :deveui ";

    $stmt = $db->prepare($sql_id_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $id_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $id_sensor[0];
    }
  }

  /** 
   * Get basic info of sensors given a group name
   *
   * @param string $group_name the name of the group
   * @return array results of the query
   * id_device | groupe | device_number | ligneHT | equipement | last_message_received | status |date_installation
   * 
   */
  public static function getBriefInfoForGroup($group_name){
    $db = static::getDB();

    $sql_brief_info = "SELECT 
    id_device_db,
    groupe,
    device_number, 
    ligneHT, 
    equipement, 
    DATE_FORMAT(
      last_message_received, '%d/%m/%Y'
    ) AS `last_message_received` ,
    status,
    date_installation
  FROM 
    (
      SELECT 
        sensor.id AS 'id_device_db',
        sensor.device_number AS 'device_number', 
        sensor.deveui AS deveui,
        sensor.status AS status,
        sensor.installation_date AS date_installation,
        gn.name AS groupe,
        st.transmision_line_name AS `LigneHT`, 
        st.nom AS `equipement`, 
        Max(
          Date(r.date_time)
        ) AS `last_message_received` 
      FROM 
        record AS r 
        INNER JOIN structure AS st ON st.id = r.structure_id 
        INNER JOIN site AS s ON s.id = st.site_id 
        INNER JOIN sensor ON (sensor.id = r.sensor_id) 
        INNER JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id) 
        INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
      WHERE 
        gn.name = :group_name 
        AND Date(r.date_time) >= Date(sensor.installation_date) 
      GROUP BY 
        r.sensor_id, 
        st.nom, 
        s.nom, 
        st.transmision_line_name
    ) AS all_message_rte_sensor";

    $stmt = $db->prepare($sql_brief_info);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $resultsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultsArr;
    }
  }

  /** 
   * Get the sensor id from a specific site and equipement
   *
   * @param int $site_id if od the site
   * @param int $structure_id id of the structure
   * @return int id of the device
   * 
   */
  public static function getSensorIdFromEquipementAndSiteId($site_id, $structure_id){
    $db = static::getDB();

    $sql_id_sensor = "SELECT DISTINCT sensor.id FROM sensor
    LEFT JOIN record AS r ON (r.sensor_id = sensor.id)
    LEFT JOIN structure AS st ON (st.id = r.structure_id)
    LEFT JOIN site AS s ON (st.site_id = s.id)
    WHERE s.id = :site_id AND st.id = :structure_id";

    $stmt = $db->prepare($sql_id_sensor);
    $stmt->bindValue(':site_id', $site_id, PDO::PARAM_INT);
    $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $id_sensor = $stmt->fetch(PDO::FETCH_COLUMN);
      return $id_sensor;
    }
  }

  /** 
   * Get the device id on Objenious given the deveui of a sensor
   *
   * @param string $deveuiAsked the deveui of the sensor
   * @return int id of the device
   * 
   */
  public static function getDeviceIdObjeniousFromDeveui($deveuiAsked)
  {
    $listDeviceArr = SensorManager::getListOfDevicesFromAPI();
    //print_r($listDeviceArr);
    foreach ($listDeviceArr as $deviceArr) {
      //print_r($deviceArr);
      $propertiesArr = $deviceArr["properties"];
      $deveui = $propertiesArr["deveui"];
      if (strcmp($deveui, $deveuiAsked) == 0) {
        $deviceIDObjenious = $deviceArr["id"];
        return $deviceIDObjenious;
      }
    }
  }
  /** 
   * Get the device id on Objenious given a label (name of the sensor)
   *
   * @param string $labelAsked the name of the sensor
   * @return int id of the device
   * 
   */
  public static function getDeviceIdObjeniousFromLabel($labelAsked)
  {
    $listDeviceArr = SensorManager::getListOfDevicesFromAPI();
    foreach ($listDeviceArr as $deviceArr) {
      print_r($deviceArr);
      $label = $deviceArr["label"];
      if (strcmp($label, $labelAsked) == 0) {
        $deviceIDObjenious = $deviceArr["id"];
        return $deviceIDObjenious;
      }
    }
  }

  /**
   *
   * @return void
   */
  public static function getListOfDevicesFromAPI()
  {

    $url = "https://api.objenious.com/v1/devices";
    $listDevicesArr = ControllerDataObjenious::CallAPI("GET", $url);
    
    return $listDevicesArr;
  }

  /**
   *
   * @return void
   */
  public function getDeviceInfoFromAPI($device_id)
  {

    $url = "https://api.objenious.com/v1/devices/".$device_id;
    $deviceInfo = ControllerDataObjenious::CallAPI("GET", $url);

    return $deviceInfo;
  }



  /**
   * Reactivate a deactivated device. 
   * The reactivated device will be able to receive/send messages.
   * @return void
   */
  public function reactivateDeviceFromAPI($device_id)
  {

    $url = "https://api.objenious.com/v1/devices/".$device_id."/reactivate";
    $resultAPI = ControllerDataObjenious::CallAPI("POST", $url);

    return $resultAPI;
  }

  /**
   * Deactivate a device. 
   * Message sent to/from a deactivated device will not be processed.
   * @return void
   */
  public function deactivateDeviceFromAPI($device_id)
  {

    $url = "https://api.objenious.com/v1/devices/" . $device_id . "/deactivate";
    $deviceInfo = ControllerDataObjenious::CallAPI("POST", $url);

    return $deviceInfo;
  }

  /**
   *It archives the device with his data, and it creates a new device 
   *with the new deveui/appeui/appkey.
   * @return void
   */
  public function replaceDeviceFromAPI($device_id)
  {

    $url = "https://api.objenious.com/v1/devices/" . $device_id . "/replace";
    $deviceInfo = ControllerDataObjenious::CallAPI("POST", $url);

    return $deviceInfo;
  }


  /**
   * Display the state of a list of devices
   * The state of a device includes the following information : uplink/downlink counters, 
   * latest data sent by the device, timestamps of last messages & various network information..
   *
   * @return void
   */
  public function getStateListOfDevicesStatesFromAPI($device_id)
  {

    $url = "https://api.objenious.com/v1/devices/states?id=" . $device_id;
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);
    $state_device = $results_api["states"];

    return $state_device;
  }

  /**
   * Display the state of a list of devices
   * The state of a device includes the following information : uplink/downlink counters, 
   * latest data sent by the device, timestamps of last messages & various network information..
   *
   * @return void
   */
  public function getStateDeviceUsingIdFromAPI($device_id)
  {

    $url = "https://api.objenious.com/v1/devices/".$device_id."/state";
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);
    $state_device = $results_api["states"];

    return $state_device;
  }

  public function getStateDeviceUsingDeveuiFromAPI($deveui)
  {

    $url = "https://api.objenious.com/v1/devices/lora:" . $deveui . "/state";
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);
    $state_device = $results_api["states"];

    return $state_device;
  }

  public function getLocationDeviceFromAPI($device_id, $since = null, $until = null){
    $url = "https://api.objenious.com/v1/devices/" . $device_id . "/locations";
    if (isset($since) && isset($until)) {
      $url .= "?since=" . $since . "&until=" . $until;
    }
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);
    $location_device = $results_api["locations"];

    return $location_device;
  }

  public function getListGatewayDeviceForGroupFromAPI($device_group){
    $url = "https://api.objenious.com/v1/gateways?group=".$device_group;
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);

    return $results_api;
  }

  public function getListDevicesProfileTemplateFromAPI()
  {
    $url = "https://api.objenious.com/v1/templates";
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);

    return $results_api;
  }

  public function getListDevicesProfileFromAPI()
  {
    $url = "https://api.objenious.com/v1/profiles";
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);

    return $results_api;
  }

  public function getDeviceProfileFromAPI($device_id)
  {
    $url = "https://api.objenious.com/v1/profiles/".$device_id;
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);

    return $results_api;
  }


  public function getStatusDevice($sensor_id){
    $db = static::getDB();

    $sql_status_device = "SELECT sensor.status FROM sensor
      WHERE sensor.id = :sensor_id";

    $stmt = $db->prepare($sql_status_device);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $nb_actif_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $nb_actif_sensor[0];
    }
  }


  /**
   * Get the number of inactif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   */
  public function getNumberActiveSensorFromDB($group_name)
  {
    $db = static::getDB();

    $sql_nb_actif_sensor = "SELECT 
      COUNT(*) AS nb_active 
    FROM 
      sensor AS s 
      LEFT JOIN sensor_group AS gs ON (gs.sensor_id = s.id) 
      LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
    WHERE 
      gn.name = :group_name 
      AND s.status LIKE 'active'";

    $stmt = $db->prepare($sql_nb_actif_sensor);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $nb_actif_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $nb_actif_sensor[0];
    }
  }

  /**
   * Get the number of inactif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   */
  public function getNumberInactiveSensorFromDB($group_name)
  {
    $db = static::getDB();

    $sql_nb_actif_sensor = "SELECT 
      COUNT(*) AS nb_active 
    FROM 
      sensor AS s 
      LEFT JOIN sensor_group AS gs ON (gs.sensor_id = s.id) 
      LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
    WHERE 
      gn.name = :group_name 
      AND s.status LIKE 'inactive'";

    $stmt = $db->prepare($sql_nb_actif_sensor);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $nb_actif_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $nb_actif_sensor[0];
    }
  }



  /**
   * Get the number of inactif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   
  public function getNumberInactifSensorFromDB($group_name)
  {
    $db = static::getDB();

    $sql_nb_actif_sensor = "SELECT 
      count(*) 
    FROM 
      (
    SELECT 
      DISTINCT s.device_number, 
      MAX(
        DATE(r.date_time)
      ) as dateMaxReceived 
    FROM 
      sensor AS s 
      INNER JOIN record AS r ON (s.id = r.sensor_id) 
      INNER JOIN sensor_group AS gs ON (gs.sensor_id = s.id) 
      INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
    WHERE 
      gn.name = :group_name
    GROUP BY 
      s.device_number
    ) AS LAST_MSG_RECEIVED 
    WHERE 
      dateMaxReceived < CURDATE() - 5";

    $stmt = $db->prepare($sql_nb_actif_sensor);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $nb_actif_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $nb_actif_sensor[0];
    }
  }*/

  /**
   * Get the number of actif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   
  public function getNumberActifSensorFromDB($group_name)
  {
    $db = static::getDB();

    $sql_nb_actif_sensor = "SELECT 
      count(*) 
    FROM 
      (
    SELECT 
      DISTINCT s.device_number, 
      MAX(
        DATE(r.date_time)
      ) as dateMaxReceived 
    FROM 
      sensor AS s 
      INNER JOIN record AS r ON (s.id = r.sensor_id) 
      INNER JOIN sensor_group AS gs ON (gs.sensor_id = s.id) 
      INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
    WHERE 
      gn.name = :group_name 
    GROUP BY 
      s.device_number
    ) AS LAST_MSG_RECEIVED 
    WHERE 
      dateMaxReceived >= CURDATE() -1";

    $stmt = $db->prepare($sql_nb_actif_sensor);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $nb_actif_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $nb_actif_sensor[0];
    }
  }*/
}
