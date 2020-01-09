<?php

namespace App\Models;
use PDO;

class ScoreManager extends \Core\Model
{

public function __constructor(){

}

public function getLastScoreFromStructure($structure_id){
  $db = static::getDB();

  $sql_last_score ="SELECT id, `score_value`, `predicted_maintenance`, MAX(`date`) AS date FROM score
  WHERE structure_id = :structure_id
  GROUP BY id";

  $stmt = $db->prepare($sql_last_score);
  $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_STR);

  if ($stmt->execute()) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!isset($results[0])) {
      return 0;
    }else {
        return $results[0];
    }
    
  }

}

/**
TO CHANGE
* Get all the equipement score given a specific site ID
*
* @param int $siteID the site ID which we want to retrieve the score of the structure
* @return array  results from the query
*/
public function getAllStructuresScoresBySiteId($siteID){
  $db = static::getDB();

  $sql ="SELECT st.nom, s.date as date, s.score_value AS score , s.predicted_maintenance AS 'predicted_maintenance'
  FROM `structure` as st
  LEFT JOIN score AS s ON (s.id = st.score_id)
  WHERE site_id = :site_id";

  $stmt = $db->prepare($sql);
  $stmt->bindValue(':site_id', $siteID, PDO::PARAM_INT);

  if ($stmt->execute()) {
    $all_score = $stmt->fetchAll();

    return $all_score;
  }
}

/**
* Insert score to the DB
*
* @param date_time $date date format YYYY-MM-D
* @param int $score score value
* @param int $structure_id id of structure
* @return boolean  return True if insert query successfully executed
*/
public function insertScore($date, $score, $structure_id){
  $sql_score = 'INSERT INTO score(
    structure_id, date, score_value, predicted_maintenance
  )
  VALUES
  (
    (
      SELECT
      id
      FROM
      structure
      WHERE
      id = :structure_id
    ),
    :date_time,
    :score,
    NULL
  )
  ';

  $db = static::getDB();
  $stmt = $db->prepare($sql_score);

  $stmt->bindValue(':date_time', $date, PDO::PARAM_STR);
  $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_STR);
  $stmt->bindValue(':score', $score, PDO::PARAM_INT);

  return $stmt->execute();

}


}
