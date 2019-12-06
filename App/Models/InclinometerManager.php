<?php

namespace App\Models;
use PDO;
use App\Utilities;

class InclinometerManager extends \Core\Model
{

  public function __construst(){

  }

  /**
  * Get all the inclinometer messages received from the sensors, for a specific group (RTE for example)
  *
  * @param string $group_name the name of the group we want to retrieve inclinometer data
  * @return array  results from the query
  */
  public function getAllInclinometerData($group_name){
    $db = static::getDB();

    $sql_inclinometer_data ="SELECT
    sensor.id,
    sensor.deveui,
    s.nom AS Site,
    st.nom AS Equipement,
    r.date_time,
    r.payload,
    r.msg_type AS 'Type message',
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
    INNER JOIN structure AS st ON st.id = r.structure_id
    INNER JOIN site AS s ON s.id = st.site_id
    INNER JOIN sensor ON (sensor.id = r.sensor_id)
    INNER JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
    INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
    gn.name = : group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)
    ";

    $stmt = $db->prepare($sql_inclinometer_data);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
  * Get all the inclinometer messages received from the sensors given a specific sensor id
  *
  * @param int $sensor_id sensor id for which we want to retrieve the inclinometer data
  * @return array  results from the query
  */
  public function getAllInclinometerDataByIdSensor($sensor_id){
    $sql_all_inclinometer = "SELECT
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
    ORDER BY
    date_d ASC
    ";

    $stmt = $db->prepare($sql_all_inclinometer);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
  * Get the latest inclinometer record received from a given sensor id
  *
  * @param int $sensor_id sensor id for which we want to retrieve the last inclinometer
  * @return array  results from the query
  */
  public function getLatestTemperatureRecordByIdSensor($sensor_id){

    $db = static::getDB();

    $sql = "SELECT
    `temperature`
    FROM
    inclinometer AS inc
    LEFT JOIN record AS r ON (r.id = inc.record_id)
    WHERE
    `msg_type` LIKE 'inclinometre'
    AND `sensor_id` LIKE :sensor_id";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $last_temp = $stmt->fetch(PDO::FETCH_COLUMN);

      return $last_temp;
    }
  }

  /**
  * Get all the temperature messages received from the sensors given a specific sensor id
  *
  * @param int $sensor_id sensor id for which we want to retrieve the temperature data
  * @param string $date if we want to retrieve the data for specific date format Y-M-D
  * @return array  results from the query
  */
  public function getAllTemperatureRecordsByIdSensor($sensor_id, $date = null){
    $db = static::getDB();

    $sql = "SELECT `temperature`, DATE(`date_time`) AS date_d FROM `record`
    WHERE `msg_type` LIKE 'inclinometre' AND `sensor_id` LIKE :sensor_id ";

    if (!empty($date)){
      $sql .="AND Date(`date_time`) = :dateD ";
    }

    $sql .=" ORDER BY date_d ASC";

    $stmt = $db->prepare($sql);
    if (!empty($date)){
      $stmt->bindValue(':dateD', $date, PDO::PARAM_STR);
    }

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $all_temp = $stmt->fetchAll();
      return $all_temp;
    }
  }

    /**
    * Insert inclinometer data to the DB given a json file
    *
    * @param json $inclinometer_data_json contain the inclinometer data (temperature, x, y, z, date_time, deveui)
    * @return boolean  return True if insert query successfully executed
    */
  public function insertInclinometerData($inclinometer_data_json){
    $temperature = $inclinometer_data_json['temperature'];
    $nx = $inclinometer_data_json['X'];
    $ny = $inclinometer_data_json['Y'];
    $nz = $inclinometer_data_json['Z'];
    $date_time = $inclinometer_data_json['date_time'];
    $deveui_sensor = $inclinometer_data_json['deveui'];

    $xData_g = Utilities::mgToG($nx);
    $yData_g = Utilities::mgToG($ny);
    $zData_g = Utilities::mgToG($nz);

    if ($zData_g < - 1){
      $zData_g = -1;
    }
    if ($zData_g > 1){
      $zData_g = 1;
    }
    if ($yData_g < - 1){
      $yData_g = -1;
    }
    if ($yData_g > 1){
      $yData_g = 1;
    }
    if ($xData_g < - 1){
      $xData_g = -1;
    }
    if ($xData_g > 1){
      $xData_g = 1;
    }

    $angleX = rad2deg(asin($xData_g));
    $angleY = rad2deg(asin($yData_g));
    $angleZ = rad2deg(acos($zData_g));

    $sql_data_record_inclinometer = 'INSERT INTO  inclinometer (`record_id`, `nx`, `ny`, `nz`, `angle_x`, `angle_y`, `angle_z`, `temperature`)
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "inclinometre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui LIKE :deveui)),
      :nx, :ny, :nz, :angle_x, :angle_y, :angle_z, :temperature) AS id_record';

      $db = static::getDB();
      $stmt = $db->prepare($sql_data_record_inclinometer);

      $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);
      $stmt->bindValue(':deveui', $deveui_sensor, PDO::PARAM_STR);
      $stmt->bindValue(':nx', $nx, PDO::PARAM_STR);
      $stmt->bindValue(':ny', $ny, PDO::PARAM_STR);
      $stmt->bindValue(':nz', $nz, PDO::PARAM_STR);
      $stmt->bindValue(':angle_x', $angleX, PDO::PARAM_STR);
      $stmt->bindValue(':angle_y', $angleY, PDO::PARAM_STR);
      $stmt->bindValue(':angle_z', $angleZ, PDO::PARAM_STR);
      $stmt->bindValue(':temperature', $temperature, PDO::PARAM_STR);

      return $stmt->execute();

  }


}
