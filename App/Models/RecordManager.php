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
use App\Utilities;
use App\Controllers\ControllerDataObjenious;
use PDO;


class RecordManager extends \Core\Model
{


  /**
   * Parse json data and then insert into the DB
   *
   * @param json $jsondata json data received from Objenious. This file contain the uplink message
   * @return boolean  True if data has been correctly inserted, true otherwise
   */
  function parseJsonDataAndInsert($data)
  {
    $sensorManager = new SensorManager();
    $equipementManager = new EquipementManager();
    $geocoder = new \OpenCage\Geocoder\Geocoder(\App\Config::GEOCODER_API_KEY);

    $type_msg =  $data['type'];

    //Check what kind of message we received from the sensor
    $type_msg = RecordManager::checkTypeMessage($type_msg);

    if ($type_msg == "uplink") {
      //Classic message which has the raw data value (inclinometer...)
      $uplinkDataArr = RecordManager::extractUplinkData($data);
      //Add equipement
      $equipementManager->insertStructureType($uplinkDataArr["type_asset"]);
      if (!$equipementManager->insertStructureType($uplinkDataArr["type_asset"])) {
        return false;
      }

      //As we received a payload message, we need to decode it
      $payload_msg_json = RecordManager::decodePayload($uplinkDataArr["payload_cleartext"]);
      $payload_decoded_json = json_decode($payload_msg_json, true);

      #Add date time attribute to the decoded payload
      $payload_decoded_json['date_time'] = $uplinkDataArr["date_time"];
      $payload_decoded_json['deveui'] = $uplinkDataArr["deveui"];

      $sensor_id = $sensorManager->getSensorIdFromDeveui($uplinkDataArr["deveui"]);
      $equipement_id = $equipementManager->getEquipementIdBySensorId($sensor_id);

      //Get the type of message received from the uplink (choc, inclinometer, global, spectre)
      $type_msg = $payload_decoded_json["type"];
      //print_r($payload_decoded_json);
      //Insert a record inside the Record table of the DB
      $success = RecordManager::insertRecordData($uplinkDataArr["deveui"], $uplinkDataArr["name_asset"], $uplinkDataArr["payload_cleartext"], $uplinkDataArr["date_time"], $type_msg, $uplinkDataArr["longitude_msg"], $uplinkDataArr["latitude_msg"]);

      if ($success) {
        if ($type_msg == "choc") {

          $chocManager = new ChocManager($payload_decoded_json);

          if (!$chocManager->insertChocData($payload_decoded_json)) {
            return false;
          }

          $time_period = 30;
          $chocManager->setStdDevRule(1);
          $hasAlert = $chocManager->check($sensor_id, 30);
          $chocValue = $chocManager->getPowerValueChoc();

          //Create new alert if it's the case
          if ($hasAlert) {

            $eventDataArr = array(
              "label"  => "high_choc",
              "deveui"  => $uplinkDataArr["deveui"],
              "date_time"  => $uplinkDataArr["date_time"],
              "equipement_id"  => $equipement_id,
              "value"  => $chocValue
            );

            $alertManager = new AlertManager($eventDataArr);
            $alertManager->createFromArr($eventDataArr);
          }
        }
        //battery data
        else if ($type_msg == "global") {
          $batteryManager = new BatteryManager();

          if (!$batteryManager->insertBatteryData($payload_decoded_json)) {
            return false;
          }
        }
        //Inclinometer data
        else if ($type_msg == "inclinometre") {
          $inclinometreManager = new InclinometerManager();

          if (!$inclinometreManager->insertInclinometerData($payload_decoded_json)) {
            return false;
          }
        }
        //Subspectre data
        else if ($type_msg == "spectre") {
          $spectreManager = new SpectreManager();

          if (!$spectreManager->insertSpectreData($payload_decoded_json)) {
            return false;
          }
        }

        return true;
      }
      //exit();
      //Choc data

      //If event defined in Objenious 
    } else if ($type_msg == "event") {

      $eventDataArr = RecordManager::extractEventData($data);

      $sensor_id = $sensorManager->getSensorIdFromDeveui($eventDataArr["deveui"]);
      $equipement_id = $equipementManager->getEquipementIdBySensorId($sensor_id);
      $eventDataArr['equipement_id'] = $equipement_id;
      $eventDataArr['value'] = 0;

      $alertManager = new AlertManager($eventDataArr);
      $alertManager->createFromArr($eventDataArr);
    } else if ($type_msg == "downlink") {
    } else if ($type_msg == "join") {
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
  function initPool($group_name)
  {

    $resultsArr = RecordManager::getCoupleStructureIDSensorIDFromRecord($group_name);
    //print_r($resultsArr);
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
   * @param string $group_name group to deal with (RTE)
   * @return void
   */
  public static function getCoupleStructureIDSensorIDFromRecord($group_name)
  {
    $db = static::getDB();

    $sql = "
    SELECT DISTINCT r.structure_id, r.sensor_id FROM record AS r
    LEFT JOIN structure AS st ON (st.id=r.structure_id)
    LEFT JOIN site AS s ON (s.id = st.site_id)
    LEFT JOIN sensor ON (sensor.id=r.sensor_id)
    LEFT JOIN sensor_group AS gs ON (gs.sensor_id=sensor.id)
    LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE gn.name = :group_name
    ";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
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
      $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $results[0];
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

  public static function checkTypeMessage($type_msg)
  {
    switch ($type_msg) {
      case "ChangeStatusActive":
        return "event";
      case "ChangeStatusInactive":
        return "event";
      case "ChangeStatusError":
        return "event";
      case "ChangeStatusJoined":
        return "event";
      case "uplink":
        return "uplink";
      case "downlink":
        return "downlink";
      case "join":
        return "join";
    }
  }


  /**
   * extract uplink data from objenious
   *
   * @param json $data json data received from Objenious
   * @return array 
   */
  public static function extractUplinkData($data)
  {

    $id_uplink = $data['id'];
    $profile = $data['profile'];
    $profile_id = $data['profile_id'];
    $group = $data['group'];
    $group_id = $data['group_id'];
    $type = $data['type'];
    $device_id = $data['device_id'];
    $count = $data['count'];
    $geolocation_precision = $data['geolocation_precision'];
    $geolocation_type = $data['geolocation_type'];
    $latitude_msg = $data['lat'];
    $longitude_msg = $data['lng'];
    $payload_data = $data['payload'];
    $payload_cleartext = $data['payload_cleartext'];
    $device_properties = $data['device_properties'];

    $external_id = $device_properties['external_id'];
    $appeui = $device_properties['appeui'];
    $deveui_sensor = $device_properties['deveui'];
    $timestamp = $data['timestamp'];
    //print_r($data['timestamp']);
    //echo gettype($data['timestamp']);
    //exit();
    $date_time = RecordManager::convertTimestampToDateTime($timestamp);

    $name_asset = RecordManager::extractExternalId($external_id);

    #Provisory solution
    $type_asset = "";
    if (strpos($name_asset, 'tower') !== false) {
      $type_asset = "transmission line";
    } else {
      $type_asset = "undefined";
    }

    $uplinkDataArr = array(
      "id_uplink"  => $id_uplink,
      "profile"  => $profile,
      "profile_id"  => $profile_id,
      "nb_message"  => $count,
      "type_asset"  => $type_asset,
      "name_asset"  => $name_asset,
      "latitude_msg"  => $latitude_msg,
      "longitude_msg"  => $longitude_msg,
      "payload_cleartext"  => $payload_cleartext,
      "date_time"  => $date_time,
      "device_id"  => $device_id,
      "deveui"  => $deveui_sensor,
      "type"  => $type,
    );

    return $uplinkDataArr;
  }

  /**
   * extract event data from objenious
   *
   * @param json $data json data received from Objenious
   * @return array 
   */
  public static function extractEventData($data)
  {
    $date_time = RecordManager::convertTimestampToDateTime($data['timestamp']);

    $device_id = $data['device_id'];
    $type = $data['type'];
    $device_properties = $data['device_properties'];

    $external_id = $device_properties['external_id'];
    $deveui = $device_properties['deveui'];
    $property = $device_properties['property'];

    $name_asset = RecordManager::extractExternalId($external_id);

    $eventDataArr = array(
      "date_time"  => $date_time,
      "device_id"  => $device_id,
      "deveui"  => $deveui,
      "label"  => $type,
      "name_asset"  => $name_asset
    );

    return $eventDataArr;
  }

  /**
   * extract external_id data from Objenious (which correspond to the label of a sensor in Objenious)
   *
   * @param string $external_id 
   * @return array 
   */
  public static function extractExternalId($external_id)
  {
    #Remove bracket
    $asset_name_no_bracket = str_replace(array('[', ']'), '', $external_id);
    $asset_name_array = explode("-", $asset_name_no_bracket);
    $region = $asset_name_array[0];
    $ligne = $asset_name_array[1];
    $desc_asset = $asset_name_array[2];
    $support_asset = $asset_name_array[3];
    $corniere = $asset_name_array[4];

    #Build the asset name
    $name_asset = $desc_asset . "_" . $support_asset;

    return $name_asset;
  }

  public static function convertTimestampToDateTime($timestamp, $datetimeFormat = 'Y-m-d H:i:s')
  {
    //Split 2019-11-29T16:01:26.572226000Z to keep only the last part 572226000Z
    $part = explode(".", $timestamp);
    $second = substr($part[1], 0, 3);
    //Finnaly we get 2019-11-29T16:01:26.572Z
    $secondTimeZone = $second . "Z";
    $timestamp = $part[0] . "." . $secondTimeZone;

    $timezone = new \DateTimeZone(date_default_timezone_get());

    $date     = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $timestamp, $timezone);
    $date = new \DateTime($timestamp, $timezone);
    $date_time = $date->format($datetimeFormat);

    return $date_time;
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
  public static function insertRecordData($deveui_sensor, $name_asset, $payload_cleartext, $date_time, $type_msg, $longitude, $latitude)
  {
    $data_record = 'INSERT INTO  record (`sensor_id`,  `structure_id`, `payload`, `date_time`,  `msg_type`,`longitude`, `latitude`)
    SELECT * FROM
    (SELECT (SELECT id FROM sensor WHERE deveui LIKE :deveui_sensor),
    (SELECT id FROM structure WHERE nom like :name_asset),
    :payload_raw, :date_time, :type_msg, :longitude, :latitude) AS id_record
    WHERE NOT EXISTS (
      SELECT date_time FROM record WHERE date_time = :date_time
    ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($data_record);

    $stmt->bindValue(':deveui_sensor', $deveui_sensor, PDO::PARAM_STR);
    $stmt->bindValue(':name_asset', $name_asset, PDO::PARAM_STR);
    $stmt->bindValue(':payload_raw', $payload_cleartext, PDO::PARAM_STR);
    $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);
    $stmt->bindValue(':type_msg', $type_msg, PDO::PARAM_STR);
    $stmt->bindValue(':longitude', $longitude, PDO::PARAM_STR);
    $stmt->bindValue(':latitude', $latitude, PDO::PARAM_STR);

    $stmt->execute();
    $count = $stmt->rowCount();
    if ($count == '0') {
      echo "\n0 rows were affected\n";
      return false;
    } else {
      echo "\nSuccess: At least 1 row was affected.\n";
      return true;
    }
  }


  /**
   * Decode Payload message in order to extract information. (Inclinometer, battery, choc...)
   *
   * @param string $payload_cleartext uplink payload message
   * @return json  data decoded in json format
   */
  public static function decodePayload($payload_cleartext)
  {
    $preambule_hex = substr($payload_cleartext, 0, 2);
    $preambule_bin = substr(Utilities::hexStr2bin($preambule_hex), 0, 2);
    /*echo "\n Preambule HEX : " . $preambule_hex;
    echo "\n Hex2bin : " . Utilities::hexStr2bin($preambule_hex);
    echo "\n Preambule Bin : " . $preambule_bin;*/

    if ($preambule_bin == "00") {
      echo "\n ==> TYPE MESSAGE RECEIVED : Inclinometre data <===";
      $msgDecoded = RecordManager::decodeInclinometreMsg($payload_cleartext);
      return $msgDecoded;
    } else if ($preambule_bin == "10") {
      echo "\n ==> TYPE MESSAGE RECEIVED : choc_data data <===";
      $msgDecoded = RecordManager::decodeShockMsg($payload_cleartext);
      return $msgDecoded;
    } else if ($preambule_bin == "11") {
      echo "\n ==> TYPE MESSAGE RECEIVED : global data <===";
      $msgDecoded = RecordManager::decodeGlobalMsg($payload_cleartext);
      return $msgDecoded;
    } else if ($preambule_bin == "01") {
      echo "\n ==> TYPE MESSAGE RECEIVED : spectre data <===";
      $msgDecoded = RecordManager::decodeSpectreMsg($payload_cleartext);
      return $msgDecoded;
    } else {
      return "UNDEFINED";
    }
  }

  /**
   * Decode a spectre message
   *
   * @param string $payload_cleartext payload data
   * @return json  data decoded in json format which contain the spectre raw data
   */
  public static function decodeSpectreMsg($payload_cleartext)
  {
    #Take the preambule
    //echo "Payload HEX : " . $payload_hex;
    $spectre_msg_hex = $payload_cleartext;
    $preambule_hex = substr($payload_cleartext, 0, 2);
    $preambule_bin = Utilities::hexStr2bin($preambule_hex);
    $spectre_msg_dec = "";

    for ($i = 2; $i < intval(strlen(strval($spectre_msg_hex))); $i += 2) {
      $data_i_hex = substr($spectre_msg_hex, $i, 2);
      $data_i_dec = Utilities::hex2dec($data_i_hex);
      $spectre_msg_dec .= strval($data_i_dec);
    }

    #Extract data from prembule
    $idspectre = substr($preambule_bin, 0, 2);
    $occurence = substr($preambule_bin, 2, 2);
    $nc = substr($preambule_bin, 4, 1);
    $spectre_number = substr($preambule_bin, 5, 3);

    //echo "spectre_number : " . $spectre_number;

    $resolution = 0;
    $min_freq = 0;
    $max_freq = 0;

    if (strval($spectre_number) == "000") {
      $resolution = 0;
      $min_freq = 0;
      $max_freq = 0;
    } else if (strval($spectre_number) == "001") {
      $resolution = 1;
      $min_freq = 20;
      $max_freq = 69;
    } else if (strval($spectre_number) == "010") {
      $resolution = 2;
      $min_freq = 70;
      $max_freq = 169;
    } else if (strval($spectre_number) == "011") {
      $resolution = 4;
      $min_freq = 170;
      $max_freq = 369;
    } else if (strval($spectre_number) == "100") {
      $resolution = 8;
      $min_freq = 370;
      $max_freq = 769;
    } else if (strval($spectre_number) == "101") {
      $resolution = 16;
      $min_freq = 770;
      $max_freq = 1569;
    }


    $spectreMSGDecoded = (object) [
      'type' => 'spectre',
      'spectre_number' => $spectre_number,
      'resolution' => $resolution,
      'min_freq' => $min_freq,
      'max_freq' => $max_freq,
      'spectre_msg_hex' => $spectre_msg_hex,
      'spectre_msg_dec' => $spectre_msg_dec
    ];

    //echo json_encode($spectreMSGDecoded);

    return json_encode($spectreMSGDecoded, true);
  }

  /**
   * Decode a global message (battery data)
   *
   * @param string $payload_cleartext payload data
   * @return json  data decoded in json format which contain the battery raw data
   */
  public static function decodeGlobalMsg($payload_hex)
  {
    #Take the preambule
    $preambule_hex = substr($payload_hex, 0, 2);
    $preambule_bin = Utilities::hexStr2bin($preambule_hex);
    /*echo "\n Preambule hex " . $preambule_hex;
    echo "\n Preambule bin " . $preambule_bin;*/
    #Extract data from prembule
    $idglobal = substr($preambule_bin, 0, 2);
    $batteryState = substr($preambule_bin, 2, 1);
    $error = substr($preambule_bin, 3, 1);
    $state = substr($preambule_bin, 4, 1);
    $spectre = substr($preambule_bin, 5, 1);
    $inclinometre = substr($preambule_bin, 6, 1);
    $shock = substr($preambule_bin, 7, 1);

    #Extract data from the second part
    $batteryLevel = Utilities::hex2dec(substr($payload_hex, 2, 2));

    $globalMSGDecoded = (object) [
      'type' => 'global',
      'batteryLevel' => $batteryLevel,
      'idglobal' => $idglobal,
      'batteryState' => $batteryState,
      'error' => $error,
      'state' => $state,
      'spectre' => $spectre,
      'inclinometre' => $inclinometre,
      'shock' => $shock
    ];
    //echo json_encode($globalMSGDecoded);
    return json_encode($globalMSGDecoded);
  }

  /**
   * Decode a choc message
   *
   * @param string $payload_cleartext payload data
   * @return json  data decoded in json format which contain the choc raw data
   */
  public static function decodeShockMsg($payload_hex)
  {
    #Take the preambule
    $preambule_hex = substr($payload_hex, 0, 2);
    $preambule_bin = Utilities::hexStr2bin($preambule_hex);

    #Extract data from prembule
    $idShock = substr($preambule_bin, 0, 2);
    $limiteFrequence = substr($preambule_bin, 2, 2);
    $redondanceMsg = substr($preambule_bin, 4, 1);
    $seuil = substr($preambule_bin, 5, 3);

    #Extract data from the second part
    $msgSecondPart = substr($payload_hex, 2, strlen($payload_hex) - 2);
    $amplitude1 = Utilities::accumulatedTable16(Utilities::hex2dec(substr($msgSecondPart, 0, 2)));
    $time1 = Utilities::hex2dec(substr($msgSecondPart, 2, 2));
    $time1 = ($time1 + 1) * 200; //# 200 is micro second format

    $amplitude2 = Utilities::accumulatedTable16(Utilities::hex2dec(substr($msgSecondPart, 4, 2)));
    $time2 = Utilities::hex2dec(substr($msgSecondPart, 6, 2));
    $time2 = ($time2 + 1) * 200; //# 200 is micro second format

    $chocMsgDecoded = (object) [
      'type' => 'choc',
      'idShock' => $idShock,
      'limiteFrequence' => $limiteFrequence,
      'redondanceMsg' => $redondanceMsg,
      'seuil' => $seuil,
      'amplitude1' => $amplitude1,
      'time1' => $time1,
      'amplitude2' => $amplitude2,
      'time2' => $time2
    ];

    return json_encode($chocMsgDecoded, true);
  }

  /**
   * Decode an inclinometer message
   *
   * @param string $payload_cleartext payload data
   * @return json  data decoded in json format which contain the inclinometer raw data
   */
  public static function decodeInclinometreMsg($payload_hex)
  {
    #Take the preambule
    $preambule_hex = substr($payload_hex, 0, 2);
    $preambule_bin = Utilities::hexStr2bin($preambule_hex);
    /*echo "\n Preambule hex " . $preambule_hex;
    echo "\n Preambule bin " . $preambule_bin;*/
    #Extract data from prembule
    $idInclinometre = substr($preambule_bin, 0, 2);
    $occurence = substr($preambule_bin, 2, 2);
    $zeroing = substr($preambule_bin, 4, 2);

    if ($preambule_bin == 0) {
      $idInclinometre = "00";
      $occurence = "00";
      $zeroing = "00";
    }


    #Extract data from the second part
    $msgSecondPart = substr($payload_hex, 2, strlen($payload_hex) - 2);
    /*echo "\n MSG second part " . $msgSecondPart;
    echo "\n idInclinometre " . $idInclinometre;
    echo "\n" .hexdec("E2");*/
    $val = Utilities::hex2dec("F2");
    $X = Utilities::hex2dec(substr($msgSecondPart, 0, 4)) * 0.0625;
    $Y = Utilities::hex2dec(substr($msgSecondPart, 4, 4)) * 0.0625;
    $Z = Utilities::hex2dec(substr($msgSecondPart, 8, 4)) * 0.0625;
    $temperature =  Utilities::hex2dec(substr($msgSecondPart, 12, 4)) / 10;

    $inclinometreMsgDecoded = (object) [
      'type' => 'inclinometre',
      'idInclinometre' => $idInclinometre,
      'occurence' => $occurence,
      'zeroing' => $zeroing,
      'X' => $X,
      'Y' => $Y,
      'Z' => $Z,
      'temperature' => $temperature
    ];

    return json_encode($inclinometreMsgDecoded, true);
  }

  /** 
   * Get the last date of the last message received by a specific sensor
   *
   * @param int $sensor_id if of a sensor  
   * @return array results of the query
   *  date
   * 
   */
  public function getLastDateRecordForSensor($sensor_id = 0)
  {
    $db = static::getDB();
    $sql_last_date = "SELECT s.device_number, MAX(DATE(r.date_time)) as dateMaxReceived 
      FROM record AS r 
      LEFT JOIN sensor AS s ON (s.id = r.sensor_id) ";
    //All date
    if ($sensor_id != 0) {
      $sql_last_date .= "WHERE s.id = :sensor_id";
    }
    $sql_last_date .= " GROUP BY s.device_number, s.id";

    $stmt = $db->prepare($sql_last_date);
    if ($sensor_id != 0) {
      $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    }

    if ($stmt->execute()) {
      $last_date = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $last_date;
    }
  }

  /** 
   * Get the date of the last message received on a specific structure
   *
   * @param int $structure_id id of the equipement  
   * @return string results of the query
   *  date
   * 
   */
  public function getDateLastReceivedData($structure_id)
  {
    $db = static::getDB();
    $sql_last_date = "SELECT MAX(DATE(r.date_time)) as dateMaxReceived
    FROM record as r
    WHERE r.structure_id = :structure_id";

    $stmt = $db->prepare($sql_last_date);
    $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $last_date = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return $last_date[0];
    }
  }

  /** 
   * Get the min and max date from table record
   *  
   * @return array results of the query
   * 
   */
  function getDateMinMaxFromRecord()
  {
    $db = static::getDB();
    $query_min_max_date = "SELECT (SELECT Max(date_time) FROM record) AS MaxDateTime,
    (SELECT Min(date_time) FROM record) AS MinDateTime";

    $stmt = $db->prepare($query_min_max_date);
    $data = array();
    if ($stmt->execute()) {

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $min_date_time = $row["MinDateTime"];
      $max_date_time = $row["MaxDateTime"];
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
  function getBriefInfoFromRecord($group_name)
  {

    $db = static::getDB();

    $query_get_number_record = "
        SELECT 
    sensor_id, 
    site, 
    ligneHT, 
    equipement, 
    nb_messages,
    nb_choc, 
    DATE_FORMAT(
      last_message_received, '%d/%m/%Y'
    ) AS `last_message_received` ,
    status
  FROM 
    (
      SELECT 
        sensor.device_number AS 'sensor_id', 
        s.nom AS `site`, 
         sensor.status AS status,
        st.transmision_line_name AS `LigneHT`, 
        st.nom AS `equipement`, 
        sum(
          case when msg_type = 'choc' then 1 else 0 end
        ) AS 'nb_choc', 
        count(*) AS 'nb_messages', 
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
      
      GROUP BY 
        r.sensor_id, 
        st.nom, 
        s.nom, 
        st.transmision_line_name
    ) AS all_message_rte_sensor";
    //  AND Date(r.date_time) >= Date(sensor.installation_date) 
    $stmt = $db->prepare($query_get_number_record);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $res = $stmt->fetchAll();
      return $res;
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
  function getDataMap($group_name)
  {
    $db = static::getDB();

    $query_data_map = "SELECT DISTINCT r.sensor_id, s.latitude AS latitude_site, s.longitude AS longitude_site,
    st.latitude AS latitude_sensor, st.longitude AS longitude_sensor, s.nom AS site, st.nom AS equipement
    FROM record AS r
    INNER JOIN sensor ON (sensor.id=r.sensor_id)
    INNER JOIN structure AS st ON r.structure_id = st.id
    INNER JOIN site AS s ON s.id = st.site_id
    INNER JOIN sensor_group AS gs ON (gs.sensor_id=sensor.id)
    INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE gn.name LIKE :group_name";

    $stmt = $db->prepare($query_data_map);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $data_map = $stmt->fetchAll();
      return $data_map;
    }
  }

  function getAllRawRecord()
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

  public function getAllSpecificMsgForSpecificId($site_id, $equipment_id, $typeMSG, $dateMin, $dateMax)
  {

    $db = static::getDB();

    $sql_query_all_specific_msg =  "SELECT r.sensor_id AS `sensorID`, r.date_time AS `dateTime`,
    r.msg_type AS `typeMessage`, s.nom AS `site`, st.nom AS `equipement`
    FROM record as r
    LEFT JOIN sensor on sensor.id=r.sensor_id
    LEFT JOIN structure AS st
    on st.id=r.structure_id
    LEFT JOIN site AS s
    ON s.id = st.site_id
    WHERE ";

    if (!empty($dateMin) && !empty($dateMax)) {
      $sql_query_all_specific_msg =  "(date(r.date_time) BETWEEN date(CONCAT(:date_min, '%')) and date(CONCAT(:date_max, '%'))) AND ";
    }

    $sql_query_all_specific_msg .= "Date(r.date_time) >= Date(sensor.installation_date)
    AND s.id LIKE :site_id AND r.msg_type LIKE CONCAT(:type_msg, '%') AND st.id LIKE :equipment_id order by r.date_time desc ";

    $stmt = $db->prepare($sql_query_all_specific_msg);

    if (!empty($dateMin) && !empty($dateMax)) {
      $stmt->bindValue(':date_min', $dateMin, PDO::PARAM_STR);
      $stmt->bindValue(':date_max', $dateMax, PDO::PARAM_STR);
    }
    $stmt->bindValue(':type_msg', $typeMSG, PDO::PARAM_STR);
    $stmt->bindValue(':site_id', $site_id, PDO::PARAM_STR);
    $stmt->bindValue(':equipment_id', $equipment_id, PDO::PARAM_STR);

    if ($stmt->execute()) {

      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  public function getAllDataForChart($site_id, $equipment_id, $dateMin, $dateMax)
  {

    $db = static::getDB();
    //All
    $data = array();
    //Find ID sensor from site ID and equipement ID
    $sql_query_id =  "SELECT DISTINCT(`sensor_id`) FROM `record` AS r
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE s.id = :site_id AND st.id = :equipment_id ";

    $stmt = $db->prepare($sql_query_id);

    $stmt->bindValue(':site_id', $site_id, PDO::PARAM_INT);
    $stmt->bindValue(':equipment_id', $equipment_id, PDO::PARAM_INT);

    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    $sensor_id = $res['sensor_id'];

    //Temperature
    $sql_query_temperature = "SELECT
    `temperature`,
    DATE(r.date_time) AS date_d
    FROM
    inclinometer AS inc
    LEFT JOIN record AS r ON (r.id = inc.record_id)
    LEFT JOIN sensor on sensor.id = r.sensor_id
    INNER JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
    INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
    gn.name = :group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)
    AND `msg_type` LIKE :msg_type
    AND r.sensor_id LIKE :sensor_id
    AND Date(r.date_time) >= Date(sensor.installation_date)
    ORDER BY
    `date_d` DESC";

    $stmt = $db->prepare($sql_query_temperature);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    $stmt->bindValue(':msg_type', "inclinometre", PDO::PARAM_STR);
    $stmt->bindValue(':group_name', $_SESSION['group_name'], PDO::PARAM_STR);

    if ($stmt->execute()) {
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data["temperature_data"][] = $row;
      }
    }

    $query_inclinometre = "SELECT r.sensor_id, DATE(r.date_time) AS date_d,  inc.nx, inc.ny, inc.nz, angle_x, angle_y, angle_z, temperature
    FROM inclinometer AS inc
    LEFT JOIN record AS r ON (r.id = inc.record_id)
    LEFT JOIN sensor on sensor.id=r.sensor_id
    INNER JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
    INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
    gn.name = :group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)
    AND `msg_type` LIKE :msg_type
    AND r.sensor_id LIKE :sensor_id
    AND Date(r.date_time) >= Date(sensor.installation_date)
    ORDER BY
    `date_d` DESC";

    $stmt = $db->prepare($query_inclinometre);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    $stmt->bindValue(':msg_type', "inclinometre", PDO::PARAM_STR);
    $stmt->bindValue(':group_name', $_SESSION['group_name'], PDO::PARAM_STR);

    $stmt->execute();
    if ($stmt->execute()) {
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data["inclinometre_data"][] = $row;
      }
    }


    //Simplifier TODO
    $sql_query_date_max_choc = "SELECT
    MAX(date_d) AS max_date
    FROM
    (
      SELECT
      `sensor_id`,
      DATE(r.date_time) AS date_d,
      amplitude_1,
      amplitude_2,
      time_1,
      time_2,
      freq_1,
      freq_2,
      power
      FROM
      choc
      LEFT JOIN record AS r ON (r.id = choc.record_id)
      LEFT JOIN sensor on sensor.id = r.sensor_id
      WHERE
      `msg_type` LIKE :msg_type
      AND `sensor_id` LIKE :sensor_id
      AND Date(r.date_time) >= Date(sensor.installation_date)
    ) AS TMP";

    $stmt = $db->prepare($sql_query_date_max_choc);
    $stmt->bindValue(':msg_type', "choc", PDO::PARAM_STR);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    $stmt->execute();

    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_date_choc = $res['max_date'];


    $sql_query_choc = "SELECT
    `sensor_id`,
    DATE(`date_time`) AS date_d,
    amplitude_1,
    amplitude_2,
    time_1,
    time_2,
    freq_1,
    freq_2,
    power
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    WHERE
    `msg_type` LIKE :msg_type
    AND `sensor_id` LIKE :sensor_id
    AND DATE(r.date_time) = :max_date_choc";

    $stmt = $db->prepare($sql_query_choc);

    $stmt->bindValue(':msg_type', "choc", PDO::PARAM_STR);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    $stmt->bindValue(':max_date_choc', $max_date_choc, PDO::PARAM_STR);

    if ($stmt->execute()) {
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data["choc_data"][] = $row;
      }
    }


    //All Spectre
    $sql_query_date_max_spectre = "SELECT
    MAX(date_d) AS max_date
    FROM
    (
      SELECT
      s.nom AS site,
      st.nom AS equipement,
      r.sensor_id,
      r.date_time as date_d,
      subspectre,
      subspectre_number,
      min_freq,
      max_freq,
      resolution
      FROM
      spectre AS sp
      LEFT JOIN record AS r ON (r.id = sp.record_id)
      JOIN sensor on sensor.id = r.sensor_id
      JOIN structure as st ON (st.id = r.structure_id)
      JOIN site as s ON (s.id = st.site_id)
      WHERE
      sp.subspectre_number LIKE '001'
      AND r.sensor_id LIKE :sensor_id
      AND Date(r.date_time) >= Date(sensor.installation_date)
      ORDER BY
      r.date_time ASC
    ) AS first_subspectre_msg";

    $stmt = $db->prepare($sql_query_date_max_spectre);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    $stmt->execute();

    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_date = $res['max_date'];


    $query_all_dates = "SELECT Date(r.date_time) as date_d FROM
    `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE sp.subspectre_number LIKE '001' AND r.sensor_id LIKE :sensor_id ";
    if (!empty($dateMin) && !empty($dateMax)) {
      $query_all_dates .= "AND (date(r.date_time) BETWEEN date('$dateMin%') and date('$dateMax%')) ";
    }
    $query_all_dates .= "ORDER BY r.date_time ASC";

    //echo "</br>";
    $stmt = $db->prepare($query_all_dates);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    $spectrenumber = 0;
    if ($stmt->execute()) {
      $row_date_ = $stmt->fetchAll();

      foreach ($row_date_ as $row_date) {
        $spectre_name = 'spectre_' . $spectrenumber;
        $current_date = $row_date['date_d'];
        //Reconstruct the all spectre for the current date
        $query_all_spectre_i = "SELECT s.nom, st.nom, r.sensor_id, Date(r.date_time) AS date,
        `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
        JOIN record AS r ON (r.id=sp.record_id)
        JOIN structure as st ON (st.id=r.structure_id)
        JOIN site as s ON (s.id=st.site_id)
        WHERE r.sensor_id LIKE :sensor_id AND (DATE(r.date_time) BETWEEN DATE('$current_date') AND DATE_ADD('$current_date', INTERVAL 4 DAY))
        ORDER BY r.date_time ASC";

        $stmt = $db->prepare($query_all_spectre_i);
        $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

        if ($stmt->execute()) {
          while ($row_spectre = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data["spectre_data"][$spectre_name][] = $row_spectre;
          }
        }
        $spectrenumber++;
      }
    }
    //print json_encode($data);
    return $data;
    //
  }


