<?php

namespace App\Models;

use PDO;
use App\Utilities;

/*
InclinometerManager.php
Handle the inclinometer data CRUD on the database
author : Lirone Samoun

*/

class InclinometerManager extends \Core\Model
{


  /**
   * Check if the inclinaison received by the sensor
   * is inside a specific range ( 1SD, 2SD , 3SD) to trigger an alert
   * @param int $sensor_id sensor id to target
   * @param int $time_period check for the last X days
   * @return true if an alert is triggered
   */
  public function check($inclinometer, $group, $method = "RANGE")
  {

    $inclinometerTreshSTD = SettingManager::getInclinometerThresh($group);
    $timePeriodCheck = SettingManager::getTimePeriodCheck($group);
    $inclinometerRangeThresh = SettingManager::getInclinometerRangeThresh($group);

    $alertBoolArr = array("alertOnX" => false, "alertOnY" => false, "alertOnZ" => false);
    if (isset($inclinometer->angleX) && isset($inclinometer->angleY) && isset($inclinometer->angleZ)) {
      //echo "let's check for ". $inclinometer->deveui;

      //Etape 1 : calculer la moyenne et l'ecart type depuis la date choisie
      $avgInclinaisonArr = InclinometerManager::computeAvgInclinaisonForLast($inclinometer->deveui, $timePeriodCheck);
      $avgAngleX = $avgInclinaisonArr["averageAngleX"];
      $avgAngleY = $avgInclinaisonArr["averageAngleY"];
      $avgAngleZ = $avgInclinaisonArr["averageAngleZ"];
      $stdDevInclinaisonArr = InclinometerManager::computeStdDevInclinaisonForLast($inclinometer->deveui, $timePeriodCheck);
      $stdDevAngleX = $stdDevInclinaisonArr["stdDevAngleX"];
      $stdDevAngleY = $stdDevInclinaisonArr["stdDevAngleY"];
      $stdDevAngleZ = $stdDevInclinaisonArr["stdDevAngleZ"];

      if ($method == "RANGE") {
        $references_values = InclinometerManager::getValuesReference($inclinometer->deveui, -1);

        $date_ref = $references_values["date"];
        $angleX_ref = $references_values["angle_x"];
        $angleY_ref = $references_values["angle_y"];
        $angleZ_ref = $references_values["angle_z"];

        $angleX = $inclinometer->angleX;
        $angleY = $inclinometer->angleY;
        $angleZ = $inclinometer->angleZ;

        $diffAngleX = $angleX - $angleX_ref;
        $diffAngleY = $angleY - $angleY_ref;
        $diffAngleZ = $angleZ - $angleZ_ref;

        if ($diffAngleX > $inclinometerRangeThresh || $diffAngleX < -$inclinometerRangeThresh) {
          $alertBoolArr["alertOnX"] = true;
        }
        if ($diffAngleY > $inclinometerRangeThresh || $diffAngleY < -$inclinometerRangeThresh) {
          $alertBoolArr["alertOnY"] = true;
        }
        if ($diffAngleZ > $inclinometerRangeThresh || $diffAngleZ < -$inclinometerRangeThresh) {
          $alertBoolArr["alertOnZ"] = true;
        }
      } else if ($method == "STD") {
        switch ($inclinometerTreshSTD) {
          case 1:
            $highTreshX = $avgAngleX + $stdDevAngleX;
            $lowThreshX = $avgAngleX - $stdDevAngleX;
            $highTreshY = $avgAngleY + $stdDevAngleY;
            $lowThreshY = $avgAngleY - $stdDevAngleY;
            $highTreshZ = $avgAngleZ + $stdDevAngleZ;
            $lowThreshZ = $avgAngleZ - $stdDevAngleZ;
            break;
          case 2:
            $highTreshX = $avgAngleX + 2 * $stdDevAngleX;
            $lowThreshX = $avgAngleX - 2 * $stdDevAngleX;
            $highTreshY = $avgAngleY + 2 * $stdDevAngleY;
            $lowThreshY = $avgAngleY - 2 * $stdDevAngleY;
            $highTreshZ = $avgAngleZ + 2 * $stdDevAngleZ;
            $lowThreshZ = $avgAngleZ - 2 * $stdDevAngleZ;
            break;
          case 3:
            $highTreshX = $avgAngleX + 3 * $stdDevAngleX;
            $lowThreshX = $avgAngleX - 3 * $stdDevAngleX;
            $highTreshY = $avgAngleY + 3 * $stdDevAngleY;
            $lowThreshY = $avgAngleY - 3 * $stdDevAngleY;
            $highTreshZ = $avgAngleZ + 3 * $stdDevAngleZ;
            $lowThreshZ = $avgAngleZ - 3 * $stdDevAngleZ;
            break;
          default:
            $highTreshX = $avgAngleX + $stdDevAngleX;
            $lowThreshX = $avgAngleX - $stdDevAngleX;
            $highTreshY = $avgAngleY + $stdDevAngleY;
            $lowThreshY = $avgAngleY - $stdDevAngleY;
            $highTreshZ = $avgAngleZ + $stdDevAngleZ;
            $lowThreshZ = $avgAngleZ - $stdDevAngleZ;
        }
        //Etape 3 : verifier si la variation est supérieur à la moyenne + ecart type
        if ($inclinometer->angleX > $highTreshX || $inclinometer->angleX < $lowThreshX) {
          echo "ALERT ON INCLINAISON X ! \n";
          echo "Current Inclinaison X " . $inclinometer->angleX . " is outside " . $lowThreshX . " and " . $highTreshX . " ! ";
          $alertBoolArr["alertOnX"] = true;
        }
        if ($inclinometer->angleY > $highTreshY || $inclinometer->angleY < $lowThreshY) {
          echo "ALERT ON INCLINAISON Y ! \n";
          echo "Current Inclinaison Y " . $inclinometer->angleY . " is outside " . $lowThreshY . " and " . $highTreshY . " ! ";
          $alertBoolArr["alertOnY"] = true;
        }
        if ($inclinometer->angleZ > $highTreshZ || $inclinometer->angleZ < $lowThreshZ) {
          echo "ALERT ON INCLINAISON Z ! \n";
          echo "Current Inclinaison Z " . $inclinometer->angleZ . " is outside " . $lowThreshZ . " and " . $highTreshZ . " ! ";
          $alertBoolArr["alertOnZ"] = true;
        }
      }

      return $alertBoolArr;
    }
  }

