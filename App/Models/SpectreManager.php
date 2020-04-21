<?php

namespace App\Models;

use PDO;
use App\Utilities;

/*
SpectreManager.php
Handle the spectre data CRUD on the database
author : Lirone Samoun

*/

class SpectreManager extends \Core\Model
{


  public static function reconstituteAllSpectreForSensorFirstGeneration($deveui)
  {
    $fullSpectreArr = array();

    $allSubspectresArr = SpectreManager::getAllFirstSubspectreForSensorFirstGeneration($deveui);

    $spectreID = 1;

    foreach ($allSubspectresArr as $firstSubSpectreArr) {
      $spectre_name = 'spectre_' . $spectreID;

      $record_id = $firstSubSpectreArr["record_id"];
      $date_time = $firstSubSpectreArr["date_time"];
      $fullSpectreArr[$spectre_name]["min_freq"] = 20;
      $fullSpectreArr[$spectre_name]["max_freq"] = 1569;
      $fullSpectreArr[$spectre_name]["record_id"] = $record_id;
      $fullSpectreArr[$spectre_name]["date_time"] = $date_time;
      $fullSpectreArr[$spectre_name]["structure_name"] = $firstSubSpectreArr["structure_name"];
      $fullSpectreArr[$spectre_name]["transmission_name"] = $firstSubSpectreArr["transmission_name"];
      $fullSpectreArr[$spectre_name]["site_name"] = $firstSubSpectreArr["site_name"];
      $fullSpectreArr[$spectre_name]["deveui"] = $firstSubSpectreArr["deveui"];

      $subspectreID = 1;

      $full_spectre_decomposed = SpectreManager::getAllSubspectres($deveui, $date_time);

      for ($i = 0; $i < count($full_spectre_decomposed); $i++) {
        $subspectre_name = 'subspectre_' . $subspectreID;

        $subspectreNumber = $full_spectre_decomposed[$i]["subspectre_number"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["record_id"] = $full_spectre_decomposed[$i]["record_id"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["structure_name"] = $full_spectre_decomposed[$i]["structure_name"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["transmission_name"] = $full_spectre_decomposed[$i]["transmission_name"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["site_name"] = $full_spectre_decomposed[$i]["site_name"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["deveui"] = $full_spectre_decomposed[$i]["deveui"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["date_time"] = $full_spectre_decomposed[$i]["date_time"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["data"] = $full_spectre_decomposed[$i]["subspectre"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["resolution"] = $full_spectre_decomposed[$i]["resolution"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["min_freq"] = $full_spectre_decomposed[$i]["min_freq"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["max_freq"] = $full_spectre_decomposed[$i]["max_freq"];

        $subspectreID++;
      }

      $spectreID++;
    }

    return $fullSpectreArr;
  }

  public static function reconstituteAllSpectreForSensorSecondGeneration($deveui)
  {
    $fullSpectreArr = array();

    $allSubspectresArr = SpectreManager::getAllFirstSubspectreForSensorSecondGeneration($deveui);

    $spectreID = 1;

    foreach ($allSubspectresArr as $firstSubSpectreArr) {
      $spectre_name = 'spectre_' . $spectreID;

      $record_id = $firstSubSpectreArr["record_id"];
      $date_time = $firstSubSpectreArr["date_time"];
      $fullSpectreArr[$spectre_name]["min_freq"] = 1;
      $fullSpectreArr[$spectre_name]["max_freq"] = 1550;
      $fullSpectreArr[$spectre_name]["record_id"] = $record_id;
      $fullSpectreArr[$spectre_name]["date_time"] = $date_time;
      $fullSpectreArr[$spectre_name]["structure_name"] = $firstSubSpectreArr["structure_name"];
      $fullSpectreArr[$spectre_name]["transmission_name"] = $firstSubSpectreArr["transmission_name"];
      $fullSpectreArr[$spectre_name]["site_name"] = $firstSubSpectreArr["site_name"];
      $fullSpectreArr[$spectre_name]["deveui"] = $firstSubSpectreArr["deveui"];

      $subspectreID = 1;

      $full_spectre_decomposed = SpectreManager::getAllSubspectres($deveui, $date_time);
      $already_first_resolution = false;
      for ($i = 0; $i < count($full_spectre_decomposed); $i++) {
        $subspectre_name = 'subspectre_' . $subspectreID;
        //Check if we get in the array the beginning of a new spectre because it's possible that we get not 5 subspectres but less depending of the 
        //configuration of the sensor
        if ($full_spectre_decomposed[$i]["resolution"] == 1 && $already_first_resolution == True) {
          return $fullSpectreArr;
        }
        if ($full_spectre_decomposed[$i]["resolution"] == 1) {
          $already_first_resolution = true;
        }

        $subspectreNumber = $full_spectre_decomposed[$i]["subspectre_number"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["record_id"] = $full_spectre_decomposed[$i]["record_id"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["structure_name"] = $full_spectre_decomposed[$i]["structure_name"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["transmission_name"] = $full_spectre_decomposed[$i]["transmission_name"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["site_name"] = $full_spectre_decomposed[$i]["site_name"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["deveui"] = $full_spectre_decomposed[$i]["deveui"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["date_time"] = $full_spectre_decomposed[$i]["date_time"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["data"] = $full_spectre_decomposed[$i]["subspectre"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["resolution"] = $full_spectre_decomposed[$i]["resolution"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["min_freq"] = $full_spectre_decomposed[$i]["min_freq"];
        $fullSpectreArr[$spectre_name][$subspectre_name]["max_freq"] = $full_spectre_decomposed[$i]["max_freq"];

        $subspectreID++;
      }

      $spectreID++;
    }

    return $fullSpectreArr;
  }

  public static function reconstituteOneSpectreForSensorFirstGeneration($deveui, $date_request)
  {

    $fullSpectreArr = array();

    $allSubspectresArr = SpectreManager::getAllFirstSubspectreForSensorFirstGeneration($deveui, $date_request);

    if (isset($date_request)) {
      //There is only one spectre to deal with

      $firstSubSpectreArr = $allSubspectresArr[0];
      $record_id = $firstSubSpectreArr["record_id"];
      $date_time = $firstSubSpectreArr["date_time"];
      $fullSpectreArr["min_freq"] = 20;
      $fullSpectreArr["max_freq"] = 1569;
      $fullSpectreArr["record_id"] = $record_id;
      $fullSpectreArr["date_time"] = $date_time;
      $fullSpectreArr["structure_name"] = $firstSubSpectreArr["structure_name"];
      $fullSpectreArr["transmission_name"] = $firstSubSpectreArr["transmission_name"];
      $fullSpectreArr["site_name"] = $firstSubSpectreArr["site_name"];
      $fullSpectreArr["deveui"] = $firstSubSpectreArr["deveui"];

      $subspectreID = 1;

      $full_spectre_decomposed = SpectreManager::getAllSubspectres($deveui, $date_request);

      for ($i = 0; $i < count($full_spectre_decomposed); $i++) {
        $subspectre_name = 'subspectre_' . $subspectreID;

        $subspectreNumber = $full_spectre_decomposed[$i]["subspectre_number"];

        $fullSpectreArr[$subspectre_name]["record_id"] = $full_spectre_decomposed[$i]["record_id"];
        $fullSpectreArr[$subspectre_name]["structure_name"] = $full_spectre_decomposed[$i]["structure_name"];
        $fullSpectreArr[$subspectre_name]["transmission_name"] = $full_spectre_decomposed[$i]["transmission_name"];
        $fullSpectreArr[$subspectre_name]["site_name"] = $full_spectre_decomposed[$i]["site_name"];
        $fullSpectreArr[$subspectre_name]["deveui"] = $full_spectre_decomposed[$i]["deveui"];
        $fullSpectreArr[$subspectre_name]["date_time"] = $full_spectre_decomposed[$i]["date_time"];
        $fullSpectreArr[$subspectre_name]["data"] = $full_spectre_decomposed[$i]["subspectre"];
        $fullSpectreArr[$subspectre_name]["resolution"] = $full_spectre_decomposed[$i]["resolution"];
        $fullSpectreArr[$subspectre_name]["min_freq"] = $full_spectre_decomposed[$i]["min_freq"];
        $fullSpectreArr[$subspectre_name]["max_freq"] = $full_spectre_decomposed[$i]["max_freq"];

        $subspectreID++;
      }
    }

    //var_dump($fullSpectreArr);
    return $fullSpectreArr;
  }

  public static function reconstituteOneSpectreForSensorSecondGeneration($deveui, $date_request)
  {

    $fullSpectreArr = array();

    $allSubspectresArr = SpectreManager::getAllFirstSubspectreForSensorSecondGeneration($deveui, $date_request);

    if (isset($date_request)) {
      //There is only one spectre to deal with

      $firstSubSpectreArr = $allSubspectresArr[0];
      $record_id = $firstSubSpectreArr["record_id"];
      $date_time = $firstSubSpectreArr["date_time"];
      $fullSpectreArr["min_freq"] = 1;
      $fullSpectreArr["max_freq"] = 1550;
      $fullSpectreArr["record_id"] = $record_id;
      $fullSpectreArr["date_time"] = $date_time;
      $fullSpectreArr["structure_name"] = $firstSubSpectreArr["structure_name"];
      $fullSpectreArr["transmission_name"] = $firstSubSpectreArr["transmission_name"];
      $fullSpectreArr["site_name"] = $firstSubSpectreArr["site_name"];
      $fullSpectreArr["deveui"] = $firstSubSpectreArr["deveui"];

      $subspectreID = 1;

      $full_spectre_decomposed = SpectreManager::getAllSubspectres($deveui, $date_request);

      for ($i = 0; $i < count($full_spectre_decomposed); $i++) {
        $subspectre_name = 'subspectre_' . $subspectreID;

        $subspectreNumber = $full_spectre_decomposed[$i]["subspectre_number"];

        $fullSpectreArr[$subspectre_name]["record_id"] = $full_spectre_decomposed[$i]["record_id"];
        $fullSpectreArr[$subspectre_name]["structure_name"] = $full_spectre_decomposed[$i]["structure_name"];
        $fullSpectreArr[$subspectre_name]["transmission_name"] = $full_spectre_decomposed[$i]["transmission_name"];
        $fullSpectreArr[$subspectre_name]["site_name"] = $full_spectre_decomposed[$i]["site_name"];
        $fullSpectreArr[$subspectre_name]["deveui"] = $full_spectre_decomposed[$i]["deveui"];
        $fullSpectreArr[$subspectre_name]["date_time"] = $full_spectre_decomposed[$i]["date_time"];
        $fullSpectreArr[$subspectre_name]["data"] = $full_spectre_decomposed[$i]["subspectre"];
        $fullSpectreArr[$subspectre_name]["resolution"] = $full_spectre_decomposed[$i]["resolution"];
        $fullSpectreArr[$subspectre_name]["min_freq"] = $full_spectre_decomposed[$i]["min_freq"];
        $fullSpectreArr[$subspectre_name]["max_freq"] = $full_spectre_decomposed[$i]["max_freq"];

        $subspectreID++;
      }
    }

    //var_dump($fullSpectreArr);
    return $fullSpectreArr;
  }

  public static function getAllSubspectres($deveui, $date_time_first_measure)
  {
    $db = static::getDB();

    $sql_subspectres_data = "
    SELECT r.id as record_id, sensor.deveui, st.id AS structure_id, st.nom as structure_name,
        st.transmision_line_name as transmission_name,
        s.nom as site_name, r.date_time AS date_time,
    `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution`
    FROM `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN sensor on sensor.id = r.sensor_id
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE r.date_time >= :date_time
    AND sensor.deveui = :deveui
    ORDER BY r.date_time ASC
    LIMIT 5";

    $stmt = $db->prepare($sql_subspectres_data);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':date_time', $date_time_first_measure, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $results;
  }

  public static function getActivityData($deveui, $time_period = -1)
  {

    $all_full_spectres_recorded = SpectreManager::reconstituteAllSpectreForSensorFirstGeneration($deveui);

    $dataArr = array();
    foreach ($all_full_spectres_recorded as $fullSpectreCurrent) {
      //var_dump($fullSpectreCurrent);
      //exit();
      $date_time = $fullSpectreCurrent["date_time"];
      $deveui = $fullSpectreCurrent["deveui"];
      $site_name = $fullSpectreCurrent["site_name"];
      $structure_name = $fullSpectreCurrent["structure_name"];
      $transmission_name = $fullSpectreCurrent["transmission_name"];

      $tmpArr = array(
        "date" => $date_time,
        "deveui" => $deveui,
        "structure_name" => $structure_name,
        "site_name" => $site_name,
        "transmission_name" => $transmission_name,
      );
      $count  = 0;
      for ($i = 1; $i < 6; $i++) {
        $subspectreName = "subspectre_" . $i;

        if (array_key_exists($subspectreName, $fullSpectreCurrent)) {

          $subspectreData = $fullSpectreCurrent[$subspectreName];

          $subspectreDataValuesHex = $subspectreData["data"];
          $resolution = $subspectreData["resolution"];
          $min_freq = $subspectreData["min_freq"];
          $max_freq = $subspectreData["max_freq"];

          $axisX_freq = $min_freq;

          //Loop over the subspectre data values (hex format)
          for ($j = 2; $j < intval(strlen(strval($subspectreDataValuesHex))); $j += 2) {
            //We need to analyse two by two
            $data_amplitude_j_hex = substr($subspectreDataValuesHex, $j, 2);
            //Convert hexa value to decimal
            $data_amplitude_j_dec = Utilities::hex2dec($data_amplitude_j_hex, $signed = false);
            //From the decimal value, compute the power of the amplitude
            $axisY_amplitude = Utilities::accumulatedTable32($data_amplitude_j_dec);

            $x = "x" . $count;
            $y = "y" . $count;
            $tmpArr[$x] = $axisX_freq;
            $tmpArr[$y] = $axisY_amplitude;
            $axisX_freq = $axisX_freq + $resolution;
            $count += 1;
          }
        }
      }

      array_push($dataArr, $tmpArr);
    }

    return $dataArr;
  }

  /**
   * Get all the subspectre received on a specifc equipement from a specific site
   *
   * @param int $site_id
   * @param int $structure_id
   * @return array double array which all spectre recevied from a sensor and each array contain
   * array that contain the decomposed spectre
   */
  public function reconstituteAllSpectreFromSpecificEquipement($site_id, $structure_id)
  {
    //Retrieve the sensor associated to the site_id and equipement_id
    $sensor_id = SensorManager::getSensorIdFromEquipementAndSiteId($site_id, $structure_id);
    $fullSpectreArr = $this->reconstituteAllSpectreForSensorFirstGeneration($sensor_id);

    return $fullSpectreArr;
  }




  /**
   * Get all the first subspectre (001) received from a sensor
   * date | subspectre
   * @param string $deveui
   * @return array  results from the query
   */
  public static function getAllFirstSubspectreForSensorFirstGeneration($deveui, $dateTime = null)
  {
    $db = static::getDB();

    $sql_subspectre_data = "
          SELECT record_id, deveui, structure_name, transmission_name, site_name, structure_id, device_number, type_sensor, date_time, subspectre, resolution, subspectre_number FROM
      (SELECT
      sensor.device_number as device_number,
      sensor.deveui as deveui,
      sensor_type.name as type_sensor,
        s.nom AS site_name,
        st.id AS structure_id,
        st.nom AS structure_name,
        st.transmision_line_name as transmission_name,
        r.id as record_id,
        r.sensor_id,
        r.date_time as date_time,
        subspectre,
        subspectre_number,
        min_freq,
        max_freq,
        resolution
      FROM
        spectre AS sp
        LEFT JOIN record AS r ON (r.id = sp.record_id)
        JOIN sensor on sensor.id = r.sensor_id
      JOIN sensor_type ON sensor_type.id = sensor.type_id
        JOIN structure as st ON (st.id = r.structure_id)
        JOIN site as s ON (s.id = st.site_id)
      WHERE
        sp.subspectre_number = '001'
        AND sensor.deveui = :deveui
        AND Date(r.date_time) >= Date(sensor.installation_date) ";

    if (isset($dateTime)) {
      $sql_subspectre_data .= "AND r.date_time = :dateTime ";
    }

    $sql_subspectre_data .= ") AS first_subpsectre_sensor
      ORDER BY date_time DESC";

    $stmt = $db->prepare($sql_subspectre_data);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if (isset($dateTime)) {
      $stmt->bindValue(':dateTime', $dateTime, PDO::PARAM_STR);
    }

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Get all the first subspectre (000) received from a sensor
   * date | subspectre
   * @param string $deveui
   * @return array  results from the query
   */
  public static function getAllFirstSubspectreForSensorSecondGeneration($deveui, $dateTime = null)
  {
    $db = static::getDB();

    $sql_subspectre_data = "
          SELECT record_id, deveui, structure_name, transmission_name, site_name, structure_id, device_number, type_sensor, date_time, subspectre, resolution, subspectre_number FROM
      (SELECT
      sensor.device_number as device_number,
      sensor.deveui as deveui,
      sensor_type.name as type_sensor,
        s.nom AS site_name,
        st.id AS structure_id,
        st.nom AS structure_name,
        st.transmision_line_name as transmission_name,
        r.id as record_id,
        r.sensor_id,
        r.date_time as date_time,
        subspectre,
        subspectre_number,
        min_freq,
        max_freq,
        resolution
      FROM
        spectre AS sp
        LEFT JOIN record AS r ON (r.id = sp.record_id)
        JOIN sensor on sensor.id = r.sensor_id
      JOIN sensor_type ON sensor_type.id = sensor.type_id
        JOIN structure as st ON (st.id = r.structure_id)
        JOIN site as s ON (s.id = st.site_id)
      WHERE
        sp.subspectre_number = '000'
        AND sensor.deveui = :deveui
        AND Date(r.date_time) >= Date(sensor.installation_date) ";

    if (isset($dateTime)) {
      $sql_subspectre_data .= "AND r.date_time = :dateTime ";
    }

    $sql_subspectre_data .= ") AS first_subpsectre_sensor_second_generation
      ORDER BY date_time DESC";

    $stmt = $db->prepare($sql_subspectre_data);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if (isset($dateTime)) {
      $stmt->bindValue(':dateTime', $dateTime, PDO::PARAM_STR);
    }

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  public static function getFirstSubspectreForSensor($deveui, $date_time)
  {
    $db = static::getDB();

    $sql_subspectre_data = " SELECT  device_number,type_sensor, sensor_id, structure_id, date_time, subspectre, resolution, subspectre_number FROM
      (SELECT
      sensor.device_number AS device_number,
      s.nom AS site,
      st.id AS structure_id,
      st.nom AS equipement,
      r.id as record_id,
      r.sensor_id,
      r.date_time as date_time,
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
      sp.subspectre_number = '001'
      AND sensor.deveui= :deveui
      AND Date(r.date_time) >= Date(sensor.installation_date)
      AND r.date_time = :date_time
    ORDER BY
      r.date_time ASC) AS first_subpsectre_sensor";

    $stmt = $db->prepare($sql_subspectre_data);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  //TODO CHECK not workign well : two subspectre 001
  public static function getAllsubspectreForSensorId($deveui, $date_requested)
  {
    $db = static::getDB();

    $sql_subspectre_data = "
        SELECT s.nom, st.nom, r.sensor_id, r.date_time AS date_time,
        `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution` FROM `spectre` AS sp
        JOIN record AS r ON (r.id=sp.record_id)
        JOIN sensor on sensor.id = r.sensor_id
        JOIN structure as st ON (st.id=r.structure_id)
        JOIN site as s ON (s.id=st.site_id)
        WHERE sensor.deveui = :deveui AND r.date_time >= :date_requested
        ORDER BY r.date_time ASC
        LIMIT 5";

    $stmt = $db->prepare($sql_subspectre_data);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':date_requested', $date_requested, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $subspectresTMPArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $subspectresArr = array();
      $subspectreID = 0;
      foreach ($subspectresTMPArr as $subArr) {
        $subspectre_name = 'subspectre_' . $subspectreID;
        $subspectresArr[$subspectre_name] = $subArr;
        $subspectreID += 1;
      }
      return $subspectresArr;
    }
  }

  /**
   * Get specific subspectre received from a sensor given a date
   * sensor_id | date | subspectre | subspectre_number | min_freq | max_freq | resolution
   * @param int $snesor_id
   * @return array  results from the query
   */
  public static function getSpecificSubspectreForSensor($deveui, $date_request)
  {
    $db = static::getDB();

    $sql_query_get_spectre = "SELECT r.sensor_id, st.id AS structure_id, r.date_time AS date_d,
    `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution`
    FROM `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN sensor on sensor.id = r.sensor_id
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE CAST(r.date_time as DATE)  LIKE :date_request
    AND sensor.deveui = :deveui  ";

    $stmt = $db->prepare($sql_query_get_spectre);

    $stmt->bindValue(':date_request', $date_request, PDO::PARAM_STR);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {

      $all_spectre_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (empty($all_spectre_data)) {
        return 0;
      }
      return $all_spectre_data[0];
    }
  }

  /**
   * Insert spectre data to the DB given a json file
   *
   * @param json $spectre_data_json contain the spectre data (spectre_number, min_freq, max_freq, spectre_msg_hex,
   * resolution, date_time, deveui)
   * @return boolean  return True if insert query successfully executed
   */
  public static function insertSpectre($spectre)
  {

    $sql_data_record_subspectre = 'INSERT INTO  spectre (`record_id`, `subspectre`, `subspectre_number`, `min_freq`, `max_freq`, `resolution`)
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "spectre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui)) AS record_id,
      :subspectre AS subspectre, :subspectre_number AS subspectre_number, :min_freq AS min_freq,
      :max_freq AS max_freq, :resolution AS resolution) AS id_record
      WHERE NOT EXISTS (
      SELECT record_id FROM spectre WHERE record_id = (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "spectre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui))
      ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($sql_data_record_subspectre);

    $stmt->bindValue(':date_time', $spectre->dateTime, PDO::PARAM_STR);
    $stmt->bindValue(':deveui', $spectre->deveui, PDO::PARAM_STR);
    $stmt->bindValue(':subspectre', $spectre->spectre_msg_hex, PDO::PARAM_STR);
    $stmt->bindValue(':subspectre_number', $spectre->spectre_number, PDO::PARAM_STR);
    $stmt->bindValue(':min_freq', floatval($spectre->min_freq), PDO::PARAM_STR);
    $stmt->bindValue(':max_freq', floatval($spectre->max_freq), PDO::PARAM_STR);
    $stmt->bindValue(':resolution', floatval($spectre->resolution), PDO::PARAM_STR);

    $stmt->execute();

    $count = $stmt->rowCount();
    if ($count == '0') {
      echo "\n[Spectre] 0 spectre inserted.\n";
      return false;
    } else {
      echo "\n[Spectre] 1 spectre inserted.\n";
      return true;
    }
  }


  /**
   * Get all the spectre messages received from the sensors, for a specific group (RTE for example)
   *
   * @param string $group_name the name of the group we want to retrieve spectre data
   * @return array  results from the query
   */
  public function getAllSpectreData($group_name)
  {
    $db = static::getDB();

    $sql_spectre_data = "SELECT
    sensor.id,
    sensor.deveui,
    s.nom AS Site,
    st.nom AS Equipement,
    r.date_time,
    r.payload,
    r.msg_type AS 'Type message',
    subspectre,
    subspectre_number,
    min_freq,
    max_freq,
    resolution
    FROM
    spectre AS sp
    LEFT JOIN record AS r ON (r.id = sp.record_id)
    INNER JOIN structure AS st ON st.id = r.structure_id
    INNER JOIN site AS s ON s.id = st.site_id
    INNER JOIN sensor ON (sensor.id = r.sensor_id)
    INNER JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
    INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
    gn.name = :group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)

    ";

    $stmt = $db->prepare($sql_spectre_data);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }
}
