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

    public static function insert($temperature, $site, $dateTime)
    {
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

    public static function insertDataWeather($dataArr, $site, $dateTime)
    {

        $temperature = $dataArr["currently"]["temperature"];
        $summary = $dataArr["currently"]["summary"];
        $humidity = $dataArr["currently"]["humidity"];
        $windspeed = $dataArr["currently"]["windSpeed"];
        $windgust = $dataArr["currently"]["windGust"];
        $cloudcover = $dataArr["currently"]["cloudCover"];
        if (isset($dataArr['alerts'])) {
            $hasAlert = True;
            $alert_description = $dataArr["alerts"][0]["title"];
            $alert_severity = $dataArr["alerts"][0]["severity"];
            $uri_alert = $dataArr["alerts"][0]["uri"];
        } else {
            $hasAlert = False;
            $alert_description = Null;
            $alert_severity = Null;
            $uri_alert = Null;
        }

        $db = static::getDB();

        $sql = "INSERT INTO `weather_associated` (`site_id`,`temperature`,`dateTime`, `summary`,`humidity`, `windspeed`,`windgust`,`cloudcover`,`has_alert`, `alert_description`,`alert_severity`, `uri_alert`)
            SELECT * FROM
            (SELECT (SELECT id FROM site WHERE nom like :site ) as site_id,
            :temperature AS temperature, :dateTime AS dateTime,
            :summary, :humidity, :windspeed, :windgust, :cloudcover, :hasAlert, :alert_description, :alert_severity, :uri_alert
            ) AS weather_record
            WHERE NOT EXISTS (
            SELECT dateTime FROM weather_associated
            WHERE dateTime = :dateTime
            and site_id = (SELECT id FROM site WHERE nom LIKE :site)) LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':temperature', $temperature, PDO::PARAM_STR);
        $stmt->bindValue(':dateTime', $dateTime, PDO::PARAM_STR);
        $stmt->bindValue(':summary', $summary, PDO::PARAM_STR);
        $stmt->bindValue(':humidity', $humidity, PDO::PARAM_STR);
        $stmt->bindValue(':windspeed', $windspeed, PDO::PARAM_STR);
        $stmt->bindValue(':windgust', $windgust, PDO::PARAM_STR);
        $stmt->bindValue(':cloudcover', $cloudcover, PDO::PARAM_STR);
        $stmt->bindValue(':hasAlert', $hasAlert, PDO::PARAM_STR);
        $stmt->bindValue(':alert_description', $alert_description, PDO::PARAM_STR);
        $stmt->bindValue(':alert_severity', $alert_severity, PDO::PARAM_STR);
        $stmt->bindValue(':uri_alert', $uri_alert, PDO::PARAM_STR);

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


    public static function getHistoricalDataForSite($deveui, $site)
    {
        $db = static::getDB();

        $sql = "SELECT DISTINCT temperature, DATE_FORMAT(weather_associated.dateTime, '%Y-%m-%d') as date_d FROM `weather_associated`
        LEFT JOIN site ON (site.id = weather_associated.site_id)
        LEFT join structure ON (structure.site_id = site.id)
        LEFT JOIN record ON (record.structure_id = structure.id)
        LEFT JOIN sensor ON (sensor.id = record.sensor_id)
        WHERE sensor.deveui = :deveui
        AND site.nom LIKE :site AND weather_associated.dateTime > sensor.installation_date
        ORDER BY `date_d`  ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $temperatureDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $temperatureDataArr;
        }
    }
}
