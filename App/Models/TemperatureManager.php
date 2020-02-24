<?php

namespace App\Models;
use PDO;

/*
TemperatureManager.php
Handle the temperature data CRUD on the database
author : Lirone Samoun

*/

class TemperatureManager extends \Core\Model
{

public static function insert($temperature, $site, $dateTime){
        $db = static::getDB();

        $sql = "INSERT INTO `weather_associated` (`site_id`,`temperature`,`dateTime`)
            SELECT * FROM
            (SELECT (SELECT id FROM site WHERE nom like :site ) as site_id,
            :temperature AS temperature, :dateTime AS dateTime) AS weather_record
            WHERE NOT EXISTS (
            SELECT dateTime FROM weather_associated 
            WHERE dateTime = :dateTime
            and site_id = (SELECT id FROM site WHERE nom LIKE :site)) LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':temperature', $temperature, PDO::PARAM_STR);
        $stmt->bindValue(':dateTime', $dateTime, PDO::PARAM_STR);
        
        $stmt->execute();
        $count = $stmt->rowCount();
        if ($count == '0') {
            echo "\n0 temperature data were affected\n";
            return false;
        } else {
            echo "\n 1 temperature data was affected.\n";
            return true;
        }
}

}