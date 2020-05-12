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

    /**
     * Insert Time Serie Data to Database
     *
     * @param int $record_id
     * @param int $structure_id
     * @param int $sensor_id
     * @param int $date_time
     * @param float $valueX x axis (frequency of the peak)
     * @param float $valueY y axis (amplitude of the peak)
     * @return true if the object has been saved correctly
     */
    private static function insertTimeSeriesData($record_id, $structure_id, $sensor_id, $date_time, $valueX, $valueY)
    {
        $db = static::getDB();

        $sql = "INSERT INTO timeseries(record_id, structure_id, sensor_id, dataType_id, date_time, valueX, valueY)
        SELECT :record_id, :structure_id, :sensor_id, (SELECT id FROM dataType WHERE nom = 'spectre'), :date_time, :valueX, :valueY
        WHERE NOT EXISTS (
            SELECT * FROM timeseries WHERE record_id = :record_id AND valueX = :valueX AND valueY = :valueY
        ) LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':valueX', $valueX, PDO::PARAM_STR);
        $stmt->bindValue(':valueY', $valueY, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);
        $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
        $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);
        $stmt->bindValue(':record_id', $record_id, PDO::PARAM_INT);
        $stmt->execute();

        $ok = $stmt->execute();

        $db = null;

        if ($ok) {
            return true;
        }
        return false;
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