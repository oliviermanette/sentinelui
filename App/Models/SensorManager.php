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
   * Get the number of actif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   */
  public function getNumberActifSensor($group_name)
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
  }

  /**
   * Get the number of inactif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   */
  public function getNumberInactifSensor($group_name)
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
  }

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
   *
   * @return void
   */
  public function getListOfDevicesFromAPI()
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
}
