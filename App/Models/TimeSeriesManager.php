<?php

namespace App\Models;
use PDO;
use \App\Models\SpectreManager;
use App\Utilities;
use \Core\View;


class TimeSeriesManager extends \Core\Model
{

    public static function createTimeSeriesFromSpectreArr($spectreArr)
    {


        $date_time = $spectreArr["date_time"];
        $sensor_id = $spectreArr["sensor_id"];
        $structure_id = $spectreArr["structure_id"]; 
        print_r($spectreArr);
        exit();
        //Loop over subspectre
        foreach ($spectreArr as $spectre) {

            print_r($spectre);
            exit();
            $resolution = $spectre["resolution"];
            $min_freq = $spectre["min_freq"];
            $max_freq = $spectre["max_freq"];
            $subspectreValuesHex = $spectre["data"];
            echo $subspectreValuesHex . "\n";
            $spectre_msg_dec = "";
            $min_freq_initial = $min_freq;
            for ($i = 2; $i < intval(strlen(strval($subspectreValuesHex))); $i += 2) {
                $data_amplitude_i_hex = substr($subspectreValuesHex, $i, 2);

                $data_amplitude_i_dec = Utilities::hex2dec($data_amplitude_i_hex);
                $spectre_msg_dec .= strval($data_amplitude_i_dec);
                echo "[" . $min_freq . ", " . $data_amplitude_i_hex . "]" . "\n";
                $min_freq = $min_freq + $resolution;
            }
            exit();
        }
    }

    public static function insertTimeSeriesData(){
        $db = static::getDB();

        $sql = "INSERT INTO TimeSeries(record_id, structure_id, sensor_id, dataType_id, date_time, valueX, valueY)
        SELECT * FROM 
        (SELECT 1, :structure_id, :sensor_id, (SELECT id FROM dataType WHERE nom = 'spectre'), :date_time, :valueX, :valueY) AS timese
        WHERE NOT EXISTS (
            SELECT * FROM TimeSeries WHERE valueX = :valueX AND valueY = :valueY
        ) LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        /*$stmt->bindValue(':valueX', $valueX, PDO::PARAM_STR);
        $stmt->bindValue(':valueY', $valueY, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);
        $stmt->bindValue(':sensor_id', $sensor_id, PDO::PARAM_INT);
        $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);*/
        $stmt->execute();

        $ok = $stmt->execute();

        $db = null;

        if ($ok){
            return true;
        }
        return false;
    }


}