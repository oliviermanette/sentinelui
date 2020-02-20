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

  public static function getOwner($deveui){
    $db = static::getDB();

    $sql = "SELECT group_name.name FROM group_name
    LEFT JOIN sensor_group ON (sensor_group.groupe_id = group_name.group_id)
    LEFT JOIN sensor ON (sensor.id = sensor_group.sensor_id)
    WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $owner = $stmt->fetch(PDO::FETCH_COLUMN);
      return $owner;
    }
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
  public static function getDeveuiFromSensorLabel($device_number)
  {
    $db = static::getDB();

    $sql_deveui_sensor = "SELECT deveui FROM `sensor` 
      WHERE device_number = :device_number ";

    $stmt = $db->prepare($sql_deveui_sensor);
    $stmt->bindValue(':device_number', $device_number, PDO::PARAM_STR);

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
  public static function getDeviceNumberFromSensorId($sensor_id)
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
  public static function getSensorIdFromDeveui($deveui)
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

  public static function getSensorIdUsingSiteAndEquipementID($site_id, $equipement_id){
    $db = static::getDB();

    $sql_query_id =  "SELECT DISTINCT(`sensor_id`) FROM `record` AS r
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE s.id = :site_id AND st.id = :equipement_id ";

    $stmt = $db->prepare($sql_query_id);

    $stmt->bindValue(':site_id', $site_id, PDO::PARAM_INT);
    $stmt->bindValue(':equipement_id', $equipement_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $sensor_id = $stmt->fetch(PDO::FETCH_COLUMN);
      return $sensor_id;
    }

    $db = null;

    return $sensor_id;
  }

  public static function getSensorLabelFromDeveui($deveui)
  {
    $db = static::getDB();

    $sql_id_sensor = "SELECT device_number FROM `sensor` 
      WHERE deveui = :deveui ";

    $stmt = $db->prepare($sql_id_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $device_label= $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $device_label[0];
    }
  }

  /** 
   * Get last message received from a specific sensor
   *
   * @param string $deveui
   * @return string last date
   * 
   */
  public static function getLastMessageReceivedFromDeveui($deveui){
    $db = static::getDB();
    
    $sql_last_date_received = "SELECT DATE_FORMAT(MAX(DATE(r.date_time)), '%d/%m/%Y') as lastDateReceived FROM sensor AS s 
    INNER JOIN record AS r ON (s.id = r.sensor_id)
    AND s.deveui LIKE :deveui
    GROUP BY s.device_number
    ";

    $stmt = $db->prepare($sql_last_date_received);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $last_date = $stmt->fetchAll(PDO::FETCH_COLUMN);
      if (!(empty($last_date))){
        return $last_date[0];
      }
      return 0;
    }
  }

  /** 
   * Get the battery state of sensor
   *
   * @param string $deveui
   * @return int battery value left
   * date_time | battery_left
   * 
   */
  public static function getLastBatteryStateFromDeveui($deveui)
  {
    $db = static::getDB();

    $sql_battery_sensor = "SELECT r.date_time, g.battery_level  
    FROM record AS r
    LEFT JOIN global AS g ON (g.record_id = r.id)
    LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
    WHERE s.deveui LIKE :deveui  
    AND r.msg_type LIKE 'global'  
    ORDER BY `r`.`date_time` DESC LIMIT 1";

    $stmt = $db->prepare($sql_battery_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $batteryInfoArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $batteryInfoArr;
    }
  }

  public static function getDateMinMaxActivity($deveui)
  {
    $db = static::getDB();
    $query_min_max_date = "SELECT DATE_FORMAT(MIN(Date(r.date_time)), '%m/%d/%Y') AS first_activity,
      DATE_FORMAT(MAX(Date(r.date_time)), '%m/%d/%Y') AS last_activity  From record AS r 
      LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
      WHERE s.deveui = :deveui";

    $stmt = $db->prepare($query_min_max_date);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    $data = array();
    if ($stmt->execute()) {

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $min_date_time = $row["first_activity"];
      $max_date_time = $row["last_activity"];

      $date_min_max = array($min_date_time, $max_date_time);

      return $date_min_max;
    }
  }

  /** 
   * Get all records of a device
   *
   * @param string $deveui
   * @return array records from the device array
  
   * 
   */
  public static function getRecordsFromDeveui($deveui)
  {
    $db = static::getDB();

    $sql_record_sensor = "SELECT 
        sensor.device_number AS 'sensor_label', 
        DATE_FORMAT(
          r.date_time,'%d/%m/%Y %H:%i:%S'
        ) AS `date_mesure`,
        r.msg_type AS 'type',
        g.battery_level AS 'battery',
        inc.temperature AS 'temperature',
        ROUND(inc.angle_x,3) AS 'inclinaison_x',
        ROUND(inc.angle_y,3) AS 'inclinaison_y',
        ROUND(inc.angle_z,3) AS 'inclinaison_z',
        c.amplitude_1 AS 'amplitude_1',
        c.freq_1 AS 'freq_1',
        c.amplitude_2 AS 'amplitude_2',
        c.freq_2 AS 'freq_2',
        ROUND(c.power,4) AS 'power'
        FROM 
          record AS r 
          LEFT JOIN inclinometer AS inc ON (inc.record_id = r.id)
          LEFT JOIN choc AS c ON (c.record_id = r.id)
          LEFT JOIN global AS g ON (g.record_id = r.id)
          LEFT JOIN sensor ON (sensor.id = r.sensor_id) 
        WHERE 
          sensor.deveui LIKE :deveui
          AND Date(r.date_time) >= Date(sensor.installation_date) 
          ORDER BY r.date_time DESC";

    $stmt = $db->prepare($sql_record_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $recordArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $recordArr;
    }
  }

  /** 
   * Get number total of message received
   *
   * @param string $deveui
   * @return int nbre total message received by the sensor
  
   * 
   */
  public static function getNbTotalMessagesFromDeveui($deveui)
  {
    $db = static::getDB();

    $sql_battery_sensor = "SELECT count(*) AS nbreTotMessages
    FROM record AS r
    LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
    WHERE s.deveui LIKE :deveui  
    ORDER BY `r`.`date_time`  DESC";

    $stmt = $db->prepare($sql_battery_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $nbreTotMsg = $stmt->fetch(PDO::FETCH_COLUMN);
      return $nbreTotMsg;
    }
  }

  /** 
   * Get basic info of sensors given his deveui
   *
   * @param string $deveui the deveui of the sensor
   * @return array results of the query
   * id_device | id_objenious | device_number | firmware | Hardware |
   * constructor | deveui | groupe | ligneHT | equipement | status |date_installation
   * 
   */
  public static function getBriefInfoForSensor($deveui)
  {
    $db = static::getDB();

    $sql_brief_info = "SELECT 
      DISTINCT sensor.id AS 'id_device', 
      sensor.id_device AS 'id_objenious', 
      sensor.device_number AS 'device_number', 
      sensor.firmware_version AS 'firmware', 
      sensor.hardware_version AS 'hardware',
      sensor.constructeur AS 'constructor', 
      sensor.deveui AS deveui, 
      s.nom AS site, 
      s.latitude AS latitude_site, 
      s.longitude AS longitude_site,
      st.latitude AS latitude_sensor, 
      st.longitude AS longitude_sensor,
      gn.name AS groupe, 
      st.transmision_line_name AS `LigneHT`, 
      st.nom AS `equipement`, 
      sensor.status AS status, 
      DATE_FORMAT(
        sensor.installation_date, '%d/%m/%Y'
      ) AS date_installation 
    FROM 
      sensor 
      LEFT JOIN record AS r ON (sensor.id = r.sensor_id) 
      LEFT JOIN structure AS st ON st.id = r.structure_id 
      LEFT JOIN site AS s ON s.id = st.site_id 
      LEFT JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id) 
      LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
    WHERE 
      sensor.deveui LIKE :deveui
    ";

    $stmt = $db->prepare($sql_brief_info);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $resultsArr = $stmt->fetch(PDO::FETCH_ASSOC);
      return $resultsArr;
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
    groupe,
    device_number, 
    ligneHT, 
    equipement,
    site,
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
        s.nom AS 'site',
        Max(
          Date(r.date_time)
        ) AS `last_message_received` 
      FROM 
        record AS r 
        LEFT JOIN structure AS st ON st.id = r.structure_id 
        LEFT JOIN site AS s ON s.id = st.site_id 
        LEFT JOIN sensor ON (sensor.id = r.sensor_id) 
        LEFT JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id) 
        LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
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
      $label = $deviceArr["label"];
      if (strcmp($label, $labelAsked) == 0) {
        $deviceIDObjenious = $deviceArr["id"];
        return $deviceIDObjenious;
      }
    }
  }

  /** 
   * Get Deveui on Objenious given a label (name of the sensor)
   *
   * @param string $labelAsked the name of the sensor
   * @return int deveui of the device
   * 
   */
  public static function getDeveuiFromLabel($labelAsked)
  {
    $listDeviceArr = SensorManager::getListOfDevicesFromAPI();
    foreach ($listDeviceArr as $deviceArr) {
      $label = $deviceArr["label"];
      if (strcmp($label, $labelAsked) == 0) {
        $propertiesArr = $deviceArr["properties"];
        $deveui = $propertiesArr["deveui"];
        return $deveui;
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


  public static function getStatusDevice($sensor_id){
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
  public static function getNumberActiveSensorFromDB($group_name)
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
  public static function getNumberInactiveSensorFromDB($group_name)
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
