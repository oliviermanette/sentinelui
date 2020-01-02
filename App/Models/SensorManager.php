<?php

/*
SensorManager.php
author : Lirone Samoun

Briefly : 

*/

namespace App\Models;

use App\Config;
use App\Utilities;
use PDO;

class SensorManager extends \Core\Model
{

    public function __construct()
    {
    }

    /**
     * Get the number of actif sensor for a specific group
     *
     * @param string $group_name the group we want to check the number of actif sensor
     * @return array  array results
     */
    public function getNumberActifSensor($group_name)
    {
        $db = static::getDB();

        $sql_nb_actif_sensor = "SELECT 
      count(*) 
    FROM 
      (
    SELECT 
      DISTINCT s.device_number, 
      MAX(
        DATE(r.date_time)
      ) as dateMaxReceived 
    FROM 
      sensor AS s 
      INNER JOIN record AS r ON (s.id = r.sensor_id) 
      INNER JOIN sensor_group AS gs ON (gs.sensor_id = s.id) 
      INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
    WHERE 
      gn.name = :group_name 
    GROUP BY 
      s.device_number
    ) AS LAST_MSG_RECEIVED 
    WHERE 
      dateMaxReceived >= CURDATE() -1";

        $stmt = $db->prepare($sql_nb_actif_sensor);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $nb_actif_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $nb_actif_sensor[0];
        }
    }

    /**
     * Get the number of inactif sensor for a specific group
     *
     * @param string $group_name the group we want to check the number of actif sensor
     * @return array  array results
     */
    public function getNumberInactifSensor($group_name)
    {
        $db = static::getDB();

        $sql_nb_actif_sensor = "SELECT 
      count(*) 
    FROM 
      (
    SELECT 
      DISTINCT s.device_number, 
      MAX(
        DATE(r.date_time)
      ) as dateMaxReceived 
    FROM 
      sensor AS s 
      INNER JOIN record AS r ON (s.id = r.sensor_id) 
      INNER JOIN sensor_group AS gs ON (gs.sensor_id = s.id) 
      INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id) 
    WHERE 
      gn.name = :group_name
    GROUP BY 
      s.device_number
    ) AS LAST_MSG_RECEIVED 
    WHERE 
      dateMaxReceived < CURDATE() -1";

        $stmt = $db->prepare($sql_nb_actif_sensor);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $nb_actif_sensor = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $nb_actif_sensor[0];
        }
    }
}
