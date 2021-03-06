<?php

/*
RecordManager.php
Handle the record data CRUD on the database
author : Lirone Samoun

Briefly : Handle record data. By record, we mean all the data from the record table of the DB.
Basically inclinometer data, choc, spectre, battery...

*/

namespace App\Models;

use App\Config;
use App\Models\Messages\Message;
use App\Utilities;
use App\Controllers\ControllerDataObjenious;
use App\Models\API\TemperatureAPI;
use App\Models\Messages\Choc;
use App\Models\Messages\Inclinometer;
use App\Models\Messages\Battery;
use App\Models\Messages\Spectre;
use App\Models\Messages\Alert;
use App\Models\Settings\SettingSensorManager;
use App\Models\SentiveAIManager;
use \App\Models\API\SentiveAPI;
use PDO;


ini_set('error_reporting', E_ALL);
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "./log/error.log");

class RecordManager extends \Core\Model
{


  /**
   * Parse json data and then insert into the DB
   *
   * @param json $jsondata json data received from Objenious. This file contain the uplink message
   * @return boolean  True if data has been correctly inserted, true otherwise
   */
  public static function parseJsonDataAndInsert($data)
  {

    //Take the raw data and decode it
    $message = new Message($data);

    //Check if the sensors is associated to an installation before dealing with a message
    if (SensorManager::isInstalled($message->deveui)) {
      if ($message->getFormatMessage() == "uplink") {
        RecordManager::handleUplinkMessage($message);
      } else if ($message->getFormatMessage() == "event") {

        RecordManager::handleEventMessage($message);
      } else if ($message->getFormatMessage() == "downlink") {
      } else if ($message->getFormatMessage() == "join") {
      }
    } else {
      echo "\nThe sensor has not been yet installed. \n";
    }
  }

