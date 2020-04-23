<?php

namespace App\Models;

use App\Utilities;
use PDO;
use App\Models\Settings\SettingSensorManager;

/*
chocManager.php
Handle the choc CRUD on the database
author : Lirone Samoun

*/

class ChocManager extends \Core\Model
{



  /**
   * Check if a choc value is inside a specific range ( 1SD, 2SD , 3SD) to trigger an alert
   * @param int $sensor_id sensor id to target
   * @param int $time_period check for the last X days
   * @param enum $method if VALUE, check just if the current shock is below or above the thresh. if
   *              STD, check if the value is between 1SD, 2SD or 3SD
   * @return true if an alert is triggered
   */
  public function check($choc, $groupId, $method = "VALUE")
  {
    //The value received is in G
    if ($method == "VALUE") {

      $shockTresh = SettingSensorManager::getSettingValueForSensorOrNull($choc->deveui, "shock_thresh");

      if (isset($choc->power)) {
        if ($choc->power > $shockTresh) {
          echo "ALERT ! \n";
          return true;
        }
        return false;
      }
    } else if ($method = "STD") {
      echo "METHOD STD ! \n";
      $shockTreshSTD = SettingSensorManager::getSettingValueForSensorOrNull($choc->deveui, "shock_thresh_std");
      $timePeriodCheck = SettingSensorManager::getSettingValueForSensorOrNull($choc->deveui, "time_period");
      if (!isset($timePeriodCheck)) {
        $timePeriodCheck = -1;
      }
      if (!isset($shockTreshSTD)) {
        $shockTreshSTD = 3;
      }

      if (isset($choc->power)) {

        $avgPowerChoc = ChocManager::computeAvgPowerChocForLast($choc->deveui, $timePeriodCheck);
        $stdDevPowerChoc = ChocManager::computeStdDevChocForLast($choc->deveui, $timePeriodCheck);

        switch ($shockTreshSTD) {
          case 1:
            $highTresh = $avgPowerChoc + $stdDevPowerChoc;
            $lowThresh = $avgPowerChoc - $stdDevPowerChoc;
            break;
          case 2:
            $highTresh = $avgPowerChoc + 2 * $stdDevPowerChoc;
            $lowThresh = $avgPowerChoc - 2 * $stdDevPowerChoc;
            break;
          case 3:
            $highTresh = $avgPowerChoc + 3 * $stdDevPowerChoc;
            $lowThresh = $avgPowerChoc - 3 * $stdDevPowerChoc;
            break;
          default:
            $highTresh = $avgPowerChoc + $stdDevPowerChoc;
            $lowThresh = $avgPowerChoc - $stdDevPowerChoc;
        }

        echo "\n</br>";
        echo "\n Value power current choc : $choc->power ";
        echo "\n Average choc last days : $avgPowerChoc \n";
        echo "\n High Tresh last days : $highTresh \n";
        echo "\n Low Tresh last days : $lowThresh \n";

        if ($choc->power > $highTresh || $choc->power < $lowThresh) {
          echo "ALERT ! \n";
          return true;
        } else {
          echo "No alert ! \n";
          return false;
        }
      }
    }
  }

  /** set the structure Id
   *
   * @param int $structure_id id of the structure
   * @return void
   */
  public function setStructureID($structure_id)
  {
    $this->structure_id = $structure_id;
  }

  public static function getActivityData($deveui, $time_period = -1)
  {
    $db = static::getDB();

    $sql_power_choc = "SELECT
      r.date_time AS date_d,
      s.device_number,
        st.nom as structure_name,
        st.transmision_line_name as transmission_name,
        site.nom as site_name,
      `power`
      FROM
      choc
      LEFT JOIN record AS r ON (r.id = choc.record_id)
      LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
        LEFT JOIN structure AS st ON (st.id = s.structure_id)
        LEFT JOIN site AS site ON (site.id = st.site_id)
      WHERE
      `msg_type` LIKE 'choc'
      AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql_power_choc .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }
    $sql_power_choc .= "ORDER BY `date_d` ASC";