  /**
   * Compute average of inclinaison (X, Y, Z) received from today to a specific date in term of days
   *
   * @param int $sensor_id sensor id for which we want to compute the average inclinaison data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago. Defaut = 1 i.e since the installation date
   * @return array  results from the query (avgInclinaisonArr with contain the average value for
   * angleX, angleY and angleZ)
   */
  public static function computeAvgInclinaisonForLast($deveui, $time_period = -1)
  {
    $db = static::getDB();
    $sql_avg = "SELECT
      sensor_id,
      AVG(angle_x) AS average_angle_x,
      AVG(angle_y) AS average_angle_y,
      AVG(angle_z) AS average_angle_z
      FROM
        (
        SELECT
          `sensor_id`,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
          angle_x, angle_y, angle_z
        FROM
          inclinometer AS inc
          LEFT JOIN record AS r ON (r.id = inc.record_id)
          LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
        WHERE
          `msg_type` LIKE 'inclinometre'
          AND s.deveui LIKE :deveui ";

    if ($time_period != -1) {
      $sql_avg .= " AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    } else {
      $sql_avg .= "  AND Date(r.date_time) > s.installation_date ";
    }
    $sql_avg .= "
          ORDER BY
            `date_d` DESC) AS inclinaison
          GROUP BY
            sensor_id
          ";

    $stmt = $db->prepare($sql_avg);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      if (isset($results)) {
        $average_angle_x = $results["average_angle_x"];
        $average_angle_y = $results["average_angle_y"];
        $average_angle_z = $results["average_angle_z"];
        $avgInclinaisonArr = array(
          "averageAngleX" => $average_angle_x,
          "averageAngleY" => $average_angle_y,
          "averageAngleZ" => $average_angle_z
        );

        return $avgInclinaisonArr;
      } else {
        return null;
      }
    }
  }


  /**
   * Compute the standard deviation of inclinaison (X, Y, Z) received from today to a specific date in term of days
   *
   * @param int $sensor_id sensor id for which we want to compute the std dev inclinaison data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago. Defaut = 1 i.e since the installation date
   * @return array  results from the query (stdDevInclinaisonArr with contain the std dev value for
   * angleX, angleY and angleZ)
   */
  public static function computeStdDevInclinaisonForLast($deveui, $time_period = -1)
  {
    $db = static::getDB();

    $sql_stdDev = "SELECT
      sensor_id,
      STDDEV(angle_x) AS stdDev_angle_x,
      STDDEV(angle_y) AS stdDev_angle_y,
      STDDEV(angle_z) AS stdDev_angle_z
      FROM
        (
        SELECT
          `sensor_id`,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
          angle_x, angle_y, angle_z
        FROM
          inclinometer AS inc
          LEFT JOIN record AS r ON (r.id = inc.record_id)
          LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
        WHERE
          `msg_type` LIKE 'inclinometre'
          AND s.deveui LIKE :deveui ";

    if ($time_period != -1) {
      $sql_stdDev .= " AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    } else {
      $sql_stdDev .= "  AND Date(r.date_time) > s.installation_date ";
    }

    $sql_stdDev .= "ORDER BY
            `date_d` DESC
          ) AS inclinaison_data
          GROUP BY
          sensor_id
      ";

    $stmt = $db->prepare($sql_stdDev);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      if (isset($results)) {
        $stdDev_angle_x = $results["stdDev_angle_x"];
        $stdDev_angle_y = $results["stdDev_angle_y"];
        $stdDev_angle_z = $results["stdDev_angle_z"];
        $stdDevInclinaisonArr = array(
          "stdDevAngleX" => $stdDev_angle_x,
          "stdDevAngleY" => $stdDev_angle_y,
          "stdDevAngleZ" => $stdDev_angle_z
        );

        return $stdDevInclinaisonArr;
      } else {
        return null;
      }
    }
  }
  /**
   * Get all the inclinometer messages received from the sensors, for a specific group (RTE for example)
   * sensor_id | deveui |site | equipement | date_time | payload | type message |
   * nx |ny |nz | angle_x | angle_y |angle_z | temperature
   *
   * @param string $group_name the name of the group we want to retrieve inclinometer data
   * @return array  results from the query
   */
  public function getAllInclinometerDataForGroup($group_name)
  {
    $db = static::getDB();

    $sql_inclinometer_data = "SELECT
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
   *  sensor_id | date_time | nx |ny |nz | angle_x | angle_y |angle_z | temperature
   *
   * @param int $sensor_id sensor id for which we want to retrieve the inclinometer data
   * @return array  results from the query
   */
  public function getAllInclinometerDataForSensor($sensor_id)
  {

    $db = static::getDB();

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
   * Get all the angles X Y Z received from the sensors given a specific sensor id
   *  sensor_id | date_time | angle_x | angle_y |angle_z | temperature
   *
   * @param int $sensor_id sensor id for which we want to retrieve the inclinometer data
   * @return array  results from the query
   */
  public static function getAngleXYZPerDayForSensor($deveui, $startDate = NULL, $endDate = NULL)
  {

    $db = static::getDB();

    $sql_angleXYZ_data = "SELECT
    `sensor_id`,
    DATE(`date_time`) AS date_d,
    angle_x,
    angle_y,
    angle_z,
    temperature
    FROM
    inclinometer AS inc
    LEFT JOIN record AS r ON (r.id = inc.record_id)
    LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
    WHERE
    `msg_type` LIKE 'inclinometre'
    AND deveui = :deveui
    AND Date(r.date_time) >= Date(s.installation_date) ";

    if (!empty($startDate) && !empty($endDate)) {
      $sql_angleXYZ_data .= " AND Date(r.date_time) BETWEEN :startDate AND :endDate ";
    }

    $sql_angleXYZ_data .= "ORDER BY date_d ASC";


    $stmt = $db->prepare($sql_angleXYZ_data);

    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if (!empty($startDate) && !empty($endDate)) {
      $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = $stmt->rowCount();
    if ($count == '0') {
      return array();
    } else {
      return $results;
    }
  }



  /**
   * Get the latest temperature record received from a given sensor id
   *
   * @param int $sensor_id sensor id for which we want to retrieve the last inclinometer
   * @return array  results from the query
   */
  public static function getLatestTemperatureForSensor($sensor_id)
  {

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



  public static function convertInclinometerDataToAngle($nx, $ny, $nz)
  {
    $xData_g = Utilities::mgToG($nx);
    $yData_g = Utilities::mgToG($ny);
    $zData_g = Utilities::mgToG($nz);

    if ($zData_g < -1) {
      $zData_g = -1;
    }
    if ($zData_g > 1) {
      $zData_g = 1;
    }
    if ($yData_g < -1) {
      $yData_g = -1;
    }
    if ($yData_g > 1) {
      $yData_g = 1;
    }
    if ($xData_g < -1) {
      $xData_g = -1;
    }
    if ($xData_g > 1) {
      $xData_g = 1;
    }

    $angleX = rad2deg(asin($xData_g));
    $angleY = rad2deg(asin($yData_g));
    $angleZ = rad2deg(acos($zData_g));

    $angleArr = array("angleX" => $angleX, "angleY" => $angleY, "angleZ" => $angleZ);

    return $angleArr;
  }

  /**
   * Insert inclinometer data to the DB given a json file
   *
   * @param json $inclinometer_data_json contain the inclinometer data (temperature, x, y, z, date_time, deveui)
   * @return boolean  return True if insert query successfully executed
   */
  public static function insertInclinometer($inclinometer)
  {

    $sql_data_record_inclinometer = 'INSERT INTO  inclinometer (`record_id`, `nx`, `ny`, `nz`, `angle_x`, `angle_y`, `angle_z`, `temperature`, `battery_left` )
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "inclinometre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui)) as record_id,
      :nx AS nx, :ny AS ny, :nz AS nz, :angle_x AS angleX, :angle_y AS angleY, :angle_z AS angleZ, :temperature AS temperature, :battery_left AS battery) AS id_record
      WHERE NOT EXISTS (
      SELECT record_id FROM inclinometer WHERE record_id = (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "inclinometre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui))
    ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($sql_data_record_inclinometer);

    $stmt->bindValue(':date_time', $inclinometer->dateTime, PDO::PARAM_STR);
    $stmt->bindValue(':deveui', $inclinometer->deveui, PDO::PARAM_STR);
    $stmt->bindValue(':nx', $inclinometer->X, PDO::PARAM_STR);
    $stmt->bindValue(':ny', $inclinometer->Y, PDO::PARAM_STR);
    $stmt->bindValue(':nz', $inclinometer->Z, PDO::PARAM_STR);
    $stmt->bindValue(':angle_x', $inclinometer->angleX, PDO::PARAM_STR);
    $stmt->bindValue(':angle_y', $inclinometer->angleY, PDO::PARAM_STR);
    $stmt->bindValue(':angle_z', $inclinometer->angleZ, PDO::PARAM_STR);
    $stmt->bindValue(':temperature', $inclinometer->temperature, PDO::PARAM_STR);
    $battery_left = null;
    if (property_exists($inclinometer, 'battery_left')) {
      $battery_left = $inclinometer->battery_left;
    }
    $stmt->bindValue(':battery_left', $battery_left, PDO::PARAM_STR);
    $stmt->execute();

    $count = $stmt->rowCount();
    if ($count == '0') {
      echo "\n[Inclinometre] No inclinometer inserted.\n";
      return false;
    } else {
      echo "\n[Inclinometre] Inclinometer inserted.\n";
      return true;
    }
  }

  /**
   * Insert battery data to the database for sentinel 2.0
   * @param json $battery_data_json json array which contain the data to insert
   * @return array
   */
  public static function insertBattery($inclinometer)
  {

    $sql_data_record_battery = 'INSERT INTO  global (`record_id`, `battery_level`)
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "inclinometre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui)) AS record_id,
      :battery AS battery) AS id_record
      WHERE NOT EXISTS (
      SELECT record_id FROM global WHERE record_id = (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "inclinometre"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui))
      ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($sql_data_record_battery);

    $stmt->bindValue(':date_time', $inclinometer->dateTime, PDO::PARAM_STR);
    $stmt->bindValue(':deveui', $inclinometer->deveui, PDO::PARAM_STR);
    $stmt->bindValue(':battery', floatval($inclinometer->battery_left), PDO::PARAM_STR);

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

  /**
   * Compute variation (%) of inclinometer data from today to a specific date in term of days
   *
   * @param int $sensor_id sensor id for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago . Defaut $time_period = - 1 i.e we take
   * values since the first record (installation date )
   * @return array  results from the query
   * sensor_id |deveui |
   * newAngleX | oldAngleX | pourcentage_variation_anglex | newAngleY | oldAngleY | pourcentage_variation_angleY |
   * newAngleZ | oldAngleZ | pourcentage_variation_angleZ | newTemp | oldTemp | pourcentage_variation_temp |
   */
  public static function computePercentageVariationAngleValueForLast($deveui, $percentage = true, $time_period = -1, $precision = 2)
  {
    $db = static::getDB();

    if ($percentage) {
      $sql_variation_angle = "SELECT first_date, last_date, new_values_inclinometer.deveui,
      ROUND(newAngleX,:precision) AS newAngleX, ROUND(oldAngleX,:precision) AS oldAngleX,
      IFNULL(ROUND((sum(ABS(newAngleX - oldAngleX))/newAngleX)*100, :precision),0) AS pourcentage_variation_angleX,
      ROUND(newAngleY,:precision) AS newAngleY, ROUND(oldAngleY,:precision) AS oldAngleY,
      ROUND((sum(ABS(newAngleY - oldAngleY))/newAngleY)*100,:precision) as pourcentage_variation_angleY,
      ROUND(newAngleZ,:precision) AS newAngleZ, ROUND(oldAngleZ,:precision) AS oldAngleZ,
      ROUND((sum(ABS(newAngleZ - oldAngleZ))/newAngleZ)*100,:precision) as pourcentage_variation_angleZ,
      newTemp,oldTemp,
      ROUND((sum(ABS(newTemp - oldTemp))/newTemp)*100,1) as variation_temperature ";
    } else {
      $sql_variation_angle = "SELECT first_date, last_date, new_values_inclinometer.deveui,
      ROUND(newAngleX,:precision) AS newAngleX, ROUND(oldAngleX,:precision) AS oldAngleX,
      ROUND(sum(newAngleX - oldAngleX), :precision) AS pourcentage_variation_angleX,
      ROUND(newAngleY,:precision) AS newAngleY, ROUND(oldAngleY,:precision) AS oldAngleY,
      ROUND(sum(newAngleY - oldAngleY), :precision) AS pourcentage_variation_angleY,
      ROUND(newAngleZ,:precision) AS newAngleZ, ROUND(oldAngleZ,:precision) AS oldAngleZ,
      ROUND(sum(newAngleZ - oldAngleZ), :precision) AS pourcentage_variation_angleZ,
      newTemp,oldTemp,
      sum(newTemp - oldTemp)  as variation_temperature ";
    }

    $sql_variation_angle .= "
        FROM
        (SELECT
            `sensor_id`, deveui,
            DATE_FORMAT(r.date_time, '%d/%m/%Y') AS last_date,
            angle_x AS newAngleX,
            angle_y AS newAngleY,
            angle_z AS newAngleZ,
            temperature AS newTemp
            FROM
            inclinometer AS inc
            LEFT JOIN record AS r ON (r.id = inc.record_id)
          LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
            WHERE
            `msg_type` LIKE 'inclinometre'
            AND deveui = :deveui
          ORDER BY last_date DESC
        LIMIT 1) AS new_values_inclinometer
        JOIN
        (SELECT
            `sensor_id`,deveui,
            DATE_FORMAT(r.date_time, '%d/%m/%Y') AS first_date,
            angle_x AS oldAngleX,
            angle_y AS oldAngleY,
            angle_z AS oldAngleZ,
            temperature AS oldTemp
            FROM
            inclinometer AS inc
            LEFT JOIN record AS r ON (r.id = inc.record_id)
            LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
            WHERE
            `msg_type` LIKE 'inclinometre'
            AND deveui = :deveui ";

    if ($time_period != -1) {
      $sql_variation_angle .= " AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    } else {
      $sql_variation_angle .= "  AND Date(r.date_time) > s.installation_date ";
    }
    $sql_variation_angle .= "
              ORDER BY
              first_date ASC
              LIMIT 1) AS last_values_period_inclinometer
          ON new_values_inclinometer.sensor_id = last_values_period_inclinometer.sensor_id
          GROUP BY last_date,first_date,oldTemp,newTemp, newAngleZ,oldAngleZ,newAngleY,oldAngleY, newAngleX,oldAngleX
          ";

    $stmt = $db->prepare($sql_variation_angle);
    $stmt->bindValue(':precision', $precision, PDO::PARAM_INT);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':time_period', $time_period, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $resultsArr = $stmt->fetch(PDO::FETCH_ASSOC);

      return $resultsArr;
    }
  }

  /**
   * Get the references values meaning the first data recorded since the sensor installation
   * (angleX, angleY, angeZ, temperature)
   *
   * @param string $deveui sensor deveui for which we want to compute the variation data
   * @param int $time_period the last X days for computing the reference values. Ex : $time_period = 30,
   * get the references values 30 days from now. defaults to -1 meaning that we take
   * all records in account so the first record will correspond to installation date
   * @return array which contain the reference values (angleX, angleY, angleZ, temperature)
   */
  public static function getValuesReference($deveui, $time_period = -1)
  {
    $db = static::getDB();
    $sql_reference_values = "SELECT
        DATE_FORMAT(r.date_time, '%d/%m/%Y %H:%i:%s') AS date,
        angle_x,
        angle_y,
        angle_z,
        temperature
        FROM
        inclinometer AS inc
        LEFT JOIN record AS r ON (r.id = inc.record_id)
        LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
        WHERE
        `msg_type` LIKE 'inclinometre'
        AND Date(r.date_time) >= Date(s.installation_date) + INTERVAL 1 DAY
        AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql_reference_values .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }

    $sql_reference_values .= "ORDER BY r.date_time ASC LIMIT 1";

    $stmt = $db->prepare($sql_reference_values);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    $stmt->execute();
    $references_values = $stmt->fetch(PDO::FETCH_ASSOC);

    $db = null;

    return $references_values;
  }

  public static function computeDerivativeSpeedVariation($deveui, $time_period = -1)
  {
    //$variation = InclinometerManager::computeVariationPercentageAngleForLast($deveui, false, -1);
    $variation = InclinometerManager::computeAverageDailyVariationPercentageAngleForLast($deveui, false, $time_period);
    $equipment_height = EquipementManager::getEquipementHeightBySensorDeveui($deveui);
    $variationSpeedDerivateArr = array();
    $variationSpeedNormalArr = array();
    for ($i = 0; $i < count($variation); $i++) {
      $date = $variation[$i]["date"];
      $x_deg = $variation[$i]["variationAngleX"];
      $y_deg = $variation[$i]["variationAngleY"];
      $distanceXY_deg = sqrt(pow($x_deg, 2) + pow($y_deg, 2));

      $distanceXY_rad = (pi() / 180) * $distanceXY_deg;
      $delta_xy_cm = (tan($distanceXY_rad) * $equipment_height) * 100;

      if ($i == 0) {
        $delta_xy_cm_diff = 0;
      } else {
        $delta_xy_cm_diff = abs($delta_xy_cm - $variationSpeedNormalArr[$i - 1]["delta_xy_cm"]);
      }

      $tmpArr = array(
        "date" => $date, "delta_xy_cm" => $delta_xy_cm_diff
      );
      $tmpArr1 = array(
        "date" => $date, "delta_xy_cm" => $delta_xy_cm
      );
      array_push($variationSpeedDerivateArr, $tmpArr);
      array_push($variationSpeedNormalArr, $tmpArr1);
    }

    return $variationSpeedDerivateArr;
  }

  public static function computeDirectionVariationForLast($deveui, $time_period = -1)
  {
    //$percentageVariationDayArr = InclinometerManager::computeVariationPercentageAngleForLast($deveui, false, $time_period);
    $percentageVariationDayArr = InclinometerManager::computeAverageDailyVariationPercentageAngleForLast($deveui, false, $time_period);

    $equipment_height = EquipementManager::getEquipementHeightBySensorDeveui($deveui);
    $variationDirectionArr = array();

    foreach ($percentageVariationDayArr as $array) {
      $date = $array["date"];
      $x_deg = $array["variationAngleX"];
      $y_deg = $array["variationAngleY"];
      #echo "(", $x_deg . ", " . $y_deg . ")\n";
      $x_rad = (pi() / 180) * $x_deg;
      $y_rad = (pi() / 180) * $y_deg;

      $delta_x_cm = (tan($x_rad) * $equipment_height) * 100; //*100 for obtaining the result in cm
      $delta_y_cm = (tan($y_rad) * $equipment_height) * 100;


      $tmpArr = array(
        "date" => $date, "delta_x" => $delta_x_cm, "delta_y" => $delta_y_cm
      );
      array_push($variationDirectionArr, $tmpArr);
    }
    return $variationDirectionArr;
  }

  public static function getActivityData($deveui, $time_period = -1)
  {
    $db = static::getDB();

    $references_values = InclinometerManager::getValuesReference($deveui, $time_period);
    $equipment_height = EquipementManager::getEquipementHeightBySensorDeveui($deveui);

    $date_ref = $references_values["date"];
    $angleX_ref = $references_values["angle_x"];
    $angleY_ref = $references_values["angle_y"];
    $angleZ_ref = $references_values["angle_z"];
    $temperature_ref = $references_values["temperature"];

    $sql_all_values = "SELECT
        r.date_time AS date,
        s.device_number,
        st.nom as structure_name,
        st.transmision_line_name as transmission_name,
        site.nom as site_name,
        angle_x,
        angle_y,
        angle_z,
        temperature
        FROM
        inclinometer AS inc
        LEFT JOIN record AS r ON (r.id = inc.record_id)
        LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
        LEFT JOIN structure AS st ON (st.id = s.structure_id)
        LEFT JOIN site AS site ON (site.id = st.site_id)
        WHERE
        `msg_type` LIKE 'inclinometre'
        AND Date(r.date_time) >= Date(s.installation_date)
        AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql_all_values .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }

    $sql_all_values .= "ORDER BY r.date_time ASC";

    $stmt = $db->prepare($sql_all_values);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    $stmt->execute();
    $all_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $nbResults = count($all_values);

    $dataArr = array();
    $count = 0;
    $variationSpeedNormalArr = array();
    foreach ($all_values as $values) {

      $date = $values["date"];
      $device_number = $values["device_number"];
      $structure_name = $values["structure_name"];
      $transmission_name = $values["transmission_name"];
      $site_name = $values["site_name"];
      $angleX = $values["angle_x"];
      $angleY = $values["angle_y"];
      $temperature = $values["temperature"];

      //Variation in degrees since the first day of installation
      $variationAngleX_deg = (($angleX - $angleX_ref) / $angleX_ref);
      $variationAngleY_deg = (($angleY - $angleY_ref) / $angleY_ref);
      $variationAngleTemperature =  (($temperature - $temperature_ref) / $temperature_ref);
      //Variation in pourcentage since the first day of installation
      $variationAngleX_pourcent = $variationAngleX_deg * 100;
      $variationAngleY_pourcent =  $variationAngleY_deg * 100;
      $variationTemperature_pourcent = $variationAngleTemperature * 100;
      //Variation in rad since the first day of installation
      $variationAngleX_rad = (pi() / 180) * $variationAngleX_deg;
      $variationAngleY_rad = (pi() / 180) * $variationAngleY_deg;
      //Variation X and Y in cm since  the first day of installation
      $deltaInclinaisonX_cm = (tan($variationAngleX_rad) * $equipment_height) * 100; //*100 for obtaining the result in cm
      $deltaInclinaisonY_cm = (tan($variationAngleY_rad) * $equipment_height) * 100;
      //Distance XY in deg and radian  since  the first day of installation
      $distanceXY_deg = sqrt(pow($variationAngleX_deg, 2) + pow($variationAngleY_deg, 2));
      $distanceXY_rad = (pi() / 180) * $distanceXY_deg;
      //Inclination XY in cm  since  the first day of installation
      $deltaInclinaisonXY_cm = (tan($distanceXY_rad) * $equipment_height) * 100;
      //Put all the data in an array
      $tmpArr = array(
        "date" => $date,
        "device_number" => $device_number,
        "structure_name" => $structure_name,
        "transmission_name" => $transmission_name,
        "site_name" => $site_name,
        "variationAngleX_deg" => $variationAngleX_deg,
        "variationAngleY_deg" => $variationAngleY_deg,
        "variationAngleTemperature" => $variationAngleTemperature,
        "variationAngleX_installation_pourcent" => $variationAngleX_pourcent,
        "variationAngleY_installation_pourcent" => $variationAngleY_pourcent,
        "variationTemperature_installation_pourcent" => $variationTemperature_pourcent,
        "deltaInclinaisonX_cm" => $deltaInclinaisonX_cm,
        "deltaInclinaisonY_cm" => $deltaInclinaisonY_cm,
        "deltaInclinaisonXY_cm" => $deltaInclinaisonXY_cm,
      );

      //Compute the speed derivative (value inclination XY today - value inclination XY yesterday)
      if ($count == 0) {
        $deltaInclinaisonXY_cm_diff = 0;
      } else {
        $deltaInclinaisonXY_cm_diff = abs($deltaInclinaisonXY_cm - $variationSpeedNormalArr[$count - 1]["deltaInclinaisonXY_cm"]);
      }

      $tmpLightArr = array(
        "date" => $date, "deltaInclinaisonXY_cm" => $deltaInclinaisonXY_cm_diff
      );
      $tmpLightArr1 = array(
        "date" => $date, "deltaInclinaisonXY_cm" => $deltaInclinaisonXY_cm
      );
      array_push($variationSpeedNormalArr, $tmpLightArr1);
      //Put this data in the array
      $tmpArr["deltaInclinaisonXY_cm_diff_previous"] = $deltaInclinaisonXY_cm_diff;
      //Push this tmp array into the global array
      array_push($dataArr, $tmpArr);

      $count += 1;
    }

    $db = null;
    return $dataArr;
  }

  /**
   * Compute daily variation of ALL inclinometer data from today until the last X days. To compute the
   * variation, we first get the reference value of measurement. We assume that this is the first record
   * from the first day of instalation of the sensor
   *
   * @param string $deveui sensor deveui for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago. defaults to -1 meaning that we take
   * all records in account
   * @return array which contain daily variation (date, variationAngleX, variationAngleY, variationAngleZ, variationTemperature)
   */
  public static function computeVariationPercentageAngleForLast($deveui, $percentage = false, $time_period = -1)
  {
    $db = static::getDB();

    $references_values = InclinometerManager::getValuesReference($deveui, $time_period);

    $date_ref = $references_values["date"];
    $angleX_ref = $references_values["angle_x"];
    $angleY_ref = $references_values["angle_y"];
    $angleZ_ref = $references_values["angle_z"];
    $temperature_ref = $references_values["temperature"];

    $sql_all_values = "SELECT
        DATE_FORMAT(r.date_time, '%d/%m/%Y %H:%i:%s') AS date,
        angle_x,
        angle_y,
        angle_z,
        temperature
        FROM
        inclinometer AS inc
        LEFT JOIN record AS r ON (r.id = inc.record_id)
        LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
        WHERE
        `msg_type` LIKE 'inclinometre'
        AND Date(r.date_time) >= Date(s.installation_date)
        AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql_all_values .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }

    $sql_all_values .= "ORDER BY r.date_time ASC";

    $stmt = $db->prepare($sql_all_values);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    $stmt->execute();
    $all_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $nbResults = count($all_values);

    $variationsArr = array();
    //echo "NBRE RESULTS : ".$nbResults."\n";
    //print_r($all_values);
    foreach ($all_values as $values) {

      $date = $values["date"];
      $angleX = $values["angle_x"];
      $angleY = $values["angle_y"];
      $angleZ = $values["angle_z"];
      if ($percentage) {
        $temperature = $values["temperature"];
        $variationAngleX = (($angleX - $angleX_ref) / $angleX_ref) * 100;
        $variationAngleY = (($angleY - $angleY_ref) / $angleY_ref) * 100;
        $variationAngleZ = (($angleZ - $angleZ_ref) / $angleZ_ref) * 100;
        $variationTemperature = (($temperature - $temperature_ref) / $temperature_ref) * 100;
      } else {
        $temperature = $values["temperature"];
        $variationAngleX = $angleX - $angleX_ref;
        $variationAngleY = $angleY - $angleY_ref;
        $variationAngleZ = $angleZ - $angleZ_ref;
        $variationTemperature = $temperature - $temperature_ref;
      }

      $tmpArr = array(
        "date" => $date, "variationAngleX" => $variationAngleX, "variationAngleY" => $variationAngleY,
        "variationAngleZ" => $variationAngleZ, "variationTemperature" => $variationTemperature
      );
      array_push($variationsArr, $tmpArr);
      //echo "\n Date : ".$date." | Angle Referent X : ".$angleX_ref ." |Angle courant X : ".$angleX." | Variation X :". $variationAngleX . "<br/>\n";
      //echo "\n Angle Referent Y : " . $angleY_ref . " |Angle courant Y : " . $angleY . " | Variation Y :" . $variationAngleY . "<br/>\n";
    }

    //print_r($variationsArr);
    $db = null;
    return $variationsArr;
  }

  /**
   * Compute absolute average daily variation of inclinometer data from today until the last X days. To compute the
   * variation, we first get the reference value of measurement. We assume that this is the first record
   * from the first day of instalation of the sensor
   *
   * @param string $deveui sensor deveui for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago. defaults to -1 meaning that we take
   * all records in account
   * @return array which contain daily variation (date, variationAngleX, variationAngleY, variationAngleZ, variationTemperature)
   */
  public static function computeAverageDailyVariationPercentageAngleForLast($deveui, $percentage = false, $time_period = -1)
  {
    $db = static::getDB();

    $references_values = InclinometerManager::getValuesReference($deveui, $time_period);

    $date_ref = $references_values["date"];
    $angleX_ref = $references_values["angle_x"];
    $angleY_ref = $references_values["angle_y"];
    $angleZ_ref = $references_values["angle_z"];
    $temperature_ref = $references_values["temperature"];

    $sql_all_values = "SELECT
        DATE(r.date_time) AS date,
        AVG(angle_x) AS average_angle_x,
        AVG(angle_y) AS average_angle_y,
        AVG(angle_z) AS average_angle_z,
        AVG(temperature) AS average_temperature
      FROM
        inclinometer AS inc
        LEFT JOIN record AS r ON (r.id = inc.record_id)
        LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
      WHERE
        `msg_type` LIKE 'inclinometre'
        AND s.deveui = :deveui
        AND Date(r.date_time) > s.installation_date  ";

    if ($time_period != -1) {
      $sql_all_values .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }

    $sql_all_values .= "GROUP BY
            date
            ORDER BY
          `date` ASC";

    $stmt = $db->prepare($sql_all_values);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    $stmt->execute();
    $all_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $nbResults = count($all_values);

    $variationsArr = array();
    /*echo "NBRE RESULTS : " . $nbResults . "\n";
    print_r($all_values);*/
    foreach ($all_values as $values) {

      $date = $values["date"];
      $angleX = $values["average_angle_x"];
      $angleY = $values["average_angle_y"];
      $angleZ = $values["average_angle_z"];
      if ($percentage) {
        $temperature = $values["average_temperature"];
        $variationAngleX = (($angleX - $angleX_ref) / $angleX_ref) * 100;
        $variationAngleY = (($angleY - $angleY_ref) / $angleY_ref) * 100;
        $variationAngleZ = (($angleZ - $angleZ_ref) / $angleZ_ref) * 100;
        $variationTemperature = (($temperature - $temperature_ref) / $temperature_ref) * 100;
      } else {
        $temperature = $values["average_temperature"];
        $variationAngleX = $angleX - $angleX_ref;
        $variationAngleY = $angleY - $angleY_ref;
        $variationAngleZ = $angleZ - $angleZ_ref;
        $variationTemperature = $temperature - $temperature_ref;
      }

      $tmpArr = array(
        "date" => $date, "variationAngleX" => $variationAngleX, "variationAngleY" => $variationAngleY,
        "variationAngleZ" => $variationAngleZ, "variationTemperature" => $variationTemperature
      );
      array_push($variationsArr, $tmpArr);
    }

    //print_r($variationsArr);
    $db = null;
    return $variationsArr;
  }

  /**
   * Compute monthly variation of inclinometer data from today until the last X days. To compute the
   * variation, we first get the reference value of measurement. We assume that this is the first record
   * from the first day of instalation of the sensor
   *
   * @param string $deveui sensor deveui for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago. defaults to -1 meaning that we take
   * all records in account
   * @return array which contain monthly variation (date, variationAngleX, variationAngleY, variationAngleZ, variationTemperature)
   */
  public static function computeMonthlyVariationPercentageAngleForLast($deveui, $percentage = false, $time_period = -1)
  {
    $db = static::getDB();

    $references_values = InclinometerManager::getValuesReference($deveui, $time_period);

    $angleX_ref = $references_values["angle_x"];
    $angleY_ref = $references_values["angle_y"];
    $angleZ_ref = $references_values["angle_z"];
    $temperature_ref = $references_values["temperature"];

    $sql_monthly_values = "SELECT
          DATE_FORMAT(r.date_time, '%d/%m/%Y %H:%i:%s') AS date,
          angle_x,
          angle_y,
          angle_z,
          temperature
          FROM
          inclinometer AS inc
          LEFT JOIN record AS r ON (r.id = inc.record_id)
          INNER JOIN
        (SELECT
            MAX(r.date_time) AS max_date_time
            FROM
            inclinometer AS inc
            LEFT JOIN record AS r ON (r.id = inc.record_id)
            LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
            WHERE
            `msg_type` LIKE 'inclinometre'
            AND Date(r.date_time) >= Date(s.installation_date)
            AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql_monthly_values .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }

    $sql_monthly_values .= "GROUP BY MONTH(Date(r.date_time)),
            YEAR(Date(r.date_time))) AS t
            ON r.date_time = t.max_date_time
            order by r.date_time ASC
            ";


    $stmt = $db->prepare($sql_monthly_values);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    $stmt->execute();
    $all_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $nbResults = count($all_values);

    $variationsArr = array();
    //echo "NBRE RESULTS : " . $nbResults . "\n";
    //print_r($all_values);
    foreach ($all_values as $values) {

      $date = $values["date"];
      $angleX = $values["angle_x"];
      $angleY = $values["angle_y"];
      $angleZ = $values["angle_z"];
      if ($percentage) {
        $temperature = $values["temperature"];
        $variationAngleX = (($angleX - $angleX_ref) / $angleX_ref) * 100;
        $variationAngleY = (($angleY - $angleY_ref) / $angleY_ref) * 100;
        $variationAngleZ = (($angleZ - $angleZ_ref) / $angleZ_ref) * 100;
        $variationTemperature = (($temperature - $temperature_ref) / $temperature_ref) * 100;
      } else {
        $temperature = $values["temperature"];
        $variationAngleX = $angleX - $angleX_ref;
        $variationAngleY = $angleY - $angleY_ref;
        $variationAngleZ = $angleZ - $angleZ_ref;
        $variationTemperature = $temperature - $temperature_ref;
      }
      $tmpArr = array(
        "date" => $date,
        "variationAngleX" => $variationAngleX, "variationAngleY" => $variationAngleY,
        "variationAngleZ" => $variationAngleZ, "variationTemperature" => $variationTemperature
      );
      array_push($variationsArr, $tmpArr);
      //echo "\n Angle Referent X : ".$angleX_ref ." |Angle courant X : ".$angleX." | Variation X :". $variationAngleX . "<br/>\n";
      //echo "\n Angle Referent Y : " . $angleY_ref . " |Angle courant Y : " . $angleY . " | Variation Y :" . $variationAngleY . "<br/>\n";
    }

    //print_r($variationsArr);
    $db = null;
    return $variationsArr;
  }

  /**
   * Compute weekly variation of inclinometer data from today until the last X days.To compute the
   * variation, we first get the reference value of measurement. We assume that this is the first record
   * from the first day of instalation of the sensor
   *
   * @param string $deveui sensor deveui for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago. defaults to -1 meaning that we take
   * all records in account
   * @return array which contain weekly variation (date, variationAngleX, variationAngleY, variationAngleZ, variationTemperature)
   */
  public static function computeWeeklyVariationPercentageAngleForLast($deveui, $percentage = false, $time_period = -1)
  {
    $db = static::getDB();

    $references_values = InclinometerManager::getValuesReference($deveui, $time_period);

    $angleX_ref = $references_values["angle_x"];
    $angleY_ref = $references_values["angle_y"];
    $angleZ_ref = $references_values["angle_z"];
    $temperature_ref = $references_values["temperature"];

    $sql_weekly_values = "SELECT
          DATE_FORMAT(r.date_time, '%d/%m/%Y %H:%i:%s') AS date,
          angle_x,
          angle_y,
          angle_z,
          temperature
          FROM
          inclinometer AS inc
          LEFT JOIN record AS r ON (r.id = inc.record_id)
          INNER JOIN
        (SELECT
            MAX(r.date_time) AS max_date_time
            FROM
            inclinometer AS inc
            LEFT JOIN record AS r ON (r.id = inc.record_id)
            LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
            WHERE
            `msg_type` LIKE 'inclinometre'
            AND Date(r.date_time) > Date(s.installation_date)
            AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql_weekly_values .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }

    $sql_weekly_values .= "GROUP BY WEEK(Date(r.date_time)),
            MONTH(Date(r.date_time)),
            YEAR(Date(r.date_time))) AS t
            ON r.date_time = t.max_date_time
            order by r.date_time ASC
            ";


    $stmt = $db->prepare($sql_weekly_values);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    $stmt->execute();
    $all_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $nbResults = count($all_values);

    $variationsArr = array();
    //echo "NBRE RESULTS : " . $nbResults . "\n";
    //print_r($all_values);
    foreach ($all_values as $values) {

      $date = $values["date"];
      $angleX = $values["angle_x"];
      $angleY = $values["angle_y"];
      $angleZ = $values["angle_z"];
      if ($percentage) {
        $temperature = $values["temperature"];
        $variationAngleX = (($angleX - $angleX_ref) / $angleX_ref) * 100;
        $variationAngleY = (($angleY - $angleY_ref) / $angleY_ref) * 100;
        $variationAngleZ = (($angleZ - $angleZ_ref) / $angleZ_ref) * 100;
        $variationTemperature = (($temperature - $temperature_ref) / $temperature_ref) * 100;
      } else {
        $temperature = $values["temperature"];
        $variationAngleX = $angleX - $angleX_ref;
        $variationAngleY = $angleY - $angleY_ref;
        $variationAngleZ = $angleZ - $angleZ_ref;
        $variationTemperature = $temperature - $temperature_ref;
      }
      $tmpArr = array(
        "date" => $date,
        "variationAngleX" => $variationAngleX, "variationAngleY" => $variationAngleY,
        "variationAngleZ" => $variationAngleZ, "variationTemperature" => $variationTemperature
      );
      array_push($variationsArr, $tmpArr);
      //echo "\n Angle Referent X : " . $angleX_ref . " |Angle courant X : " . $angleX . " | Variation X :" . $variationAngleX . "<br/>\n";
      //echo "\n Angle Referent Y : " . $angleY_ref . " |Angle courant Y : " . $angleY . " | Variation Y :" . $variationAngleY . "<br/>\n";
    }

    //print_r($variationsArr);
    $db = null;
    return $variationsArr;
  }

  /**
   * Compute variation of inclinometer data from today to a specific date in term fo days
   *
   * @param int $sensor_id sensor id for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago
   * @return array  results from the query
   */
  public function computeVariationAngleValueForLast($sensor_id, $time_period)
  {
    $db = static::getDB();

    $sql_variation_angle = "select sum(ABS(new_values_inclinometer.angle_x - last_values_period_inclinometer.angle_x)) as variation_angleX,
    sum(ABS(new_values_inclinometer.angle_y - last_values_period_inclinometer.angle_y)) as variation_angleY,
    sum(ABS(new_values_inclinometer.angle_z - last_values_period_inclinometer.angle_z)) as variation_angleZ,
    sum(ABS(new_values_inclinometer.temperature - last_values_period_inclinometer.temperature)) as variation_temperature
    FROM
    (SELECT
        `sensor_id`,
        DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
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
      ORDER BY Date(r.date_time) DESC
    LIMIT 1) AS new_values_inclinometer
    JOIN
    (SELECT
        `sensor_id`,
        DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
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
        AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE()
        ORDER BY
        date_d ASC
        LIMIT 1) AS last_values_period_inclinometer
    ON new_values_inclinometer.sensor_id = last_values_period_inclinometer.sensor_id
        ";

    $stmt = $db->prepare($sql_variation_angle);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
    $stmt->bindValue(':time_period', $time_period, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $results;
    }
  }


  /**
   * Compute variation of inclinometer data for a specific range of date
   *
   * @param int $sensor_id sensor id for which we want to compute the variation data
   * @param str $start_date the first date for the start of the range. Format %YYYY-MM-DD == > 2019-12-10
   * @param str $end_date the first date for the end of the range. Format %YYYY-MM-DD == > 2019-12-10
   * @return array  results from the query
   */
  public function computeVariationAngleValueForSpecificPeriod($sensor_id, $start_date, $end_date)
  {
    $db = static::getDB();

    $sql_variation_angle = "select sum(ABS(new_values_inclinometer.angle_x - last_values_period_inclinometer.angle_x)) as variation_angleX,
    sum(ABS(new_values_inclinometer.angle_y - last_values_period_inclinometer.angle_y)) as variation_angleY,
    sum(ABS(new_values_inclinometer.angle_z - last_values_period_inclinometer.angle_z)) as variation_angleZ,
    sum(ABS(new_values_inclinometer.temperature - last_values_period_inclinometer.temperature)) as variation_temperature
        FROM
        (SELECT
        `sensor_id`,
        DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
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
      AND DATE(r.date_time) LIKE :start_date
        LIMIT 1) AS new_values_inclinometer
    JOIN
    (SELECT
        `sensor_id`,
        DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,,
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
      AND DATE(r.date_time) LIKE :end_date
        LIMIT 1) AS last_values_period_inclinometer
    ON new_values_inclinometer.sensor_id = last_values_period_inclinometer.sensor_id
            ";

    $stmt = $db->prepare($sql_variation_angle);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $results;
    }
  }

  /**
   * Compute variation (%) of inclinometer data from today to a specific date in term of days
   *
   * @param int $deveui sensor deveui for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago
   * @return array  results from the query
   * sensor_id |deveui |
   * newAngleX | oldAngleX | pourcentage_variation_anglex | newAngleY | oldAngleY | pourcentage_variation_angleY |
   * newAngleZ | oldAngleZ | pourcentage_variation_angleZ | newTemp | oldTemp | pourcentage_variation_temp |
   */
  public static function getInclinometerDataForLast($deveui, $time_period)
  {
    $db = static::getDB();

    $sql_data_inclinometer = "SELECT
        `sensor_id`,
        DATE_FORMAT(r.date_time, '%d/%m/%Y %H:%i:%s') AS date_d,
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
        LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
        WHERE
        `msg_type` LIKE 'inclinometre'
        AND Date(r.date_time) > Date(s.installation_date)
        AND s.deveui = :deveui  ";

    if ($time_period != -1) {
      $sql_data_inclinometer .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }

    $sql_data_inclinometer .= " ORDER BY r.date_time ASC";


    $stmt = $db->prepare($sql_data_inclinometer);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }

    if ($stmt->execute()) {
      $resultsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultsArr;
    }
  }

  /**
   * Get all the temperature messages received from the sensors given a specific sensor id
   *
   * @param int $sensor_id sensor id for which we want to retrieve the temperature data
   * @param string $date if we want to retrieve the data for specific date format Y-M-D
   * @return array  results from the query
   */
  public static function getTemperatureRecordsForSensor($deveui, $time_period = -1)
  {
    $db = static::getDB();

    $sql = "SELECT `temperature`, r.date_time AS date_d
      FROM `record` AS r
      LEFT JOIN inclinometer AS inc ON (inc.record_id = r.id)
      LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
      WHERE r.msg_type = 'inclinometre' AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql .= " AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    } else {
      $sql .= " AND Date(r.date_time) > s.installation_date ";
    }

    $sql .= " ORDER BY r.date_time ASC";

    $stmt = $db->prepare($sql);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $all_temp = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $all_temp;
    }
  }
}