  /**
   * handle uplink message received by the sensor

   * @param message $message object that contain all the info received by the uplink
   * @return void
   */
  private static function handleUplinkMessage($message)
  {

    EquipementManager::insertStructureCategory($message->typeStructure);
    //Add message received to the database
    $success = RecordManager::insertRecordData($message);

    if ($success) {
      if ($message->typeMsg == "choc") {
        $choc = new Choc($message->msgDecoded);
        if (!ChocManager::insertChoc($choc)) {
          return false;
        }

        $chocManager = new ChocManager();
        //Check if the sensor is installed and the alert is activated 
        if (SensorManager::isInstalled($choc->deveui) && SettingSensorManager::isSettingActivatedForSensor($choc->deveui, 'shock_thresh')) {
          $group_id = SensorManager::getGroupOwnerCurrentUser($choc->deveui);

          $hasAlert = $chocManager->check($choc, $group_id, $method = "VALUE");
          //Create new alert if it's the case
          if ($hasAlert) {
            $label = "high_choc";
            $type = "shock";

            $alert = new Alert($type, $label, $choc->deveui, $choc->dateTime, $choc->getPowerValueChoc($precision = 3, $unite = "g"));

            AlertManager::insertTypeEvent($label,  $alert->criticality);
            AlertManager::insert($alert);
            //Send alert
            AlertManager::sendAlert($alert, $group_id);
          }
        }
      }
      //battery data
      else if ($message->typeMsg == "global") {
        $battery = new Battery($message->msgDecoded);

        if (!BatteryManager::insertBattery($battery)) {
          return false;
        }
      }
      //Inclinometer data
      else if ($message->typeMsg == "inclinometre") {
        $inclinometer = new Inclinometer($message->msgDecoded);

        if (isset($inclinometer->battery_left)) {
          if (!InclinometerManager::insertBattery($inclinometer)) {
            return false;
          }
        }
        if (!InclinometerManager::insertInclinometer($inclinometer)) {
          return false;
        }

        //Insert current temperature of the site today
        $dataArr = TemperatureAPI::getCurrentDataWeather($message->latitude, $message->longitude, $API_NAME = "DARKSKY");
        TemperatureManager::insertDataWeather($dataArr, $message->site, $message->dateTime, $API_NAME = "DARKSKY");
        //$currentTemperature = TemperatureAPI::getCurrentTemperature($message->latitude, $message->longitude);
        //TemperatureManager::insert($currentTemperature, $message->site, $message->dateTime);

        //Check only if it's installed on the structure
        if (SensorManager::isInstalled($inclinometer->deveui)) {

          $inclinometreManager = new InclinometerManager();
          $group_id = SensorManager::getGroupOwnerCurrentUser($inclinometer->deveui);
          $isAlert = False;
          $hasAlertArr = $inclinometreManager->check($inclinometer, $group_id);

          if (
            Utilities::is_key_in_array($hasAlertArr, "alertThirdThreshAxisY") &&
            SettingSensorManager::isSettingActivatedForSensor($inclinometer->deveui, 'third_inclinationY_thresh')
          ) {

            $label = "third_thresh_axisY_inclinometer_raised";
            $criticality = "HIGH";
            $thresh = $hasAlertArr["alertThirdThreshAxisY"]["thresh"];
            $type = $hasAlertArr["type"];
            $values = $hasAlertArr["alertThirdThreshAxisY"];
            $isAlert = True;
          } else if (
            Utilities::is_key_in_array($hasAlertArr, "alertSecondThreshAxisY") &&
            SettingSensorManager::isSettingActivatedForSensor($inclinometer->deveui, 'second_inclinationY_thresh')
          ) {

            $label = "second_thresh_axisY_inclinometer_raised";
            $criticality = "HIGH";
            $thresh = $hasAlertArr["alertSecondThreshAxisY"]["thresh"];
            $type = $hasAlertArr["type"];
            $values = $hasAlertArr["alertSecondThreshAxisY"];
            $isAlert = True;
          } else if (
            Utilities::is_key_in_array($hasAlertArr, "alertFirstThreshAxisY") &&
            SettingSensorManager::isSettingActivatedForSensor($inclinometer->deveui, 'first_inclinationY_thresh')
          ) {

            $label = "first_thresh_axisY_inclinometer_raised";
            $criticality = "HIGH";
            $thresh = $hasAlertArr["alertFirstThreshAxisY"]["thresh"];
            $type = $hasAlertArr["type"];
            $values =  $hasAlertArr["alertFirstThreshAxisY"];
            $isAlert = True;
          } //Axis X
          else if (
            Utilities::is_key_in_array($hasAlertArr, "alertFirstThreshAxisX") &&
            SettingSensorManager::isSettingActivatedForSensor($inclinometer->deveui, 'first_inclinationX_thresh')
          ) {

            $label = "first_thresh_axisX_inclinometer_raised";
            $criticality = "HIGH";
            $thresh = $hasAlertArr["alertFirstThreshAxisX"]["thresh"];
            $type = $hasAlertArr["type"];
            $values = $hasAlertArr["alertFirstThreshAxisX"];
            $isAlert = True;
          }


          if ($isAlert == True) {
            $alert = new Alert($type, $label, $inclinometer->deveui, $inclinometer->dateTime, $values);
            AlertManager::insertTypeEvent($label, $criticality);
            AlertManager::insert($alert);
            AlertManager::sendAlert($alert, $group_id);
          }
        }
      }
      //Subspectre data
      else if ($message->typeMsg == "spectre") {
        $spectre = new Spectre($message->msgDecoded);
        if (!SpectreManager::insertSpectre($spectre)) {
          return false;
        }

        //Update Sentive AI
        $device_number = $message->device_number;
        //Create timeserie from spectre
        if (SentiveAPI::isConnected()) {
          echo "\n SENTIVE CONNECTED \n";
          //Check if timeseries empty to see if we need to reset the network

          //

          $timeSerie = new TimeSeries();
          $timeSerie->createFromMsg($message);
          $timeSerie->setNetworkId($device_number);
          $dataPayloadJson = $timeSerie->parseForSentiveAi();
          //SentiveAIManager::addDataToNetwork($device_number, $dataPayloadJson, $name = "DbTimeSeries");
          $networkId = $device_number;
          //SentiveAIManager::runUnsupervisedOnNetwork($networkId);
          //SentiveAIManager::computeImagesOnNetwork($networkId);
        } else {
          echo "\n SENTIVE NOT CONNECTED \n";
        }
      }

      return true;
    }
  }

