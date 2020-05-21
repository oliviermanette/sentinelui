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


    public static function insertDataWeather($dataArr, $site, $dateTime, $API_NAME = "VISUALCROSSING")
    {

        if ($API_NAME == "VISUALCROSSING") {
            TemperatureManager::insertFromVisualCrossingAPI($dataArr, $site);
        } else if ($API_NAME == "DARKSKY") {

            TemperatureManager::insertFromDarkskyAPI($dataArr, $site, $dateTime);
        }
    }

    private static function insertFromDarkskyAPI($dataArr, $site, $dateTime)
    {

        $temperature = $dataArr["currently"]["temperature"];
        $precipitation = $dataArr["currently"]["precipIntensity"];
        $summary = $dataArr["currently"]["summary"];
        $humidity = $dataArr["currently"]["humidity"];
        $icon = $dataArr["currently"]["icon"];
        $windspeed = $dataArr["currently"]["windSpeed"];
        $windgust = $dataArr["currently"]["windGust"];
        $cloudcover = $dataArr["currently"]["cloudCover"];
        if (isset($dataArr['alerts'])) {
            $hasAlert = 1;
            $alert_description = $dataArr["alerts"][0]["title"];
            $alert_severity = $dataArr["alerts"][0]["severity"];
            $uri_alert = $dataArr["alerts"][0]["uri"];
        } else {
            $hasAlert = 0;
            $alert_description = Null;
            $alert_severity = Null;
            $uri_alert = Null;
        }


        $db = static::getDB();

        $sql = "INSERT INTO `weather_associated` (`site_id`,`temperature`,`dateTime`, `precipitation`, `summary`, `icon`,`humidity`, `windspeed`,`windgust`,`cloudcover`,`has_alert`, `alert_description`,`alert_severity`, `uri_alert`)
            SELECT * FROM
            (SELECT (SELECT id FROM site WHERE nom like :site ) as site_id,
            :temperature AS temperature, :dateTime AS dateTime, :precipitation AS precipitation,
            :summary AS summary, :icon AS icon, :humidity AS humidity, :windspeed AS windspeed,
            :windgust AS windgust, :cloudcover AS cloudcover, :hasAlert AS has_alert, :alert_description AS alert_description, :alert_severity AS alert_severity, :uri_alert AS uri_alert
            ) AS weather_record
            WHERE NOT EXISTS (
            SELECT dateTime FROM weather_associated
            WHERE dateTime = :dateTime
            and site_id = (SELECT id FROM site WHERE nom LIKE :site)) LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':temperature', $temperature, PDO::PARAM_STR);
        $stmt->bindValue(':dateTime', $dateTime, PDO::PARAM_STR);
        $stmt->bindValue(':precipitation', $precipitation, PDO::PARAM_STR);
        $stmt->bindValue(':summary', $summary, PDO::PARAM_STR);
        $stmt->bindValue(':icon', $icon, PDO::PARAM_STR);
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

    private static function insertFromVisualCrossingAPI($dataArr, $site)
    {

        $temperature = $dataArr["temp"];
        $precip = $dataArr["precip"];
        if (!isset($precip)) {
            $precip = 0;
        }
        $cloudcover = $dataArr["cloudcover"];
        $humidity = $dataArr["humidity"];
        $windspeed = $dataArr["wspd"];
        $windgust = $dataArr["wgust"];
        $windirection = $dataArr["wdir"];
        if (isset($dataArr["snow"])) {
            $snow = $dataArr["snow"];
        }
        $snowdepth = $dataArr["snowdepth"];
        $dateTime = $dataArr["datetime"];

        $db = static::getDB();

        $sql = "INSERT INTO `weather_associated` (`site_id`,`temperature`,`dateTime`,`precipitation`,`humidity`, `windspeed`,`windgust`,`cloudcover`)
            SELECT * FROM
            (SELECT (SELECT id FROM site WHERE nom like :site ) as site_id,
            :temperature AS temperature, :dateTime AS dateTime,
            :precip AS precip, :humidity AS humidity, :windspeed AS windspeed, :windgust AS windgust, :cloudcover AS cloudclover
            ) AS weather_record
            WHERE NOT EXISTS (
            SELECT dateTime FROM weather_associated
            WHERE dateTime = :dateTime
            and site_id = (SELECT id FROM site WHERE nom LIKE :site)) LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':temperature', $temperature, PDO::PARAM_STR);
        $stmt->bindValue(':dateTime', $dateTime, PDO::PARAM_STR);
        $stmt->bindValue(':precip', $precip, PDO::PARAM_STR);
        $stmt->bindValue(':humidity', $humidity, PDO::PARAM_STR);
        $stmt->bindValue(':windspeed', $windspeed, PDO::PARAM_STR);
        $stmt->bindValue(':windgust', $windgust, PDO::PARAM_STR);
        $stmt->bindValue(':cloudcover', $cloudcover, PDO::PARAM_STR);

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

    public static function getDataWeatherForSite($deveui, $site)
    {
        $db = static::getDB();

        $sql = "SELECT DISTINCT `temperature`, `summary`, `icon`,  `precipitation`, `humidity`,
        `windSpeed`, `windGust`, `alert_description`, `alert_severity`, DATE_FORMAT(weather_associated.dateTime, '%Y-%m-%d') as date_d FROM `weather_associated`
        LEFT JOIN site ON (site.id = weather_associated.site_id)
        LEFT join structure ON (structure.site_id = site.id)
        LEFT JOIN record ON (record.structure_id = structure.id)
        LEFT JOIN sensor ON (sensor.id = record.sensor_id)
        WHERE sensor.deveui =  :deveui
        AND site.nom LIKE  :site AND weather_associated.dateTime > sensor.installation_date
        ORDER BY `date_d`  DESC LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $temperatureDataArr = $stmt->fetch(PDO::FETCH_ASSOC);
            return $temperatureDataArr;
        }
    }

    public static function getAllDataWeatherForSite($deveui, $site, $limit = 30)
    {
        $db = static::getDB();

        $sql = "SELECT * FROM (
                    SELECT 
                    DISTINCT `temperature`, 
                    `summary`, 
                    `icon`, 
                    `precipitation`, 
                    `humidity`, 
                    `windSpeed`, 
                    `windGust`, 
                    `cloudCover`, 
                    `alert_description`, 
                    `alert_severity`, 
                    weather_associated.dateTime as date_time 
                    FROM 
                    `weather_associated` 
                    LEFT JOIN site ON (
                        site.id = weather_associated.site_id
                    ) 
                    LEFT join structure ON (structure.site_id = site.id) 
                    LEFT JOIN record ON (
                        record.structure_id = structure.id
                    ) 
                    LEFT JOIN sensor ON (sensor.id = record.sensor_id) 
                    WHERE 
                    sensor.deveui =  :deveui
                    AND site.nom LIKE :site
                    AND weather_associated.dateTime > sensor.installation_date 
                    ORDER BY 
                    `date_time` DESC
                ) AS data_weather 
                GROUP BY 
                Date(date_time) 
                ORDER BY 
                `data_weather`.`date_time` DESC 
                LIMIT 
                :limit
                ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $temperatureDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $temperatureDataArr;
        }
    }

    public static function getHistoricalDataForSite($deveui, $site, $limit = 30)
    {
        $db = static::getDB();

        $sql = "SELECT DISTINCT temperature, DATE_FORMAT(weather_associated.dateTime, '%Y-%m-%d') as date_d FROM `weather_associated`
        LEFT JOIN site ON (site.id = weather_associated.site_id)
        LEFT join structure ON (structure.site_id = site.id)
        LEFT JOIN record ON (record.structure_id = structure.id)
        LEFT JOIN sensor ON (sensor.id = record.sensor_id)
        WHERE sensor.deveui = :deveui
        AND site.nom LIKE :site AND weather_associated.dateTime > sensor.installation_date
        ORDER BY `date_d` ASC
        LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':site', $site, PDO::PARAM_STR);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $temperatureDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $temperatureDataArr;
        }
    }
}
