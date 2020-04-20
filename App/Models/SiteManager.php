<?php

namespace App\Models;

use PDO;

/*
SiteManager.php
Handle the site data CRUD on the database
author : Lirone Samoun

*/

class SiteManager extends \Core\Model
{


  /**
   * Get all the site which belong to a specific group (RTE for example)
   *
   * @param string $group_name the name of the group we want to retrieve site data
   * @return array  results from the query
   */
  public static function getSites($groupId)
  {

    $db = static::getDB();

    $sql_query_get_site = "SELECT DISTINCT site.id AS site_id, site.nom AS site 
      FROM structure AS st
      LEFT JOIN site ON (st.site_id=site.id)
      LEFT JOIN sensor ON sensor.structure_id = st.id
      LEFT JOIN sensor_group AS sg ON (sg.sensor_id=sensor.id)
      LEFT JOIN group_name AS gn ON (gn.group_id=sg.groupe_id)
      WHERE gn.group_id = :groupId";

    $stmt = $db->prepare($sql_query_get_site);
    $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $all_site = $stmt->fetchAll();

      return $all_site;
    }
  }


  public static function getGeoCoordinates($group_name)
  {
    $db = static::getDB();

    $sql = "SELECT site.id, site.nom, site.latitude, site.longitude FROM site
      LEFT JOIN group_site AS gs ON (gs.site_id=site.id)
      LEFT JOIN group_name AS gn ON (gn.group_id=gs.group_id)
      WHERE gn.name LIKE :group_name";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $coordinateDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $coordinateDataArr;
    }
  }
}
