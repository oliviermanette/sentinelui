<?php

namespace App\Models;
use PDO;
use \App\Models\SpectreManager;
use App\Utilities;
use \Core\View;


class TimeSeriesManager extends \Core\Model
{

    /**
     * constructor
     *
     * @return void
     */
    function __construct()
    {
    }

    public function getAllTimeSeries(){
        $db = static::getDB();

        $sql = "select * FROM timeseries";

        $stmt = $db->prepare($sql);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }
    }

    public function getSpecificTimeSeriesFromPool($pool_id, $date_time = ""){
        $db = static::getDB();

        $sql = "SELECT p.id AS pool_id, Date(timeseries.date_time) AS date, valueX, valueY 
        FROM `timeseries` 
        LEFT JOIN record AS r ON (r.id = timeseries.record_id)
        LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
        LEFT JOIN pool AS p ON (p.sensor_id=s.id)
        WHERE p.id = :pool_id
        AND DATE(timeseries.date_time) LIKE '%:date_time%'";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':pool_id', $pool_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_time', $pool_id, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }
 
    }

    public function getSpecificTimeSeriesFromSensorID($sensor_id, $structure_id, $date_time = "")
    {
        $db = static::getDB();

        $sql = "SELECT p.id AS pool_id, timeseries.sensor_id AS sensor_id, timeseries.structure_id AS structure_id, Date(timeseries.date_time) AS date, valueX, valueY 
        FROM `timeseries` 
        LEFT JOIN record AS r ON (r.id = timeseries.record_id)
        LEFT JOIN sensor AS s ON (s.id = r.sensor_id)
        LEFT JOIN pool AS p ON (p.sensor_id=s.id)
        WHERE timeseries.sensor_id = :sensor_id 
        AND timeseries.structure_id = : 
        AND DATE(timeseries.date_time) LIKE '%:date_time%'";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
        $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }
    }

}