<?php

namespace App\Models;

use \Core\View;
use PDO;
use App\Config;
use \App\Models\Peak;
use \App\Models\RecordManager;
use App\Utilities;

/**
 * TimeSeries object
 *
 * PHP version 7.0
 */
class TimeSeries extends \Core\Model
{
    private $id;
    private $record_id;
    private $sensor_id;
    private $structure_id;
    private $date_time;
    private $dataType_id;

    private $listPeakArr;
    private $listSubspectreArr;

    private $isCreated; //If timeSeries has been filed with data



    /**
     * constructor
     *
     * @return void
     */
    function __construct()
    {
        $this->id = spl_object_id($this);
        //Init empty array
        $this->listSubspectreArr = array();
        $this->listPeakArr = array();

        $this->isCreated = false;

        //Init Data type
        $recordManager = new RecordManager();
        $this->dataType_id = $recordManager->getDataTypeIdFromName("spectre");
    }

    public function getId(){
        return $this->id;
    }


    /**
     * get the five subspectre data that constitute a full spectre
     *
     * @return array array which contain the data in hexa of each subspectre
     */
    public function getAllSubspectre()
    {
        return $this->listSubspectreArr;
    }

    /**
     * get all the peak that have this time serie
     *
     * @return array array which contain all the peak (valX and valY)
     */
    public function getAllPeaks()
    {
        return $this->listPeakArr;
    }

    /**
     * Find the first X peaks from the spectre
     * 
     * @param array $peakArr array data which contain all the peak ([valX, valYH])
     * @param int $nb first nbre peak to find in the spectre
     * @param float $thresh_high difference of at least thresh_hig% between the max and lower values 
     * to be considered a peak. 
     * @param float $thresh_low limits peaks to trhesh_low% in amplitude of the largest peak. 
     * @return array array which contain all the peak (valX and valY)
     */
    public function findPeaks($peaksArr, $nb, $thresh_high = 0.25, $thresh_low = 0.05)
    {
        $count = 0;
        $min = 10000;
        $max = 0;
        $newPeaksArr = array();
        while ($count < $nb) {
            for ($i = 0; $i < count($peaksArr); $i++) {
                $valX = intval($peaksArr[$i]->getValX());
                $valY = floatval($peaksArr[$i]->getValY());
                if ($valY < $min) {
                    $min = $valY;
                }
                echo "Min : " . $min . "\n";
            }
            $count++;
        }
        /*
        for ($i = 0; $i < count($peaksArr); $i++){
            if ($count < $nb){
                $valX = intval($peaksArr[$i]->getValX());
                $valY = floatval($peaksArr[$i]->getValY());
                if ($valY < $min){
                    $min = $valY;
                }
                echo $valY . "\n";
                //echo "Multiplication : ".$min * $thresh . "\n";
                //echo "Final : " . ($min + $min * $thresh) . "\n";
                if ($valY > ($min + $min * $thresh)){
                    echo "Value > thresh :".$valY . "\n";
                    if ($valY > $max) {
                        $max = $valY;
                        if ($max > )
                        $val=array("x" => $valX, "y" => $max);
                        array_push($newPeaksArr, $val);
                    }
                }
               
                //echo "Min : ". $min ."\n";

                //$count++;
            }
        }*/
        echo "New Peaks \n";
        print_r($newPeaksArr);
    }

    /**
     * From a spectre Array data, create the time serie object
     *
     * @param array $spectreArr array data that contain all the info needed for building the timeserie
     * @return void  
     */
    public function createFromSpectreArr($spectreArr)
    {
        //print_r($spectreArr);
        //Get basic info from the spectre array
        $record_id = $spectreArr["record_id"];
        $this->record_id = $record_id;
        $date_time = $spectreArr["date_time"];
        $this->date_time = $date_time;
        $sensor_id = $spectreArr["sensor_id"];
        $this->sensor_id = $sensor_id;
        $structure_id = $spectreArr["structure_id"];
        $this->structure_id = $structure_id;

        //Loop over the 5 subspectre to get the full spectre
        for ($i = 0; $i < 4; $i++) {
            $subspectreName = "subspectre_" . $i;
            //get the subspectre data
            if (array_key_exists($subspectreName, $spectreArr)) {
                $subspectreData = $spectreArr[$subspectreName];

                $subspectreDataValuesHex = $subspectreData["data"];
                $resolution = $subspectreData["resolution"];
                $min_freq = $subspectreData["min_freq"];
                $max_freq = $subspectreData["max_freq"];

                //fill in the subspectre array with the subspectre data value in hexa
                array_push($this->listSubspectreArr, $subspectreDataValuesHex);

                $axisX_freq = $min_freq;

                //Loop over the subspectre data values (hex format)
                for ($j = 2; $j < intval(strlen(strval($subspectreDataValuesHex))); $j += 2) {
                    //We need to analyse two by two
                    $data_amplitude_j_hex = substr($subspectreDataValuesHex, $j, 2);
                    //Convert hexa value to decimal
                    $data_amplitude_j_dec = Utilities::hex2dec($data_amplitude_j_hex, $signed = false);
                    //From the decimal value, compute the power of the amplitude
                    $axisY_amplitude = Utilities::accumulatedTable32($data_amplitude_j_dec);

                    //We keep only the data when the amplitude is not null
                    if ($data_amplitude_j_hex != 0) {
                        //Create a new peak
                        $peak = new Peak($axisX_freq, $axisY_amplitude);
                        //Put it to the list
                        array_push($this->listPeakArr, $peak);
                    }

                    $axisX_freq = $axisX_freq + $resolution;
                }
            }
        }
        //If everything is fined, we say that the time serie object is well created
        $this->isCreated = true;
    }

    /**
     * save the object Time Serie to the Database
     * Need first to create the TimeSerie Object
     * @return true is the object has been well saved  
     */
    public function save()
    {
        if (!$this->isCreated) {
            return false;
        }
        //Loop over the list of peak
        foreach ($this->listPeakArr as $peak) {
            $axisX_freq = $peak->getValX();
            $axisY_amplitude = $peak->getValY();

            echo "[" . $peak->getValX() . ", " . $peak->getValY() . "]" . "\n";
            //Insert data to DB
            TimeSeries::insertTimeSeriesData($this->record_id, $this->structure_id, $this->sensor_id, $this->date_time, $axisX_freq, $axisY_amplitude);
        }

        return true;
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
}