  public function getDataForSpecificChart($time_data, $type_msg, $sensor_id)
  {
    $db = static::getDB();
    $useTimeData = True;
    if ($type_msg == "global") {
      //Temperature
      $sql_query = "SELECT
      `temperature`,
      DATE(`date_time`) AS date_d
      FROM
      inclinometer AS inc
      LEFT JOIN record AS r ON (r.id = inc.record_id)
      WHERE
      `msg_type` LIKE 'inclinometre'
      AND `sensor_id` LIKE :sensor_id
      ORDER BY
      date_d ASC ";
      $useTimeData = False;
    } else if ($type_msg == "inclinometre") {
      //Inclinometre
      $sql_query = "SELECT
      `sensor_id`,
      DATE(`date_time`) AS date_d,
      `nx`,
      `ny`,
      `nz`,
      `temperature`,
      inc.nx,
      inc.ny,
      inc.nz,
      angle_x,
      angle_y,
      angle_z,
      temperature
      FROM
      inclinometer AS inc
      LEFT JOIN record AS r ON (r.id = inc.record_id)
      WHERE
      `msg_type` LIKE 'inclinometre'
      AND `sensor_id` LIKE :sensor_id
      AND r.date_time LIKE :time_data";
    } else if ($type_msg == "choc") {
      //Choc
      $sql_query = "SELECT
      `sensor_id`,
      DATE(`date_time`) AS date_d,
      amplitude_1,
      amplitude_2,
      time_1,
      time_2,
      freq_1,
      freq_2,
      power
      FROM
      choc
      LEFT JOIN record AS r ON (r.id = choc.record_id)
      WHERE
      `msg_type` LIKE 'choc'
      AND `sensor_id` LIKE :sensor_id
      AND r.date_time LIKE :time_data
      ";
    } else if ($type_msg == "spectre") {
      //Choc
      //Sub Spectre
      $sql_query = "SELECT
      s.nom,
      st.nom,
      r.sensor_id,
      r.payload,
      r.date_time AS date_d,
      subspectre,
      subspectre_number,
      min_freq,
      max_freq,
      resolution
      FROM
      spectre AS sp
      LEFT JOIN record AS r ON (r.id = sp.record_id)
      JOIN structure as st ON (st.id = r.structure_id)
      JOIN site as s ON (s.id = st.site_id)
      WHERE
      r.date_time LIKE :time_data
      AND r.sensor_id = :sensor_id ";
    }


    $data = array();

    $stmt = $db->prepare($sql_query);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($useTimeData) {
      $stmt->bindValue(':time_data', $time_data, PDO::PARAM_STR);
    }


    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
  }

