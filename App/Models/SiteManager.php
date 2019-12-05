<?php

namespace App\Models;
use PDO;

class SiteManager extends \Core\Model
{


  /**
  * Get all the site which belong to a specific group (RTE for example)
  *
  * @param string $group_name the name of the group we want to retrieve site data
  * @return array  results from the query
  */
  function getSites($group_name){

    $db = static::getDB();

    $sql_query_get_site = "SELECT DISTINCT site, site_id FROM (SELECT gn.name, site.id AS site_id, site.nom AS site, st.id, st.nom FROM structure AS st
      LEFT JOIN site ON (st.site_id=site.id)
      LEFT JOIN group_site AS gs ON (gs.site_id=site.id)
      LEFT JOIN group_name AS gn ON (gn.group_id=gs.group_id)
      WHERE gn.name LIKE :group_name) AS site_RTE";

      $stmt = $db->prepare($sql_query_get_site);
      $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

      if ($stmt->execute()) {
        $all_site = $stmt->fetchAll();

        return $all_site;
      }

    }

  }
