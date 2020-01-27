<?php

namespace App\Models;
use PDO;

/*
SpectreManager.php
Handle the spectre data CRUD on the database
author : Lirone Samoun

*/
class SpectreManager extends \Core\Model
{


  /**
   * Get all the subspectre received by a sensor and reconstitue the whole spectre received every week
   *
   * @param int $sensor_id 
   * @return array double array which all spectre recevied from a sensor and each array contain
   * array that contain the decomposed spectre
   */
  public function reconstituteAllSpectreForSensor($sensor_id){
    $fullSpectreArr = array();

    $resultArr = SpectreManager::getAllFirstSubspectreForSensorID($sensor_id);

    $spectreID = 1;
    foreach ($resultArr as $firstSubSpectreArr){
      $spectre_name = 'spectre_' . $spectreID;

      $record_id = $firstSubSpectreArr["record_id"];
      $date_time = $firstSubSpectreArr["date_time"];
      //The sensor record a spectre then for 5 days, it send the subspectre.
      //So to know exactly when the spectre has been recorded, need to take
      //the date before the first subspectre
      $date_yesterday = date('Y-m-d G:H:s', strtotime("$date_time . -24 hours"));

      $subspectre001 = $firstSubSpectreArr["subspectre"];

      $fullSpectreArr[$spectre_name]["record_id"] = $record_id;
      $fullSpectreArr[$spectre_name]["date_time"] = $date_yesterday;
      $fullSpectreArr[$spectre_name]["sensor_id"] = $firstSubSpectreArr["sensor_id"];
      $fullSpectreArr[$spectre_name]["structure_id"] = $firstSubSpectreArr["structure_id"];

      $fullSpectreArr[$spectre_name]["subspectre_0"]["data"] = $subspectre001;
      $fullSpectreArr[$spectre_name]["subspectre_0"]["resolution"] = 1;
      $fullSpectreArr[$spectre_name]["subspectre_0"]["min_freq"] = 20;
      $fullSpectreArr[$spectre_name]["subspectre_0"]["max_freq"] = 69;

      //to have a full spectre, there are 5 subspectre in total. We already got the firt one 001
      $subspectreID= 1;
      for ($i = 0; $i < 4; $i++){
        $subspectre_name = 'subspectre_' . $subspectreID;
        $date_time = date('Y-m-d', strtotime($date_time . "+1 days"));
        
        $subspectreArr = SpectreManager::getSubspectreForSensorID($sensor_id, $date_time);

        //There is a result found
        if (is_array($subspectreArr)){
          $subspectreNumber = $subspectreArr["subspectre_number"];
          $fullSpectreArr[$spectre_name][$subspectre_name]["data"] = $subspectreArr["subspectre"];
          $fullSpectreArr[$spectre_name][$subspectre_name]["resolution"] = $subspectreArr["resolution"];
          $fullSpectreArr[$spectre_name][$subspectre_name]["min_freq"] = $subspectreArr["min_freq"];
          $fullSpectreArr[$spectre_name][$subspectre_name]["max_freq"] = $subspectreArr["max_freq"];

          $subspectreID++;
        }
        
      }

      $spectreID++;
      
    }
    return $fullSpectreArr;
  }

  /**
   * Get all the subspectre received on a specifc equipement from a specific site
   *
   * @param int $site_id
   * @param int $structure_id 
   * @return array double array which all spectre recevied from a sensor and each array contain
   * array that contain the decomposed spectre
   */
  public function reconstituteAllSpectreFromSpecificEquipement($site_id, $structure_id){
    //Retrieve the sensor associated to the site_id and equipement_id
    $sensor_id = SensorManager::getSensorIdFromEquipementAndSiteId($site_id, $structure_id);
    $fullSpectreArr = $this->reconstituteAllSpectreForSensor($sensor_id);

    return $fullSpectreArr;
  }

  /**
   * Get all the first subspectre (001) received from a sensor
   * date | subspectre
   * @param int $snesor_id
   * @return array  results from the query
   */
  public static function getAllFirstSubspectreForSensorID($sensor_id){
    $db = static::getDB();

    $sql_subspectre_data = "
        SELECT record_id, sensor_id, structure_id, date_time, subspectre FROM
    (SELECT 
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
      sp.subspectre_number LIKE '001' 
      AND r.sensor_id LIKE :sensor_id
      AND Date(r.date_time) >= Date(sensor.installation_date) 
    ORDER BY 
      r.date_time ASC) AS first_subpsectre_sensor";

    $stmt = $db->prepare($sql_subspectre_data);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }


  }


  /**
   * Get specific subspectre received from a sensor given a date
   * sensor_id | date | subspectre | subspectre_number | min_freq | max_freq | resolution
   * @param int $snesor_id
   * @return array  results from the query
   */
  public static function getSubspectreForSensorID($sensor_id, $date_request){
    $db = static::getDB();

    $sql_query_get_spectre = "SELECT r.sensor_id, st.id AS structure_id, r.date_time AS date_d,
    `subspectre`,`subspectre_number`,`min_freq`,`max_freq`,`resolution`
    FROM `spectre` AS sp
    JOIN record AS r ON (r.id=sp.record_id)
    JOIN structure as st ON (st.id=r.structure_id)
    JOIN site as s ON (s.id=st.site_id)
    WHERE CAST(r.date_time as DATE)  LIKE :date_request AND r.sensor_id = :sensor_id  ";

    $stmt = $db->prepare($sql_query_get_spectre);

    $stmt->bindValue(':date_request', $date_request, PDO::PARAM_STR);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {

      $all_spectre_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (empty($all_spectre_data)) //or  if(!$results)  or  if(count($results)==0)  or if($results == array())
      {
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
  public function insertSpectreData($spectre_data_json){
    $spectre_number = $spectre_data_json['spectre_number'];
    $minFreq = floatval($spectre_data_json['min_freq']);
    $maxFreq = floatval($spectre_data_json['max_freq']);
    $spectre_msg_hex = $spectre_data_json['spectre_msg_hex'];
    $resolution = floatval($spectre_data_json['resolution']);
    $date_time = $spectre_data_json['date_time'];
    $deveui_sensor = $spectre_data_json['deveui'];

    $sql_data_record_subspectre = 'INSERT INTO  spectre (`record_id`, `subspectre`, `subspectre_number`, `min_freq`, `max_freq`, `resolution`)
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "spectre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui LIKE :deveui)),
      :subspectre, :subspectre_number, :min_freq, :max_freq, :resolution) AS id_record';

      $db = static::getDB();
      $stmt = $db->prepare($sql_data_record_subspectre);

      $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);
      $stmt->bindValue(':deveui', $deveui_sensor, PDO::PARAM_STR);
      $stmt->bindValue(':subspectre', $spectre_msg_hex, PDO::PARAM_STR);
      $stmt->bindValue(':subspectre_number', $spectre_number, PDO::PARAM_STR);
      $stmt->bindValue(':min_freq', $minFreq, PDO::PARAM_STR);
      $stmt->bindValue(':max_freq', $maxFreq, PDO::PARAM_STR);
      $stmt->bindValue(':resolution', $resolution, PDO::PARAM_STR);

      return $stmt->execute();

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