  /**
   * handle event message received by the sensor

   * @param message $message object that contain all the info received by the uplink
   * @return void
   */
  private static function handleEventMessage($message)
  {
    $group_id = SensorManager::getGroupOwnerCurrentUser($message->deveui);
    $label = $message->type;
    $type = "event";
    $group_id = SensorManager::getGroupOwnerCurrentUser($message->deveui);
    $alert = new Alert($type, $label, $message->deveui, $message->dateTime);
    AlertManager::insertTypeEvent($label, $alert->criticality, $alert->msg);
    AlertManager::insert($alert, $message->status);
    AlertManager::sendAlert($alert, $group_id);
  }


  /**
   * Insert new record message into record table.
   *
   * @param string $deveui_sensor deveuil of the sensor
   * @param string $name_asset asset name (structure)
   * @param string $payload_cleartext payload message
   * @param string $date_time date time of the message
   * @param string $type_msg type of the message
   * @param string $longitude approximative longitude of the localisation of the message
   * @param string $latitude approximative Latitude of the localisation of the message
   *
   * @return boolean  True if the data has been correctly inserted, False otherwise
   */
  public static function insertRecordData($message)
  {

    $data_record = "INSERT INTO record
                  (`sensor_id`,
                  `structure_id`,
                  `id_message_platform`,
                  `payload`,
                  `count`,
                  `date_time`,
                  `msg_type`,
                  `longitude`,
                  `latitude`)
          SELECT * FROM   (
          SELECT (  SELECT id
                  FROM   sensor
                  WHERE  deveui = :deveui_sensor),
                  (SELECT structure.id
                      FROM   structure
                            LEFT JOIN attr_transmission_line
                                    ON structure.attr_transmission_id = attr_transmission_line.id
                      WHERE  structure.nom = :name_asset
                            AND attr_transmission_line.name LIKE :transmission_line_name),
                    :message_id,
                    :payload_raw,
                    :count_msg,
                    :date_time,
                    :type_msg,
                    :longitude,
                    :latitude) AS id_record
      WHERE  NOT EXISTS (SELECT date_time
                        FROM   record
                        WHERE  date_time = :date_time)
      LIMIT  1 ";

    $db = static::getDB();
    $stmt = $db->prepare($data_record);

    $stmt->bindValue(':deveui_sensor', $message->deveui, PDO::PARAM_STR);
    $stmt->bindValue(':name_asset', $message->structureName, PDO::PARAM_STR);
    $stmt->bindValue(':transmission_line_name', $message->transmissionLineName, PDO::PARAM_STR);
    $stmt->bindValue(':message_id', $message->id, PDO::PARAM_STR);
    $stmt->bindValue(':payload_raw', $message->payload_cleartext, PDO::PARAM_STR);


    $stmt->bindValue(':date_time', $message->dateTime, PDO::PARAM_STR);
    $stmt->bindValue(':type_msg', $message->typeMsg, PDO::PARAM_STR);
    $stmt->bindValue(':longitude', $message->longitude, PDO::PARAM_STR);
    $stmt->bindValue(':latitude', $message->latitude, PDO::PARAM_STR);
    $count = null;
    if (property_exists($message, 'count')) {
      $count = $message->count;
    }
    $stmt->bindValue(':count_msg', $count, PDO::PARAM_STR);

    $stmt->execute();
    $count = $stmt->rowCount();
    if ($count == '0') {
      echo "\n[record] No new record inserted\n";
      return false;
    } else {
      echo "\n[record] Success: new record insert.\n";
      return true;
    }
  }



