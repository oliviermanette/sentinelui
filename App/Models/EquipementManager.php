<?php

namespace App\Models;

use PDO;

/*
EquipementManager.php
Handle the structure CRUD on the database
author : Lirone Samoun

*/

class EquipementManager extends \Core\Model
{

  public function __constructor()
  {
  }

  /**
   * Insert structure type inside the DB
   *
   * @param string $type_asset type of assert to insert (ex : transmission line)
   * @return boolean  return True if insert query successfully executed
   */
  public static function insertStructureType($type_asset)
  {

    $sql = 'INSERT INTO structure_type (`typename`)
    SELECT * FROM (SELECT :type_asset) AS tmp
    WHERE NOT EXISTS (
        SELECT typename FROM structure_type WHERE typename like :type_asset
    ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':type_asset', $type_asset, PDO::PARAM_STR);

    $count = $stmt->rowCount();
    if ($count == '0') {
      echo "\n0 structure were added\n";
      return false;
    } else {
      echo "\n 1 structure added.\n";
      return true;
    }
  }

  /**
   * Get all the equipement which belong to a specific group (RTE for example)
   *
   * @param string $group_name the name of the group we want to retrieve equipment data
   * @return array  results from the query
   */
  public static function getEquipements($group_name)
  {

    $db = static::getDB();

    $sql_query_get_equipement = "SELECT DISTINCT equipement, equipement_id FROM (SELECT site.nom AS site ,st.nom AS equipement, st.id AS equipement_id, gn.name AS GroupeName FROM structure AS st
    LEFT JOIN record AS r ON (r.structure_id=st.id)
    LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
    LEFT JOIN sensor_group AS gs ON (gs.sensor_id=s.id)
    LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
    LEFT JOIN group_site AS grs ON (grs.group_id=gn.group_id)
    LEFT JOIN site ON (site.id = grs.site_id)
    WHERE gn.name LIKE :group_name) AS equipement_RTE";

    $stmt = $db->prepare($sql_query_get_equipement);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $all_equipment = $stmt->fetchAll();

      return $all_equipment;
    }
  }

  /** Get equipement (structure) info from the DB using the equipement id
   *
   * @param int $structure_id id of the structure
   * @return array results of the query
   *  equipement_id | equipement | ligneHT  
   */
  public static function getEquipementFromId($structure_id){
    $db = static::getDB();

    $sql_query_equipement_by_id = "SELECT 
        DISTINCT st.id AS equipement_id, 
        st.nom AS equipement, 
        st.transmision_line_name AS ligneHT 
      FROM 
        structure AS st 
      WHERE 
        st.id = :structure_id
      ";

    $stmt = $db->prepare($sql_query_equipement_by_id);
    $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $equipement = $stmt->fetch(PDO::FETCH_ASSOC);

      return $equipement;
    }
  }

  /**
   * Get all the equipement which belong to a specific group (RTE for example) given a particular site ID
   *
   * @param int $siteID ID of the site
   * @param string $group_name the name of the group we want to retrieve equipment data
   * @return array  results from the query
   */
  function getEquipementsBySiteId($siteID, $group_name)
  {
    $db = static::getDB();

    $sql_query_equipement_by_id = "SELECT DISTINCT equipement_id, equipement, ligneHT, nomSite FROM
    (SELECT  gs.sensor_id, site.nom AS nomSite, site.id AS site_id, st.transmision_line_name AS ligneHT, st.nom AS equipement, st.id AS equipement_id FROM structure AS st
      INNER JOIN record AS r ON (r.structure_id=st.id)
      INNER JOIN sensor AS s ON (s.id = r.sensor_id)
      INNER JOIN sensor_group AS gs ON (gs.sensor_id=s.id)
      INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
      LEFT JOIN site ON (site.id=st.site_id)
      WHERE gn.name = :group_name AND site_id = :site_id) AS RTE ";

    $stmt = $db->prepare($sql_query_equipement_by_id);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
    $stmt->bindValue(':site_id', $siteID, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $all_equipment_by_id = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $all_equipment_by_id;
    }
  }

  /**
   * Get sensor ID on a specific structure
   * 
   * @param int $structure_id structure id to get the sensor id
   * @return int  sensor id
   */
  public static function getSensorIdOnEquipement($structure_id)
  {
    $db = static::getDB();

    $sql_sensor_id = "SELECT DISTINCT sensor.id AS sensor_id FROM sensor
    LEFT JOIN record as r ON (r.sensor_id = sensor.id)
    LEFT JOIN structure as st ON (r.structure_id = st.id)
    WHERE st.id = :structure_id";

    $stmt = $db->prepare($sql_sensor_id);
    $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $sensor_id_res = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (isset($sensor_id_res[0])) {
        return $sensor_id_res[0]['sensor_id'];
      }
    }
  }

  
  /**
   * Get deveui of a sensor on a specific structure
   * 
   * @param int $structure_id structure id to get the sensor id
   * @return string  deveui
   */
  public static function getDeveuiSensorOnEquipement($structure_id)
  {
    $db = static::getDB();

    $sql_sensor_id = "SELECT DISTINCT sensor.deveui AS deveui FROM sensor
    LEFT JOIN record as r ON (r.sensor_id = sensor.id)
    LEFT JOIN structure as st ON (r.structure_id = st.id)
    WHERE st.id = :structure_id";

    $stmt = $db->prepare($sql_sensor_id);
    $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $deveui = $stmt->fetch(PDO::FETCH_COLUMN);
      return $deveui;
    }
  }

  /**
   * Get an equipement from a sensor deveui
   * 
   * @param int $sensor_id sensor id to get the equipement ID
   * @return int  sensor id
   */
  public static function getEquipementIdBySensorDeveui($deveui)
  {
    $db = static::getDB();

    $sql_equipement_id = "SELECT DISTINCT structure.id AS equipement_id
    FROM structure
    LEFT JOIN record as r ON (r.structure_id = structure.id)
    LEFT JOIN sensor ON (sensor.id = r.sensor_id)
    WHERE sensor.deveui = :deveui";

    $stmt = $db->prepare($sql_equipement_id);
    $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $sensor_id_res = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (isset($sensor_id_res[0])) {
        return $sensor_id_res[0]['equipement_id'];
      }
    }
  }

  /**
   * Get all the structure belonging to a specific site
   * 
   * @param int $siteID site ID to retrieve all the structure
   * @return array results from the query
   */
  function getAllStructuresBySiteId($siteID)
  {
    $db = static::getDB();

    $sql = "SELECT st.nom AS equipement, st.transmision_line_name AS ligne FROM structure AS st
    WHERE st.site_id = :id_site";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id_site', $siteID, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $all_temp = $stmt->fetchAll();

      return $all_temp;
    }
  }
}