    $stmt = $db->prepare($sql_power_choc);

    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }
    if ($stmt->execute()) {
      $dataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $dataArr;
    }
  }


  /**
   * Get all the choc messages received from the sensors, for a specific group (RTE for example)
   *  sensor_id | sensor_deveui | site | equipement | date-time | payload | msg_type | amplitude_1
   * | amplitude_2 | time_1 | time_2 | freq_1 | freq_2 | power
   *
   * @param string $group_name the name of the group we want to retrieve choc data
   * @return array  results from the query
   */
  public static function getAllChocDataForGroup($group_name)
  {
    $db = static::getDB();

    $sql_choc_data = "SELECT
    sensor.id,
    sensor.device_number,
    sensor.deveui,
    s.nom AS site,
    st.nom AS equipement,
    st.transmision_line_name AS ligneHT,
    r.date_time as date_time,
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
    gn.name = :group_name
    AND Date(r.date_time) >= Date(sensor.installation_date)
    ORDER BY r.date_time DESC
    ";

    $stmt = $db->prepare($sql_choc_data);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Compute Mean of power choc received from today to a specific date in term of days
   * sensor_id | average_power
   *
   * @param int $sensor_id sensor id for which we want to compute the average power data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago
   * @return array  results from the query

   */
  public static function computeAvgPowerChocForLast($deveui, $time_period = -1)
  {
    $db = static::getDB();
    $sql_avg = "SELECT
      sensor_id,
      AVG(power) AS average_power
    FROM
      (
        SELECT
          `sensor_id`,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
          power
        FROM
          choc AS inc
          LEFT JOIN record AS r ON (r.id = inc.record_id)
          LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
        WHERE
          `msg_type` LIKE 'choc'
          AND s.deveui LIKE :deveui ";

    if ($time_period != -1) {
      $sql_avg .= " AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    } else {
      $sql_avg .= "  AND Date(r.date_time) > s.installation_date ";
    }

    $sql_avg .= " ORDER BY
          `date_d` DESC
      ) AS power_data
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
        $avgPower = $results["average_power"];

        return $avgPower;
      } else {
        return null;
      }
    }
  }

  /** Compute average power data for a specific range of date
   * sensor_id | average_power
   *
   * @param int $sensor_id sensor id for which we want to compute the variation data
   * @param str $start_date the first date for the start of the range. Format %YYYY-MM-DD == > 2019-12-10
   * @param str $end_date the first date for the end of the range. Format %YYYY-MM-DD == > 2019-12-10
   * @return array  results from the query
   */
  public static function computeAvgPowerChocForSpecificPeriod($sensor_id, $start_date, $end_date)
  {
    $db = static::getDB();

    $sql_avg = "SELECT
    sensor_id,
    AVG(power) AS average_power
    FROM
    (
      SELECT
        `sensor_id`,
        DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
        power
      FROM
        choc AS inc
        LEFT JOIN record AS r ON (r.id = inc.record_id)
      WHERE
        `msg_type` LIKE 'choc'
        AND `sensor_id` LIKE :sensor_id
        AND Date(r.date_time) BETWEEN :end_date
        AND :start_date
      ORDER BY
        `date_d` DESC
    ) AS period_power_data
    GROUP BY
      sensor_id
    ";

    $stmt = $db->prepare($sql_avg);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      if (isset($results)) {
        $avgPower = $results["average_power"];

        return $avgPower;
      } else {
        return null;
      }
    }
  }


  /**
   *  Compute standard deviation from power choc received from today to a specific date in term of days
   *  date_formatted | stdDev_power
   *
   * @param int $sensor_id from where we want to get the data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago
   * @return array  results from the query
   */
  public function computeStdDevChocForLast($deveui, $time_period = -1)
  {
    $db = static::getDB();

    $sql_stdDev = "SELECT
      sensor_id,
      STDDEV(power) AS stdDev_power
    FROM
      (
        SELECT
          `sensor_id`,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
          power
        FROM
          choc AS inc
          LEFT JOIN record AS r ON (r.id = inc.record_id)
          LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
        WHERE
          `msg_type` LIKE 'choc'
          AND s.deveui LIKE :deveui ";

    if ($time_period != -1) {
      $sql_stdDev .= " AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    } else {
      $sql_stdDev .= "  AND Date(r.date_time) > s.installation_date ";
    }

    $sql_stdDev .= "ORDER BY
            `date_d` DESC
          ) AS power_data
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
        $stdDevPower = $results["stdDev_power"];
        return $stdDevPower;
      } else {
        return null;
      }
    }
  }


  /** Compute std deviation  for a specific range of date
   * sensor_id | stdDevPower
   *
   * @param int $sensor_id sensor id for which we want to compute the variation data
   * @param str $start_date the first date for the start of the range. Format %YYYY-MM-DD == > 2019-12-10
   * @param str $end_date the first date for the end of the range. Format %YYYY-MM-DD == > 2019-12-10
   * @return array  results from the query
   */
  public function computeStdDevChocForSpecificPeriod($sensor_id, $start_date, $end_date)
  {
    $db = static::getDB();

    $sql_avg = "SELECT
    sensor_id,
    STDDEV(power) AS stdDevPower
    FROM
    (
      SELECT
        `sensor_id`,
        DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
        power
      FROM
        choc AS inc
        LEFT JOIN record AS r ON (r.id = inc.record_id)
      WHERE
        `msg_type` LIKE 'choc'
        AND `sensor_id` LIKE :sensor_id
        AND Date(r.date_time) BETWEEN :start_date
        AND :end_date
      ORDER BY
        `date_d` DESC
    ) AS period_power_data
    GROUP BY
      sensor_id
    ";

    $stmt = $db->prepare($sql_avg);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      if (isset($results)) {
        $stdDevPower = $results["stdDev_power"];

        return $stdDevPower;
      } else {
        return null;
      }
    }
  }
  /**
   *  Compute Mean of power choc received from a specific sensor ID. Compute daily, weekly, monthly
   *  and yearly average
   *  date_formatted | avg_power
   *
   * @param int $sensor_id from where we want to get the data
   * @param string $time_period DAY, WEEK, MONTH or YEAR
   * @return array  results from the query
   */
  public static function computeAvgPowerChocPerPeriod($sensor_id, $time_period = "DAY", $select = -1)
  {
    $db = static::getDB();
    if ($time_period == "DAY") {
      $sql_mean = "SELECT
                  DATE_FORMAT(dateTime, '%d-%m-%Y') AS date_formatted,";
    } else if ($time_period == "WEEK") {
      $sql_mean = "SELECT WEEK(dateTime) AS nb_date,
                  DATE_FORMAT(dateTime, '%v-%Y') AS date_formatted,";
    } else if ($time_period == "MONTH") {
      $sql_mean = "SELECT MONTH(dateTime) AS nb_date,
                  DATE_FORMAT(dateTime, '%m-%Y') AS date_formatted,";
    } else if ($time_period == "YEAR") {
      $sql_mean = "SELECT YEAR(dateTime) AS nb_date,
                  DATE_FORMAT(dateTime, '%Y') AS date_formatted,";
    }

    $sql_mean .= "AVG(power) AS avg_power
    FROM
      (
        SELECT
          sensor.id,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS dateTime,
          power
        FROM
          choc
          LEFT JOIN record AS r ON (r.id = choc.record_id)
          LEFT JOIN sensor ON (sensor.id = r.sensor_id)
          INNER JOIN structure AS st ON st.id = r.structure_id
        WHERE
          sensor.id = :sensor_id
        ORDER BY
          r.date_time DESC
      ) AS All_choc_power
    GROUP BY
      date_formatted, nb_date ";

    if ($time_period == "DAY") {
      $sql_mean .= "ORDER BY STR_TO_DATE(date_formatted, '%d-%m-%Y') DESC";
    } else if ($time_period == "WEEK") {
      $sql_mean .= "ORDER BY STR_TO_DATE(date_formatted, '%v-%Y') DESC";
    } else if ($time_period == "MONTH") {
      $sql_mean .= "ORDER BY STR_TO_DATE(date_formatted, '%m-%Y') DESC";
    } else if ($time_period == "YEAR") {
      $sql_mean .= "ORDER BY STR_TO_DATE(date_formatted, '%Y') DESC";
    }

    $stmt = $db->prepare($sql_mean);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results_avg_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $found = False;
      if ($select > -1) {
        foreach ($results_avg_arr as $value) {
          if ($value["nb_date"] == $select) {
            $avgRes = $value["avg_power"];
            $found = True;
          } else {
            $found = False;
          }
        }
      }
      if ($found) {
        return $avgRes;
      } else {
        return $results_avg_arr;
      }
    }
  }

  /**
   *  Compute standard deviation from power choc received from a specific sensor ID.
   *  Compute weekly, monthly and yearly average
   *  date_formatted | stdDevPower
   *
   * @param int $sensor_id from where we want to get the data
   * @param string $time_period DAY, WEEK, MONTH or YEAR
   * @return array  results from the query
   */
  public function computeStdDevChoc($sensor_id, $time_period = "MONTH")
  {
    $db = static::getDB();
    if ($time_period == "WEEK") {
      $sql_mean = "SELECT
                  DATE_FORMAT(dateTime, '%v-%Y') AS date_formatted,";
    } else if ($time_period == "MONTH") {
      $sql_mean = "SELECT
                  DATE_FORMAT(dateTime, '%m-%Y') AS date_formatted,";
    } else if ($time_period == "YEAR") {
      $sql_mean = "SELECT
                  DATE_FORMAT(dateTime, '%Y') AS date_formatted,";
    }

    $sql_mean .= "STDDEV(power) AS stdDev_power
    FROM
      (
        SELECT
          sensor.id,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS dateTime,
          power
        FROM
          choc
          LEFT JOIN record AS r ON (r.id = choc.record_id)
          LEFT JOIN sensor ON (sensor.id = r.sensor_id)
          INNER JOIN structure AS st ON st.id = r.structure_id
        WHERE
          sensor.id = :sensor_id
        ORDER BY
          r.date_time DESC
      ) AS All_choc_power
    GROUP BY
      date_formatted ";

    if ($time_period == "WEEK") {
      $sql_mean .= "ORDER BY STR_TO_DATE(date_formatted, '%v-%Y') DESC";
    } else if ($time_period == "MONTH") {
      $sql_mean .= "ORDER BY STR_TO_DATE(date_formatted, '%m-%Y') DESC";
    } else if ($time_period == "YEAR") {
      $sql_mean .= "ORDER BY STR_TO_DATE(date_formatted, '%Y') DESC";
    }

    $stmt = $db->prepare($sql_mean);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      if (isset($results)) {
        $stdDevPower = $results["stdDev_power"];

        return $stdDevPower;
      } else {
        return null;
      }
    }
  }


  /**
   * Get the last choc received from a given sensor
   *
   *  date | power
   * @param string $deveui the sensor we want to retrieve choc data
   * @return array  results from the query
   */
  public static function getLastChocPowerValueForSensor($deveui)
  {
    $db = static::getDB();

    $sql_last_choc = "SELECT
    DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date,
    power
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    LEFT JOIN sensor ON (sensor.id = r.sensor_id)
    WHERE
    sensor.deveui = :deveui
    ORDER BY r.date_time DESC LIMIT 1
    ";

    $stmt = $db->prepare($sql_last_choc);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      if (empty($results)) {
        return null;
      }
      return $results;
    }
  }


  /**
   * Get the number of choc received from a given sensor today
   *
   *  date | power
   * @param string $deveui the sensor we want to retrieve choc data
   * @return array  results from the query
   */
  public static function getNbChocReceivedTodayForSensor($deveui)
  {
    $db = static::getDB();

    $sql_last_choc = "SELECT
    COUNT(*) AS nb_choc_today
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    LEFT JOIN sensor ON (sensor.id = r.sensor_id)
    WHERE
    sensor.deveui = :deveui
    AND r.date_time >= CURDATE()
    AND r.date_time < CURDATE() + INTERVAL 1 DAY
    ";

    $stmt = $db->prepare($sql_last_choc);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetch(PDO::FETCH_ASSOC);
      if (empty($results)) {
        return null;
      }
      return $results;
    }
  }

  /**
   * Get all the power computed from choc data
   * id | date-time | power
   *
   * @param int $sensor_id the sensor we want to retrieve choc data
   * @return array  results from the query
   */
  public function getAllPowerDataChoc()
  {
    $sql = "SELECT sensor.id, DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d, power
    FROM choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    LEFT JOIN sensor ON (sensor.id = r.sensor_id)
    INNER JOIN structure AS st
    ON st.id=r.structure_id
    ";
  }

  /**
   * Get all the choc messages received from the sensors given a specific sensor id
   * sensor_id | date | amplitude_1 | amplitude_2 | time_1 | time_2 | freq_1 | freq_2 | power
   *
   * @param int $sensor_id sensor id for which we want to retrieve the choc data
   * @return array  results from the query
   */
  public function getAllChocDataForSensor($sensor_id)
  {
    $db = static::getDB();

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
   * Get the number of choc received from a given sensor today
   *  date | power
   *
   * @param int $sensor_id the sensor we want to retrieve choc data
   * @return array  results from the query
   */
  public function getNbChocReceivedPerDateForSensor($sensor_id, $date)
  {
    $db = static::getDB();

    $sql_nb_choc_date = "SELECT
    COUNT(*) AS nb_choc
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    LEFT JOIN sensor ON (sensor.id = r.sensor_id)
    WHERE
    sensor.id = :sensor_id
    AND DATE(r.date_time) LIKE :date_data
    ";

    $stmt = $db->prepare($sql_nb_choc_date);
    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
    $stmt->bindValue(':date_data', $date, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $results[0];
    }
  }
  /**
   * Get number of choc per day for a specific sensor
   *  date | number choc
   *
   * @param int $sensor_id sensor id for which we want to retrieve the number of choc per day
   * @return array  results from the query
   */
  public function getNbChocPerDayForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_nb_choc_per_day = "SELECT
    date_d,
    count(*) AS nb_choc
    FROM
    (
      SELECT
      `sensor_id`,
      DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
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
      LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
      WHERE
      `msg_type` LIKE 'choc'
      AND `sensor_id` LIKE :sensor_id
      AND Date(r.date_time) >= Date(s.installation_date)
    ) AS choc_data
    GROUP BY
    date_d
    ORDER BY
    date_d ASC
    ";

    $stmt = $db->prepare($sql_nb_choc_per_day);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   *
   * Get number of choc for last X days for a specific
   *

   * @param int $deveui sensor deveui for which we want to compute the variation data
   * @param int $time_period the last X days for computing the variation. Ex : $time_period = 30,
   * compute variation between today and the last value 30 days ago
   * @return array  results from the query
   * date | number choc
   */
  public static function getNbChocForLast($deveui, $time_period)
  {
    $db = static::getDB();

    $sql_nb_choc = "SELECT
        date_d,
        count(*) AS nb_choc
        FROM
        (
          SELECT
          `sensor_id`,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
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
          LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
          WHERE
          `msg_type` LIKE 'choc'
          AND s.deveui = :deveui
          AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE()
        ) AS choc_data
        GROUP BY
        date_d
        ORDER BY
        date_d ASC
        ";

    $stmt = $db->prepare($sql_nb_choc);

    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  public static function getNbChocPerDayForDates($deveui, $startDate, $endDate)
  {
    $db = static::getDB();

    $sql_nb_choc = "SELECT
        date_d,
        count(*) AS nb_choc
        FROM
        (
          SELECT
          `sensor_id`,
          DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
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
          LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
          WHERE
          `msg_type` LIKE 'choc'
          AND s.deveui = :deveui
          AND Date(r.date_time) BETWEEN :startDate AND :endDate) AS choc_data
        GROUP BY
        date_d
        ORDER BY
        date_d ASC
        ";

    $stmt = $db->prepare($sql_nb_choc);

    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = $stmt->rowCount();
    if ($count == '0') {
      return array();
    } else {
      return $results;
    }
  }
  public static function getPowerChocPerDayForDates($deveui, $startDate, $endDate)
  {
    $db = static::getDB();

    $sql_power_choc = "SELECT
      r.date_time AS date_d,
      `power`
      FROM
      choc
      LEFT JOIN record AS r ON (r.id = choc.record_id)
      LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
      WHERE
      `msg_type` LIKE 'choc'
      AND s.deveui = :deveui
      AND Date(r.date_time) BETWEEN :startDate AND :endDate
      ORDER BY `date_d` ASC
        ";

    $stmt = $db->prepare($sql_power_choc);

    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = $stmt->rowCount();
    if ($count == '0') {
      return array();
    } else {
      return $results;
    }
  }
  //TODO COMMENT AND NETTOYER UN PEU CODE INUTILE
  public static function getPowerChocForLast($deveui, $time_period = -1)
  {
    $db = static::getDB();

    $sql_power_choc = "SELECT
      r.date_time AS date_d,
      `power`
      FROM
      choc
      LEFT JOIN record AS r ON (r.id = choc.record_id)
      LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
      WHERE
      `msg_type` LIKE 'choc'
      AND s.deveui = :deveui ";

    if ($time_period != -1) {
      $sql_power_choc .= "AND Date(r.date_time) BETWEEN CURDATE() - INTERVAL :time_period DAY AND CURDATE() ";
    }
    $sql_power_choc .= "ORDER BY `date_d` ASC";

    $stmt = $db->prepare($sql_power_choc);

    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
    if ($time_period != -1) {
      $stmt->bindValue(':time_period', $time_period, PDO::PARAM_STR);
    }
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }


  /**
   * Get number of choc per week for a specific sensor
   *
   * date | number choc
   * @param int $sensor_id sensor id for which we want to retrieve the number of choc per week
   * @return array  results from the query
   */
  public function getNbChocPerWeekForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_nb_choc_per_week = "SELECT
    date_d,
    count(*) AS nb_choc
    FROM
    (
      SELECT
      `sensor_id`,
      YEARWEEK(`date_time`) AS date_d,
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
    ) AS choc_data
    group by
    date_d
    ORDER BY
    date_d ASC
    ";

    $stmt = $db->prepare($sql_nb_choc_per_week);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Get number of choc per month for a specific sensor
   * date | number choc
   * @param int $sensor_id sensor id for which we want to retrieve the number of choc per month
   * @return array  results from the query
   */
  public function getNbChocPerMonthForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_nb_choc_per_month = "SELECT
    date_d,
    count(*) AS nb_choc
    FROM
    (
      SELECT
      `sensor_id`,
      MONTH(`date_time`) AS date_d,
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
    ) AS choc_data
    group by
    date_d
    ORDER BY
    date_d ASC
    ";

    $stmt = $db->prepare($sql_nb_choc_per_month);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }
  /**
   * Retrieve all the power of chocs since the beginning for a specific sensor
   *
   * @param int $sensor_id sensor id for which we want to retrieve the power of chocs
   * @return array  results from the query
   */
  public function getPowerChocPerDayForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_power_choc = "SELECT
    DATE_FORMAT(r.date_time, '%d/%m/%Y %H:%i:%s')  AS date_d,
    `power`
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    LEFT JOIN sensor AS s ON (r.sensor_id = s.id)
    WHERE
    `msg_type` LIKE 'choc'
    AND `sensor_id` LIKE :sensor_id
    AND Date(r.date_time) >= Date(s.installation_date)
    ORDER BY `date_d` ASC";

    $stmt = $db->prepare($sql_power_choc);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Retrieve all the power of chocs since the beginning for a specific sensor for a specific date
   *
   * @param int $sensor_id sensor id for which we want to retrieve the power of chocs
   * @param date $date where we want to retrieve the data
   * @return array  results from the query
   */
  public function getPowerChocPerDateForSensor($sensor_id, $date)
  {
    $db = static::getDB();

    $sql_power_choc = "SELECT
    DATE_FORMAT(r.date_time, '%d/%m/%Y') AS date_d,
    `power`
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    WHERE
    `msg_type` LIKE 'choc'
    AND `sensor_id` LIKE :sensor_id
    AND DATE(r.date_time) LIKE :date_data
    ORDER BY `date_d` ASC";

    $stmt = $db->prepare($sql_power_choc);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);
    $stmt->bindValue(':date_data', $date, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Retrieve all the power of chocs since the beginning for a specific sensor and for week
   *
   * @param int $sensor_id sensor id for which we want to retrieve the power of chocs
   * @return array  results from the query
   */
  public function getPowerChocPerWeekForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_power_choc = "SELECT
    YEARWEEK(`date_time`) AS date_d,
    `power`
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    WHERE
    `msg_type` LIKE 'choc'
    AND `sensor_id` LIKE :sensor_id
    ORDER BY `date_d` ASC";

    $stmt = $db->prepare($sql_power_choc);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Retrieve all the power of chocs since the beginning for a specific sensor and for week
   *
   * @param int $sensor_id sensor id for which we want to retrieve the power of chocs
   * @return array  results from the query
   */
  public function getPowerChocPerMonthForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_power_choc = "SELECT
    MONTH(`date_time`) AS date_d,
    `power`
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    WHERE
    `msg_type` LIKE 'choc'
    AND `sensor_id` LIKE :sensor_id
    ORDER BY `date_d` ASC";

    $stmt = $db->prepare($sql_power_choc);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }
  /**
   * Get average power of choc per day for a specific sensor
   *
   * @param int $sensor_id sensor id for which we want to retrieve average of choc
   * @return array  results from the query
   */
  public function getAvgPowerChocPerDayForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_avg_power_choc_day = "SELECT
    DATE(`date_time`) AS date_d,
    AVG(`power`) AS puissance_moyenne
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    WHERE
    `msg_type` LIKE 'choc'
    AND `sensor_id` LIKE :sensor_id
    GROUP BY
    DATE(`date_time`)";

    $stmt = $db->prepare($sql_avg_power_choc_day);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  /**
   * Get average power of choc per week for a specific sensor
   *
   * @param int $sensor_id sensor id for which we want to retrieve average of choc
   * @return array  results from the query
   */
  public function getAvgPowerChocPerWeekForSensor($sensor_id)
  {
    $db = static::getDB();

    $sql_avg_power_choc_week = "SELECT
    YEARWEEK(`date_time`) AS date_d,
    AVG(`power`) AS puissance_moyenne
    FROM
    choc
    LEFT JOIN record AS r ON (r.id = choc.record_id)
    WHERE
    `msg_type` LIKE 'choc'
    AND `sensor_id` LIKE :sensor_id
    GROUP BY
    YEARWEEK(`date_time`)
    ORDER BY YEARWEEK(`date_time`) DESC
    ";

    $stmt = $db->prepare($sql_avg_power_choc_week);

    $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
  }

  public static function insertChoc($choc)
  {
    $sql_data_record_choc = 'INSERT INTO  choc (`record_id`, `amplitude_1`,  `amplitude_2`, `time_1`, `time_2`,  `freq_1`,`freq_2`, `power`)
      SELECT * FROM
      (SELECT (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "choc"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui)) AS record_id,
      :amplitude1 AS amplitude1, :amplitude2 AS amplitude2, :time1 AS time1, :time2 AS time2,
      :frequence1 AS frequence1, :frequence2 AS frequence2, :power AS power) AS id_record
      WHERE NOT EXISTS (
      SELECT record_id FROM choc WHERE record_id = (SELECT id FROM record WHERE date_time = :date_time AND msg_type = "choc"
      AND sensor_id = (SELECT id FROM sensor WHERE deveui = :deveui))
    ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($sql_data_record_choc);

    $stmt->bindValue(':date_time', $choc->dateTime, PDO::PARAM_STR);
    $stmt->bindValue(':deveui', $choc->deveui, PDO::PARAM_STR);
    $stmt->bindValue(':amplitude1', $choc->amplitude1, PDO::PARAM_STR);
    $stmt->bindValue(':amplitude2', $choc->amplitude2, PDO::PARAM_STR);
    $stmt->bindValue(':time1', $choc->time1, PDO::PARAM_STR);
    $stmt->bindValue(':time2', $choc->time2, PDO::PARAM_STR);
    $stmt->bindValue(':frequence1', $choc->frequence1, PDO::PARAM_STR);
    $stmt->bindValue(':frequence2', $choc->frequence2, PDO::PARAM_STR);
    $stmt->bindValue(':power', $choc->power, PDO::PARAM_STR);

    $stmt->execute();

    $count = $stmt->rowCount();
    if ($count == '0') {
      echo "\n[Choc] No choc inserted \n";
      return false;
    } else {
      echo "\n[Choc] New choc inserted.\n";
      return true;
    }
  }
}