  public function getMessagesExchangedDeviceUsingIdFromAPI($device_id, $since = null, $until = null)
  {

    $url = "https://api.objenious.com/v1/devices/" . $device_id . "/messages";
    if (isset($since) && isset($until)) {
      $url .= "?since=" . $since . "&until=" . $until;
    }
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);
    $messages_device = $results_api["messages"];

    return $messages_device;
  }


  public function getMessagesExchangedDeviceUsingDeveuiFromAPI($deveui, $since = null, $until = null)
  {

    $url = "https://api.objenious.com/v1/devices/lora:" . $deveui . "/messages";
    if (isset($since) && isset($until)) {
      $url .= "?since=" . $since . "&until=" . $until;
    }
    $results_api = ControllerDataObjenious::CallAPI("GET", $url);
    $messages_device = $results_api["messages"];

    return $messages_device;
  }

  /**
   * Parse json data and then insert into the DB
   *
   * @param json $jsondata json data received from Objenious. This file contain the uplink message
   * @return boolean  True if data has been correctly inserted, true otherwise
   */
  function parseJsonDataAndInsertOLD($jsondata)
  {
    //Get all the interesting content from JSON data
    $id = $jsondata['id'];
    $profile = $jsondata['profile'];
    $group = $jsondata['group'];
    $device_id = $jsondata['device_id'];
    $count = $jsondata['count'];
    $geolocation_precision = $jsondata['geolocation_precision'];
    $geolocation_type = $jsondata['geolocation_type'];
    $latitude_msg = $jsondata['lat'];
    $longitude_msg = $jsondata['lng'];
    $payload_data = $jsondata['payload'];
    $payload_cleartext = $jsondata['payload_cleartext'];
    $device_properties = $jsondata['device_properties'];
    $asset_name = $device_properties['external_id'];
    $appeui = $device_properties['appeui'];
    $deveui_sensor = $device_properties['deveui'];
    $timestamp = $payload_data[0]['timestamp'];
    $type_msg =  $jsondata['type'];

    #Remove bracket
    $asset_name_no_bracket = str_replace(array('[', ']'), '', $asset_name);
    $asset_name_array = explode("-", $asset_name_no_bracket);
    $region = $asset_name_array[0];
    $ligne = $asset_name_array[1];
    $desc_asset = $asset_name_array[2];
    $support_asset = $asset_name_array[3];
    $corniere = $asset_name_array[4];

    #Build the asset name
    $name_asset = $desc_asset . "_" . $support_asset;

    #Provisory solution
    $type_asset = "";
    if (strpos($desc_asset, 'pylone') !== false) {
      $type_asset = "transmission line";
    } else {
      $type_asset = "undefined";
    }

    $geocoder = new \OpenCage\Geocoder\Geocoder(\App\Config::GEOCODER_API_KEY);
    #$$$res ult = $geocoder->geocode($region, ['language' => 'fr', 'countrycode' => 'fr']);

    //Add structure type to the DB
    $equipementManager = new EquipementManager();
    $equipementManager->insertStructureType($type_asset);
    if (!$equipementManager->insertStructureType($type_asset)) {
      return false;
    }

    $datetimeFormat = 'Y-m-d H:i:s';
    $date = new \DateTime($timestamp);
    $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $date_time = $date->format($datetimeFormat);

    //As we received a payload message, we need to decode it
    $msg_json = RecordManager::decodePayload($payload_cleartext);
    $payload_decoded_json = json_decode($msg_json, true);

    #Add date time attribute to the decoded payload
    $payload_decoded_json['date_time'] = $date_time;
    $payload_decoded_json['deveui'] = $deveui_sensor;
    print_r($payload_decoded_json);

    //Get the type of message
    $type_msg = $payload_decoded_json["type"];

    //Insert a record inside the Record table of the DB
    RecordManager::insertRecordData($deveui_sensor, $name_asset, $payload_cleartext, $date_time, $type_msg, $longitude_msg, $latitude_msg);

    //Then add the corresponding type of data received
    //Choc data
    if ($type_msg == "choc") {
      $chocManager = new ChocManager();

      if (!$chocManager->insertChocData($payload_decoded_json)) {
        return false;
      }
    }
    //battery data
    else if ($type_msg == "global") {
      $batteryManager = new BatteryManager();

      if (!$batteryManager->insertBatteryData($payload_decoded_json)) {
        return false;
      }
    }
    //Inclinometer data
    else if ($type_msg == "inclinometre") {
      $inclinometreManager = new InclinometerManager();

      if (!$inclinometreManager->insertInclinometerData($payload_decoded_json)) {
        return false;
      }
    }
    //Subspectre data
    else if ($type_msg == "spectre") {
      $spectreManager = new SpectreManager();

      if (!$spectreManager->insertSpectreData($payload_decoded_json)) {
        return false;
      }
    }

    return True;
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
