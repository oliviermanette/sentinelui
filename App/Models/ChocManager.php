<?php

namespace App\Models;
use App\Utilities;
use PDO;

class ChocManager extends \Core\Model
{

  public function __construst(){

  }

  /**
  * Get all the choc messages received from the sensors, for a specific group (RTE for example)
  *
  * @param string $group_name the name of the group we want to retrieve choc data
  * @return array  results from the query
  */
  public function getAllChocData($group_name){
    $db = static::getDB();

    $sql_choc_data ="SELECT
    sensor.id,
    sensor.deveui,
    s.nom AS Site,
    st.nom AS Equipement,
    r.date_time,
    r.payload,
    r.msg_type AS 'Type message',
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
    INNER JOIN structure AS st ON st.id = r.structure_id
    INNER JOIN site AS s ON s.id = st.site_id
    INNER JOIN sensor ON (sensor.id = r.sensor_id)
    INNER JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
    INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
    gn.name = : group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)
    ";

    $stmt = $db->prepare($sql_choc_data);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
  * Get all the choc messages received from the sensors given a specific sensor id
  *
  * @param int $sensor_id sensor id for which we want to retrieve the choc data
  * @return array  results from the query
  */
  public function getAllChocDataByIdSensor($sensor_id){
    $sql_choc_data = "SELECT
    `sensor_id`,
    DATE(`date_time`) AS date_d,
    `amplitude_1`,
    `amplitude_2`,
    `time_1`,
    `time_2`,
    `freq_1`,
    `freq_2`,
    `power`
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    WHERE
    `msg_type` LIKE 'choc'
    AND `sensor_id` LIKE :sensor_id
    ORDER BY
    date_d ASC ";

    $stmt = $db->prepare($sql_choc_data);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
  * Insert choc data to the DB given a json file
  *
  * @param json $choc_data_json contain the choc data (amplitude1, amplitude2, time1, time2, date_time, deveui)
  * @return boolean  return True if insert query successfully executed
  */
  public function insertChocData($choc_data_json){
    $amplitude_1 = floatval($choc_data_json['amplitude1']);
    $amplitude_2 = floatval($choc_data_json['amplitude2']);
    $time_1 = floatval($choc_data_json['time1']);
    $time_2 = floatval($choc_data_json['time2']);

    $amplitude_g_1 = Utilities::mgToG($amplitude_1);
    $amplitude_g_2 = Utilities::mgToG($amplitude_2);
    $time_s_1 = Utilities::microToSecond($time_1);
    $time_s_2 = Utilities::microToSecond($time_2);

    $date_time = $choc_data_json['date_time'];
    $deveui_sensor = $choc_data_json['deveui'];

    $resData = ChocManager::computeChocData($amplitude_g_1, $amplitude_g_2, $time_s_1, $time_s_2 );

    $totalAreaPower = $resData[0];
    $freq1 = $resData[1];
    $freq2 = $resData[2];

    $sql_data_record_choc = 'INSERT INTO  choc (`record_id`, `amplitude_1`,  `amplitude_2`, `time_1`, `time_2`,  `freq_1`,`freq_2`, `power`)
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "choc"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui LIKE :deveui)),
      :amplitude1, :amplitude2, :time1, :time2, :frequence1, :frequence2, :power) AS id_record';

      $db = static::getDB();
      $stmt = $db->prepare($sql_data_record_choc);

      $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);
      $stmt->bindValue(':deveui', $deveui_sensor, PDO::PARAM_STR);
      $stmt->bindValue(':amplitude1', $amplitude_g_1, PDO::PARAM_STR);
      $stmt->bindValue(':amplitude2', $amplitude_g_2, PDO::PARAM_STR);
      $stmt->bindValue(':time1', $time_s_1, PDO::PARAM_STR);
      $stmt->bindValue(':time2', $time_s_2, PDO::PARAM_STR);
      $stmt->bindValue(':frequence1', $freq1, PDO::PARAM_STR);
      $stmt->bindValue(':frequence2', $freq2, PDO::PARAM_STR);
      $stmt->bindValue(':power', $totalAreaPower, PDO::PARAM_STR);

      return $stmt->execute();
  }

  /**
  * Computer Power of the choc
  *
  * @param float $amplitude_1 first amplitude (in mg or g)
  * @param float $amplitude_2 second amplitude (in mg or g)
  * @param int $time_1 first time (or period) to provok the first amplitude (in micro seconde or second)
  * @param int $time_2 second time to provok the second amplitude (in micro seconde or second)
  * @return array  $totalAreaPower, $freq1, $freq2
  */
  public static function computeChocData($amplitude_1, $amplitude_2, $time_1, $time_2 ){
    $pt0 = array(0, 0);
    $pt1 = array($time_1, $amplitude_1);
    $pt2 = array($time_2, $amplitude_2);
    $pt3 = array($time_2 + ($time_2 - $time_1), 0);

    #1. Compute line equation (pt1, pt2)
    $res = Utilities::computeLineEquation($pt1, $pt2);
    $slope = $res[0];
    $b = $res[1];

    #2. compute (pt1) : ax + b = 0 to find the new point on the abscille (Xc, Yc)
    $Xc = Utilities::findXItersection($slope, $b, 0);
    $ptC = array($Xc, 0);

    $distanceP1P2 = Utilities::computeDistance($pt1, $pt2);

    #3 Compute distance (pt1,ptc)
    $distanceP1PC = Utilities::computeDistance($pt1, $ptC);

    #4 compute the first area of the first triangle
    $areaTriangle1 = Utilities::computeAreaTrianglePt($pt0, $pt1, $ptC);
    $areaTriangle2 = Utilities::computeAreaTrianglePt($ptC, $pt2, $pt3);

    #5 Compute power choc by combining the two triangle
    $totalAreaPower = Utilities::computePowerArea($areaTriangle1, $areaTriangle2);

    #Compute frequence of each phase of the choc
    $freq1 = 1 / $pt1[0];
    $freq2 = 1/ $pt2[0];

    $data_res = array($totalAreaPower, $freq1, $freq2);

    return $data_res;

  }
}
