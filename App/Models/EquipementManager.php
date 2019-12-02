<?php

namespace App\Models;
use PDO;

class EquipementManager extends \Core\Model
{

  public function __constructor(){


  }

public function insertStructureType($type_asset){
  $sql = 'INSERT INTO structure_type (`typename`)
    SELECT * FROM (SELECT :type_asset) AS tmp
    WHERE NOT EXISTS (
        SELECT typename FROM structure_type WHERE typename like :type_asset
    ) LIMIT 1';

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':type_asset', $type_asset, PDO::PARAM_STR);

    return $stmt->execute();
}

  function getEquipements($group_name){

    $db = static::getDB();

    $query_get_equipement = "SELECT DISTINCT equipement, equipement_id FROM (SELECT site.nom AS site ,st.nom AS equipement, st.id AS equipement_id, gn.name AS GroupeName FROM structure AS st
      LEFT JOIN record AS r ON (r.structure_id=st.id)
      LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
      LEFT JOIN sensor_group AS gs ON (gs.sensor_id=s.id)
      LEFT JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
      LEFT JOIN group_site AS grs ON (grs.group_id=gn.group_id)
      LEFT JOIN site ON (site.id = grs.site_id)
      WHERE gn.name LIKE '$group_name') AS equipement_RTE";

      $stmt = $db->prepare($query_get_equipement);

      if ($stmt->execute()) {
        $all_equipment = $stmt->fetchAll();
        return $all_equipment;
      }
    }

    function getEquipementsById($siteID, $group_name){
      $db = static::getDB();

      $query_equipement_by_id = "SELECT DISTINCT equipement,equipement_id, nom, site_id FROM
      (SELECT  gs.sensor_id, site.nom AS nom, site.id AS site_id, st.nom AS equipement, st.id AS equipement_id FROM structure AS st
        INNER JOIN record AS r ON (r.structure_id=st.id)
        INNER JOIN sensor AS s ON (s.id = r.sensor_id)
        INNER JOIN sensor_group AS gs ON (gs.sensor_id=s.id)
        INNER JOIN group_name AS gn ON (gn.group_id = gs.groupe_id)
        LEFT JOIN site ON (site.id=st.site_id)
        WHERE gn.name = 'RTE' AND site_id = $siteID) AS RTE ";

        $stmt = $db->prepare($query_equipement_by_id);

        if ($stmt->execute()) {
          $all_equipment_by_id = $stmt->fetchAll();
          return $all_equipment_by_id;
        }
      }

    }
