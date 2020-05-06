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
use App\Models\API\SensorAPI;
use PDO;

class SensorManager extends \Core\Model
{

  /**
   * Get the position (horizontal or vertical) of a specific sensor
   *
   * @param string $deveui
   * @return string results of the query
   *  VERTICAL or HORIZONTAL
   */
  public static function getPositionInstallation($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT position FROM `sensor`
      WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $positionInstallation = $stmt->fetch(PDO::FETCH_COLUMN);
      return $positionInstallation;
    }
  }

  /**
   * Update the status of a specific sensor
   *
   * @param string $deveui
   * @param string $status (ACTIVE / INACTIVE / JOINED / ERROR / WARNING)
   * @return boolean true if update successfully
   * 
   */
  public static function updateStatut($deveui, $status)
  {
    $db = static::getDB();

    $sql = "UPDATE sensor
          SET status = :status
          WHERE sensor.deveui = :deveui";;

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);

    if ($stmt->execute()) {
      return True;
    }
    return False;
  }


  /**
   * Get all the groups that have a specfic sensor
   *
   * @param string $deveui
   * @return array contains the groups
   * 
   */
  public static function getGroupsOwner($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT group_name.group_id FROM group_name
    LEFT JOIN sensor_group ON (sensor_group.groupe_id = group_name.group_id)
    LEFT JOIN sensor ON (sensor.id = sensor_group.sensor_id)
    WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $ownerIdArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $ownerIdArr;
    }
  }

  /**
   * Get the current group for a specfic sensor. Suppose that there is only one group of type USER for a sensor
   *
   * @param string $deveui
   * @return string group
   * 
   */
  public static function getGroupOwnerCurrentUser($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT DISTINCT group_name.group_id FROM group_name
    LEFT JOIN sensor_group ON (sensor_group.groupe_id = group_name.group_id)
    LEFT JOIN group_roles ON (group_name.group_role = group_roles.id)
    LEFT JOIN sensor ON (sensor.id = sensor_group.sensor_id)
    WHERE sensor.deveui = :deveui AND group_roles.name= 'User' ";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $ownerId = $stmt->fetch(PDO::FETCH_COLUMN);
      return $ownerId;
    }
  }

  /**
   * Get the date of the last message received for a specific sensor
   *
   * @param string $deveui
   * @return string results of the query
   *  date
   */
  public static function getLastDataReceivedData($deveui)
  {
    $db = static::getDB();
    $sql_last_date = "SELECT DATE_FORMAT(MAX(Date(date_time)), '%d/%m/%Y') as dateMaxReceived 
    FROM record as r
    LEFT JOIN sensor ON sensor.id = r.sensor_id
    WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql_last_date);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $last_date = $stmt->fetch(PDO::FETCH_COLUMN);
      return $last_date;
    }
  }


  /**
   * Get sensor deveui on a specific structure
   *
   * @param int $structure_id structure id to get the sensor id
   * @return array  info from sensors
   */
  public static function getAllSensorsInfoFromSite($siteId, $groupId)
  {
    $db = static::getDB();

    $sql_sensor_id = "SELECT DISTINCT *, attr_transmission_line.name AS transmission_line_name, site.nom AS site_name, st.nom AS structure_name FROM sensor
    LEFT JOIN structure as st ON st.id= sensor.structure_id
    LEFT JOIN attr_transmission_line ON attr_transmission_line.id = st.attr_transmission_id
    LEFT JOIN site ON site.id = st.site_id
    INNER JOIN sensor_group ON sensor_group.sensor_id = sensor.id
    LEFT JOIN group_name ON group_name.group_id= sensor_group.groupe_id
    WHERE site.id = :siteId AND group_name.group_id = :groupId";

    $stmt = $db->prepare($sql_sensor_id);
    $stmt->bindValue(':siteId', $siteId, PDO::PARAM_INT);
    $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $sensorsInfoArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $sensorsInfoArr;
    }
  }

  public static function getSensorInfo($deveui)
  {
    $db = static::getDB();

    $sql_sensor_id = "SELECT DISTINCT *, attr_transmission_line.name AS transmission_line_name, 
    st.id AS structure_id, site.nom AS site_name, st.nom AS structure_name FROM sensor
    LEFT JOIN structure as st ON st.id= sensor.structure_id
    LEFT JOIN attr_transmission_line ON attr_transmission_line.id = st.attr_transmission_id
    LEFT JOIN site ON site.id = st.site_id
    WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql_sensor_id);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $sensorsInfo = $stmt->fetch(PDO::FETCH_ASSOC);
      return $sensorsInfo;
    }
  }

  /**
   * Get the site where the sensor is installed
   *
   * @param string $deveui
   * @return string site
   *  
   */
  public static function getSiteWhereIsInstalled($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT DISTINCT site.nom FROM site
    LEFT join structure ON (structure.site_id = site.id)
    LEFT JOIN record ON (record.structure_id = structure.id)
    LEFT JOIN sensor ON (sensor.id = record.sensor_id)
    WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $site = $stmt->fetch(PDO::FETCH_COLUMN);
      return $site;
    }
  }

  /**
   * Get the structure where the sensor is installed
   *
   * @param string $deveui
   * @return string structure
   *  
   */
  public static function getStructureWhereIsInstalled($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT DISTINCT structure.nom, structure.latitude, structure.longitude,
    attr_transmission_line.name FROM structure
    LEFT JOIN sensor ON (sensor.structure_id = structure.id)
    LEFT JOIN attr_transmission_line ON attr_transmission_line.id=structure.attr_transmission_id
    WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $site = $stmt->fetch(PDO::FETCH_ASSOC);
      return $site;
    }
  }



  /**
   * Get the device number of a device given his id
   *
   * @param string $deveui deveui of the sensor
   * @return string device number of the sensor
   *
   */
  public static function getDeviceNumberFromDeveui($deveui)
  {
    $db = static::getDB();

    $sql_deviceNb_sensor = "SELECT device_number FROM `sensor`
      WHERE deveui = :deveui ";

    $stmt = $db->prepare($sql_deviceNb_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $device_number = $stmt->fetch(PDO::FETCH_COLUMN);
      return $device_number;
    }
  }


  /**
   * Get last message received from a specific sensor
   *
   * @param string $deveui
   * @return string last date
   *
   */
  public static function getLastMessageReceivedFromDeveui($deveui)
  {
    $db = static::getDB();

    $sql_last_date_received = "SELECT DATE_FORMAT(MAX(r.date_time), '%d/%m/%Y %H:%i:%s')
    as lastDateReceived FROM sensor AS s
    INNER JOIN record AS r ON (s.id = r.sensor_id)
    AND s.deveui LIKE :deveui
    GROUP BY s.device_number
    ";

    $stmt = $db->prepare($sql_last_date_received);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $last_date = $stmt->fetchAll(PDO::FETCH_COLUMN);
      if (!(empty($last_date))) {
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
  public static function isInstalled($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT installation_date FROM sensor
    WHERE deveui = :deveui";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $installation_date = $stmt->fetch(PDO::FETCH_COLUMN);
      if (empty($installation_date)) {
        return False;
      }
      return True;
    }
  }

  /**
   * Return the image path of the sensor if exist
   *
   * @param string $deveui
   * @return string path of the image
   *
   */
  public static function getPathImage($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT img_file FROM sensor
    WHERE deveui = :deveui";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $img_path = $stmt->fetch(PDO::FETCH_COLUMN);
      if (empty($img_path)) {
        return "";
      }
      return $img_path;
    }
  }

  /**
   * check while profile is the sensor 
   *
   * @param string $deveui
   * @return int 2 if version firmware 2.0 or 1 if version firmware 1.45
   *
   */
  public static function checkProfileGenerationSensor($deveui)
  {
    $db = static::getDB();

    $sql = "SELECT sensor_type.firmware_version FROM sensor_type
    LEFT JOIN sensor ON sensor.type_id = sensor_type.id
    WHERE sensor.deveui = :deveui ";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $version = $stmt->fetch(PDO::FETCH_COLUMN);
      if ($version >= 2) {
        return 2;
      }
      return 1;
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

    $sql_battery_sensor = "SELECT  g.battery_level
    FROM record AS r
    LEFT JOIN global AS g ON (g.record_id = r.id)
    LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
    WHERE s.deveui LIKE :deveui
    AND r.msg_type LIKE 'global'
    ORDER BY `r`.`date_time` DESC LIMIT 1";

    $stmt = $db->prepare($sql_battery_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $batteryInfo = $stmt->fetch(PDO::FETCH_COLUMN);
      if (empty($batteryInfo)) {
        $batteryInfo = 100;
      }
      return $batteryInfo;
    }
  }

  /**
   * get the first and last activity of a specific sensor
   *
   * @param string $deveui
   * @return array with first_activity and last_activity
   *
   */
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
  public static function getRecordsFromDeveui($deveui, $limit = 30)
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
          ORDER BY r.date_time DESC
          LIMIT :limit";

    $stmt = $db->prepare($sql_record_sensor);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

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
   * Get basic info of sensors given a group 
   *
   * @param string $group_name the name of the group
   * @return array results of the query
   * id_device | groupe | device_number | ligneHT | equipement | last_message_received | status |date_installation
   *
   */
  public static function getBriefInfoForGroup($groupId)
  {
    $db = static::getDB();

    $sql_brief_info = "SELECT
        sensor.id AS 'sensor_id',
        sensor.device_number AS 'device_number',
        sensor.deveui AS deveui,
        sensor.status AS status,
        sensor.installation_date AS date_installation,
        gn.name AS groupe,
        st.transmision_line_name AS `ligneHT`,
        st.nom AS `equipement`,
        s.nom AS 'site'
      FROM
        sensor
        LEFT JOIN structure AS st ON st.id = sensor.structure_id
        LEFT JOIN site AS s ON s.id = st.site_id
        LEFT JOIN sensor_group AS gs ON (gs.sensor_id = sensor.id)
        LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
      WHERE
        gn.group_id = :groupId";

    $stmt = $db->prepare($sql_brief_info);
    $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $resultsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultsArr;
    }
  }


  /**
   * Get the status of a specific sensor 
   *
   * @param deveui $deveui of the sensor
   * @return string  current status of the sensor
   */
  public static function getStatusDevice($deveui)
  {
    $db = static::getDB();

    $sql_status_device = "SELECT sensor.status FROM sensor
      WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql_status_device);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $status = $stmt->fetch(PDO::FETCH_COLUMN);
      return $status;
    }
  }


  /**
   * Get the number of inactif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   */
  public static function getNumberActiveSensor($groupId)
  {
    $db = static::getDB();

    $sql_nb_actif_sensor = "SELECT
      COUNT(*) AS nb_active
    FROM
      sensor AS s
      LEFT JOIN sensor_group AS gs ON (gs.sensor_id = s.id)
      LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
      gn.group_id = :groupId
      AND s.status LIKE 'active'";

    $stmt = $db->prepare($sql_nb_actif_sensor);
    $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $nb_actif_sensor = $stmt->fetch(PDO::FETCH_COLUMN);
      return $nb_actif_sensor;
    }
  }

  /**
   * Get the number of inactif sensor for a specific group
   *
   * @param string $group_name the group we want to check the number of actif sensor
   * @return array  array results
   */
  public static function getNumberInactiveSensor($groupId)
  {
    $db = static::getDB();

    $sql_nb_actif_sensor = "SELECT
      COUNT(*) AS nb_active
    FROM
      sensor AS s
      LEFT JOIN sensor_group AS gs ON (gs.sensor_id = s.id)
      LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    WHERE
      gn.group_id = :groupId
      AND s.status LIKE 'inactive'";

    $stmt = $db->prepare($sql_nb_actif_sensor);
    $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $nb_actif_sensor = $stmt->fetch(PDO::FETCH_COLUMN);
      return $nb_actif_sensor;
    }
  }
}