  /**
   * Get the min and max date from table record
   *
   * @return array results of the query
   *
   */
  public static function getDateMinMaxFromRecord()
  {
    $db = static::getDB();
    $query_min_max_date = "SELECT (SELECT DATE_FORMAT(MAX(Date(date_time)), '%d/%m/%Y') FROM record) AS fistActivity,
    (SELECT DATE_FORMAT(MIN(Date(date_time)), '%d/%m/%Y') FROM record) AS lastActivity";

    $stmt = $db->prepare($query_min_max_date);
    $data = array();
    if ($stmt->execute()) {

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $min_date_time = $row["fistActivity"];
      $max_date_time = $row["lastActivity"];
      $min_date = date('d-m-Y', strtotime($min_date_time));
      $max_date = date('d-m-Y', strtotime($max_date_time));

      $date_min_max = array($min_date, $max_date);

      return $date_min_max;
    }
  }

  /**
   * Get a summarize of the records table
   *
   * @param string $group_name group
   * @return array results of the query
   *  sensor_id | site | ligneHT | equipement | nb_messages | nb_chocs | last_message_received | status
   *
   */
  public static function getBriefInfoFromRecord($groupId)
  {

    $db = static::getDB();

    $query_get_number_record = "
    SELECT
    sensor_id,
    deveui,
    site,
    ligneHT,
    equipement,
    nb_messages,
    nb_choc,
    DATE_FORMAT(
      last_message_received, '%d/%m/%Y %H:%i:%s'
    ) AS `last_message_received` ,
    DATE_FORMAT(date_installation, '%d/%m/%Y') AS 'date_installation',
    status
   FROM
    (
      SELECT
        sensor.device_number AS 'sensor_id',
        sensor.deveui AS 'deveui',
        sensor.installation_date AS 'date_installation',
        s.nom AS `site`,
        sensor.status AS status,
        attr_transmission_line.name AS `LigneHT`,
        st.nom AS `equipement`,
        sum(
          case when msg_type = 'choc' then 1 else 0 end
        ) AS 'nb_choc',
        count(*) AS 'nb_messages',
        Max(
          r.date_time
        ) AS `last_message_received`
      FROM
        sensor
        LEFT JOIN record AS r ON (r.sensor_id = sensor.id)
        LEFT JOIN structure AS st ON st.id = sensor.structure_id
        LEFT JOIN attr_transmission_line ON attr_transmission_line.id=st.attr_transmission_id
        LEFT JOIN site AS s ON s.id = st.site_id
        LEFT JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
        LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
      WHERE
        gn.group_id = :groupId
      GROUP BY
        sensor.deveui,
        sensor.device_number,
        sensor.installation_date,
        sensor.status,
        r.sensor_id,
        st.nom,
        s.nom,
        attr_transmission_line.name
    ) AS all_message_rte_sensor";
    //  AND Date(r.date_time) >= Date(sensor.installation_date)
    $stmt = $db->prepare($query_get_number_record);
    $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
      //Get variation inclinometer
      $newDataArr = array();
      foreach ($res as $data) {
        $deveui = $data["deveui"];

        $variationArr = InclinometerManager::computePercentageVariationAngleValueForLast($deveui, -1, 3);
        $variationX = $variationArr["pourcentage_variation_angleX"];
        $variationY = $variationArr["pourcentage_variation_angleY"];
        $variationZ = $variationArr["pourcentage_variation_angleZ"];
        //echo $variationX . "</br>\n";
        if (empty($variationX)) {
          $variationX = 0;
        }
        if (empty($variationY)) {
          $variationY = 0;
        }
        if (empty($variationZ)) {
          $variationZ = 0;
        }
        $data["variationX"] = $variationX;
        $data["variationY"] = $variationY;
        $data["variationZ"] = $variationZ;
        array_push($newDataArr, $data);
      }

      $tmpArr = array();
      $obj = new \stdClass();
      $obj->data = $newDataArr;
      return $obj;
    }
  }

  public static function getBriefInfoFromAllRecords()
  {

    $db = static::getDB();

    $query_get_number_record = "
    SELECT
    sensor_id,
    deveui,
    site,
    ligneHT,
    equipement,
    nb_messages,
    nb_choc,
    DATE_FORMAT(
      last_message_received, '%d/%m/%Y %H:%i:%s'
    ) AS `last_message_received` ,
    DATE_FORMAT(date_installation, '%d/%m/%Y') AS 'date_installation',
    status
   FROM
    (
      SELECT
        sensor.device_number AS 'sensor_id',
        sensor.deveui AS 'deveui',
        sensor.installation_date AS 'date_installation',
        s.nom AS `site`,
        sensor.status AS status,
        attr_transmission_line.name AS `LigneHT`,
        st.nom AS `equipement`,
        sum(
          case when msg_type = 'choc' then 1 else 0 end
        ) AS 'nb_choc',
        count(*) AS 'nb_messages',
        Max(
          r.date_time
        ) AS `last_message_received`
      FROM
        sensor
        LEFT JOIN record AS r ON (r.sensor_id = sensor.id)
        LEFT JOIN structure AS st ON st.id = sensor.structure_id
        LEFT JOIN attr_transmission_line ON attr_transmission_line.id=st.attr_transmission_id
        LEFT JOIN site AS s ON s.id = st.site_id
        LEFT JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
        LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
      GROUP BY
        sensor.deveui,
        sensor.device_number,
        sensor.installation_date,
        sensor.status,
        r.sensor_id,
        st.nom,
        s.nom,
        attr_transmission_line.name
    ) AS all_messages";
    //  AND Date(r.date_time) >= Date(sensor.installation_date)
    $stmt = $db->prepare($query_get_number_record);

    if ($stmt->execute()) {
      $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
      //Get variation inclinometer
      $newDataArr = array();
      foreach ($res as $data) {
        $deveui = $data["deveui"];

        $variationArr = InclinometerManager::computePercentageVariationAngleValueForLast($deveui, -1, 3);
        $variationX = $variationArr["pourcentage_variation_angleX"];
        $variationY = $variationArr["pourcentage_variation_angleY"];
        $variationZ = $variationArr["pourcentage_variation_angleZ"];
        //echo $variationX . "</br>\n";
        if (empty($variationX)) {
          $variationX = 0;
        }
        if (empty($variationY)) {
          $variationY = 0;
        }
        if (empty($variationZ)) {
          $variationZ = 0;
        }
        $data["variationX"] = $variationX;
        $data["variationY"] = $variationY;
        $data["variationZ"] = $variationZ;
        array_push($newDataArr, $data);
      }

      $tmpArr = array();
      $obj = new \stdClass();
      $obj->data = $newDataArr;
      return $obj;
    }
  }



  /**
   * Get the data for displaying the map
   *
   * @param string $group_name group
   * @return array results of the query
   *  sensor_id | latitude_site | longitude_site | latitude_sensor | longitude_sensor | site | equipement
   *
   */
  public static function getDataMapForGroup($group_id)
  {
    $db = static::getDB();

    $query_data_map = "SELECT DISTINCT sensor.device_number, sensor.deveui, s.latitude AS latitude_site, s.longitude AS longitude_site,
    st.latitude AS latitude_sensor, st.longitude AS longitude_sensor, attr_transmission_line.name AS transmission_line_name, s.nom AS site, st.nom AS equipement
    FROM sensor
    LEFT JOIN structure AS st ON sensor.structure_id = st.id
    LEFT JOIN attr_transmission_line ON attr_transmission_line.id = st.attr_transmission_id
    LEFT JOIN site AS s ON s.id = st.site_id
    LEFT JOIN sensor_group AS gs ON (gs.sensor_id=sensor.id)
    LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE gn.group_id = :group_id";

    $stmt = $db->prepare($query_data_map);
    $stmt->bindValue(':group_id', $group_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
      $data_map = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $data_map;
    }
  }

  public static function getAllDataMap()
  {
    $db = static::getDB();

    $query_data_map = "SELECT DISTINCT sensor.device_number, sensor.deveui, s.latitude AS latitude_site, s.longitude AS longitude_site,
    st.latitude AS latitude_sensor, st.longitude AS longitude_sensor, attr_transmission_line.name AS transmission_line_name, s.nom AS site, st.nom AS equipement
    FROM sensor
    LEFT JOIN structure AS st ON sensor.structure_id = st.id
    LEFT JOIN attr_transmission_line ON attr_transmission_line.id = st.attr_transmission_id
    LEFT JOIN site AS s ON s.id = st.site_id";

    $stmt = $db->prepare($query_data_map);
    if ($stmt->execute()) {
      $data_map = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $data_map;
    }
  }

  public static function getAllRawRecord()
  {
    $db = static::getDB();

    $sql_all_msg_raw_data = "SELECT sensor.id,
       sensor.deveui,
       s.nom      AS Site,
       st.nom     AS Equipement,
       st.transmision_line_name AS LigneHT,
       r.date_time,
       r.payload,
       r.msg_type AS 'Type message',
       amplitude_1,
       amplitude_2,
       time_1,
       time_2,
       freq_1,
       freq_2,
       power,
       inc.nx,
       inc.ny,
       inc.nz,
       angle_x,
       angle_y,
       angle_z,
       temperature,
       subspectre,
       subspectre_number,
       min_freq,
       max_freq,
       resolution,
       battery_level
       FROM   record AS r
       LEFT JOIN spectre AS sp
              ON ( r.id = sp.record_id )
       LEFT JOIN global AS gl
              ON ( gl.record_id = r.id )
       LEFT JOIN choc
              ON ( choc.record_id = r.id )
       LEFT JOIN inclinometer AS inc
              ON ( inc.record_id = r.id )
       INNER JOIN structure AS st
               ON st.id = r.structure_id
       INNER JOIN site AS s
               ON s.id = st.site_id
       INNER JOIN sensor
               ON ( sensor.id = r.sensor_id )
       INNER JOIN sensor_group AS gs
               ON ( gs.sensor_id = sensor.id )
       INNER JOIN group_name AS gn
               ON ( gn.group_id = gs.groupe_id )
       WHERE  gn.NAME = :group_name
       AND Date(r.date_time) >= Date(sensor.installation_date)";


    $stmt = $db->prepare($sql_all_msg_raw_data);
    $stmt->bindValue(':group_name', $_SESSION['group_name'], PDO::PARAM_STR);
    if ($stmt->execute()) {
      $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $raw_data;
    }
  }


  public static function getAllSpecificMsgFromSensor($deveui, $dateMin, $dateMax)
  {

    $db = static::getDB();

    $sql = "SELECT sensor.device_number, st.id AS 'structure_id',
    DATE_FORMAT(r.date_time, '%d/%m/%Y %H:%i:%s') AS `date_time`,
    r.msg_type AS `typeMessage`, s.nom AS `site`, st.nom AS `equipement`
    FROM record as r
    LEFT JOIN sensor on sensor.id=r.sensor_id
    LEFT JOIN structure AS st on st.id=r.structure_id
    LEFT JOIN site AS s ON s.id = st.site_id
    WHERE ";

    if (!empty($dateMin) && !empty($dateMax)) {
      $sql .= "date(r.date_time) BETWEEN date(:date_min) and date(:date_max) AND ";
    }

    $sql .= "Date(r.date_time) >= Date(sensor.installation_date)
      AND sensor.deveui = :deveui
      ORDER BY r.date_time DESC ";

    $stmt = $db->prepare($sql);

    if (!empty($dateMin) && !empty($dateMax)) {
      $stmt->bindValue(':date_min', $dateMin, PDO::PARAM_STR);
      $stmt->bindValue(':date_max', $dateMax, PDO::PARAM_STR);
    }
    //$stmt->bindValue(':type_msg', $typeMSG, PDO::PARAM_INT);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }


  /**
   * Init Pool DB
   * A pool is automatically created for each different pair (structure_Id, sensor_Id)
   * found in the record table.
   *
   * @param string $group_name group to deal with (RTE)
   * @return void
   */
  function initPool($groupId)
  {

    $resultsArr = RecordManager::getCoupleStructureIDSensorID($groupId);
    echo "Add to POOL database : \n";
    foreach ($resultsArr as $coupleArr) {
      $structure_id = $coupleArr["structure_id"];
      $sensor_id = $coupleArr["sensor_id"];
      //Add to the DB
      if (RecordManager::insertPoolData($structure_id, $sensor_id)) {
        echo "(Structure_id : " . $structure_id . ", Sensor_id : " . $sensor_id . ") \n";
      }
    }
    echo "\n DONE \n";
  }

  /**
   *
   * @param string $groupId group to deal with 
   * @return void
   */
  public static function getCoupleStructureIDSensorID($groupId)
  {
    $db = static::getDB();

    $sql = "
    SELECT DISTINCT r.structure_id, r.sensor_id FROM record AS r
    LEFT JOIN structure AS st ON (st.id=r.structure_id)
    LEFT JOIN site AS s ON (s.id = st.site_id)
    LEFT JOIN sensor ON (sensor.id=r.sensor_id)
    LEFT JOIN sensor_group AS gs ON (gs.sensor_id=sensor.id)
    LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE gn.group_id = :groupId
    ";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Get the pool id from a structure and a sensor
   *
   * @param int $structure_id id of the structure
   * @param int $sensor_id id of the sensor
   * @return int results of the query
   *  id of the pool
   *
   */
  public function getPoolId($structure_id, $sensor_id)
  {
    $db = static::getDB();

    $sql_pool_id = "
    SELECT DISTINCT id FROM pool
    WHERE structure_id = :structure_id
    AND sensor_id = :sensor_id
    ";

    $stmt = $db->prepare($sql_pool_id);

    $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $poolId = $stmt->fetch(PDO::FETCH_COLUMN);
      return $poolId;
    }
  }

  /**
   * Get all the sensor ID from all the pool
   *
   * @return array results of the query
   *  sensor_id
   *
   */
  public function getAllSensorIdFromPool()
  {
    $db = static::getDB();

    $sql = "SELECT sensor_id FROM pool";

    $stmt = $db->prepare($sql);

    if ($stmt->execute()) {
      $sensorIdArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $sensorIdArr;
    }
  }

  /**
   * Insert Pool Data to Database
   * @param int $structure_id
   * @param int $sensor_id
   * @return void
   */
  public static function insertPoolData($structure_id, $sensor_id)
  {

    $db = static::getDB();

    $sql = "INSERT INTO pool(structure_id, sensor_id)
    SELECT :structure_id, :sensor_id
    WHERE NOT EXISTS (SELECT * FROM pool
          WHERE structure_id=:structure_id AND sensor_id=:sensor_id LIMIT 1)";

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);


    $ok = $stmt->execute();

    $db = null;
    if ($ok) {
      return true;
    }
    return false;
  }



  /**
   * Get the datatype ID in the DB from the name
   *
   * @param string $name name of the datatype
   * @return int id
   *
   */
  public function getDataTypeIdFromName($name)
  {

    $db = static::getDB();

    $sql = "SELECT id FROM dataType WHERE nom = :name";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $id = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $id[0];
    }
  }
}
