<?php

namespace App\Models;

use App\Utilities;
use PDO;

class BatteryManager extends \Core\Model
{

  public function __construst()
  {
  }
  /**
   * Get all the battery data messages received from the sensors, for a specific group (RTE for example)
   *
   * @param string $group_name the name of the group we want to retrieve battery data
   * @return array  results from the query
   */
  public function getAllBatteryData($group_name)
  {
    $db = static::getDB();

    $sql_battery_data = "SELECT
    sensor.id,
    sensor.deveui,
    s.nom AS Site,
    st.nom AS Equipement,
    r.date_time,
    r.payload,
    r.msg_type AS 'Type message',
    battery_level
    FROM
    global AS gl
    LEFT JOIN record AS r ON (r.id = gl.record_id)
    INNER JOIN structure AS st ON st.id = r.structure_id
    INNER JOIN site AS s ON s.id = st.site_id
    INNER JOIN sensor ON (sensor.id = r.sensor_id)
    INNER JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
    INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
    gn.name = : group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)

    ";

    $stmt = $db->prepare($sql_battery_data);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Insert battery data to the database
   * @param json $battery_data_json json array which contain the data to insert
   * @return array 
   */
  public static function insertBattery($battery)
  {

    $sql_data_record_battery = 'INSERT INTO  global (`record_id`, `battery_level`)
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "global"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui LIKE :deveui)) AS record_id,
      :battery AS battery) AS id_record
      WHERE NOT EXISTS (
      SELECT record_id FROM global WHERE record_id = (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "global"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui LIKE :deveui))
      ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($sql_data_record_battery);

    $stmt->bindValue(':date_time', $battery->dateTime, PDO::PARAM_STR);
    $stmt->bindValue(':deveui', $battery->deveui, PDO::PARAM_STR);
    $stmt->bindValue(':battery', floatval($battery->batteryLevel), PDO::PARAM_STR);

    $stmt->execute();

    $count = $stmt->rowCount();
    if ($count == '0') {
      echo "\n0 battery were affected\n";
      return false;
    } else {
      echo "\n 1 battery data was affected.\n";
      return true;
    }
  }
}
