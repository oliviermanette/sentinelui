<?php

namespace App\Models;
use PDO;

class SpectreManager extends \Core\Model
{

  public function __construst(){

  }

  /**
  * Get all the spectre messages received from the sensors, for a specific group (RTE for example)
  *
  * @param string $group_name the name of the group we want to retrieve spectre data
  * @return array  results from the query
  */
  public function getAllSpectreData($group_name){
    $db = static::getDB();

    $sql_spectre_data ="SELECT
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
    gn.name = : group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)

    ";

    $stmt = $db->prepare($sql_spectre_data);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  public function getAllSubspectrespectreRecordById($sensor_id, $date_request){
    $db = static::getDB();

    $sql_query_get_spectre = "SELECT s.nom, st.nom, r.sensor_id, r.payload, r.date_time AS date_d,
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

      return $all_spectre_data;
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

}
