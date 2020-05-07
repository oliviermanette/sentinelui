<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\SensorManager;
use \App\Models\API\SensorAPI;
use \App\Models\RecordManager;
use \App\Models\SiteManager;
use \App\Models\SpectreManager;
use \App\Models\TemperatureManager;
use \App\Models\TimeSeriesManager;
use \App\Models\TimeSeries;
use \App\Models\API\TemperatureAPI;
use \App\Models\NetworkAI\NeuralNetwork;
use \App\Models\NetworkAI\NeuralNetworkManager;
use \App\Flash;
use App\Utilities;
use Core\Controller;

/**
 * Time Series controller
 * PHP version 7.0
 */

class ControllerInit extends \Core\Controller
{

    /*
    public function fillTemperatureDataForSiteAction()
    {
        //Get all latitude and longitude of all the site in the DB
        $coordinateDataArr = SiteManager::getGeoCoordinates("RTE");

        foreach ($coordinateDataArr as $coordinateData) {

            $latitude = $coordinateData["latitude"];
            $longitude = $coordinateData["longitude"];
            $site = $coordinateData["nom"];
            $startDate = "2019-09-01";
            $endDate = "2020-02-25";
            $temperatureDataArr = TemperatureAPI::getHistoricalTemperatureData($latitude, $longitude, $startDate, $endDate);
            //print_r($temperatureDataArr);
            foreach ($temperatureDataArr as $temperatureData) {
                $date = $temperatureData["date"];
                $temperature = $temperatureData["temperature"];
                TemperatureManager::insert($temperature, $site, $date);
            }
        }
    }*/

    /**
     * TESTING PURPOSE
     * @return void
     */
    public function goTimeSeriesAction()
    {
        ini_set('max_execution_time', 0);
        $group_name = "RTE";

        //$neuralNetwork = NeuralNetworkManager::createNeuralNetworkFromTable(1);


        //1. INIT POOL
        $recordManager = new RecordManager();
        //$recordManager->initPool($group_name);
        $spectreManager = new SpectreManager();
        //Get all the sensor ID from Pool ID
        $sensorIdArr = $recordManager->getAllSensorIdFromPool();
        $coupleArr = $recordManager->getCoupleStructureIDSensorIDFromRecord($group_name);

        ControllerInit::getTimeSerie(28, 3, "2019-10-24");
        exit();


        //Pour chaque couple (structure/sensor) de la table pool
        foreach ($coupleArr as $couple) {
            $sensor_id = $couple["sensor_id"];
            $structure_id = $couple["structure_id"];
            $poolId = $recordManager->getPoolId($structure_id, $sensor_id);

            //Get all the spectre from a specific sensor
            $allSpectreArr = $spectreManager->reconstituteAllSpectreForSensorFirstGeneration($sensor_id);

            //Loop over all spectre received by a specific sensor
            foreach ($allSpectreArr as $spectreArr) {
                echo "\n==> NEW SPECTRE <== \n";

                $timeSerie = new TimeSeries();
                $timeSerie->createFromSpectreArr($spectreArr);
                //$timeSerie->save();
                print_r($timeSerie->getTimeSerieData());
                exit();
                echo "Spectre saved \n";

                $newPeakArr = $timeSerie->findPeaks($timeSerie->getAllPeaks(), 10, $thresh_high = 0.25, $thresh_low = 0.05);
                $timeSerie->setPeaks($newPeakArr);
                print_r($newPeakArr);
                exit();
                //Creation neural network
                $neuralNetwork = new NeuralNetwork(3, $poolId);

                $neuralNetwork->setUp($newPeakArr, $spectreArr["date_time"]);
                //print_r($neuralNetwork);
                exit();
            }
        }
    }

    public function goTestTimeSeriesAction()
    {
        $structure_id = 2;
        $site_id = 26;
        $date_request = '2020-01-10 15:39:02';
        $allSubSpectresArr = SpectreManager::reconstituteSpectre($site_id, $structure_id, $date_request);
        //var_dump($allSubSpectresArr);
        $timeSerie = new TimeSeries();
        $timeSerie->createFromSpectreArr($allSubSpectresArr);
        //print_r($timeSerie->getTimeSerieData());
        ControllerInit::downloadCSV($timeSerie->getTimeSerieData(), $date_request);
        /*
        //Example1
        $allSpectreArr = array();
        $TS1_peakArr = array("520" => 1, "860" => 2);
        $TS2_peakArr = array("499" => 0.95, "780" => 2.2);
        $TS3_peakArr = array("510" => 2.9, "819" => 1);
        array_push($allSpectreArr, $TS1_peakArr);
        array_push($allSpectreArr, $TS2_peakArr);
        array_push($allSpectreArr, $TS2_peakArr);


        //Loop over all spectre received by a specific sensor
        foreach ($allSpectreArr as $spectreArr) {
            echo "\n==> NEW SPECTRE <== \n";
            //Creation neural network
            $neuralNetwork = new NeuralNetwork(5, 1);
            $neuralNetwork->setUp($spectreArr);
            $neuralNetwork->save();

            exit();
            print_r(Utilities::getCombinations(2,10));
            //print_r($arrayTest);
            //$combiArr =  Utilities::getCombinationFromArray($arrayTest);
            //echo "Nbre combination ".count($combiArr) ."\n";
            //print_r($combiArr);
        }*/
    }




    public static function getTimeSerie($site_id, $equipement_id, $date_ask)
    {
        $spectreManager = new SpectreManager();
        $allSpectreArr = $spectreManager->reconstituteAllSpectreFromSpecificEquipement($site_id, $equipement_id);
        //print_r($allSpectreArr);
        //exit();
        foreach ($allSpectreArr as $spectreArr) {

            //echo "\n==> NEW SPECTRE <== \n";
            $dateTime = $spectreArr["date_time"];
            //echo $dateTime;
            //echo "\n";
            $dateTime = explode(" ", $dateTime);
            $date = $dateTime[0];
            //echo "DATE ASK : ".$date_ask ."\n";
            //echo "DATE : " . $date . "\n";
            if ($date === $date_ask) {
                //print_r($spectreArr);
                //echo "\nDate Asked : ". $date ."\n";
                $timeSerie = new TimeSeries();
                $timeSerie->createFromSpectreArr($spectreArr);

                //print_r($timeSerie->getTimeSerieData());
                ControllerInit::downloadCSV($timeSerie->getTimeSerieData(), $date);
            }
        }
    }

    public static function downloadCSV($data, $date)
    {
        $timestamp = time();
        $filename = 'Export_spectre' . $date . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $columnNames = array();
        /*if (!empty($data)) {
            //We only need to loop through the first row of our result
            //in order to collate the column names.
            $firstRow = $data[0];
            foreach ($firstRow as $colName => $val) {
                $columnNames[] = $colName;
            }
        }*/

        $output = fopen("php://output", "w");
        //Start off by writing the column names to the file.
        //fputcsv($output, $columnNames);
        //If we want to personalize the names
        fputcsv($output, array('X', 'Y'));
        //Then, loop through the rows and write them to the CSV file.
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        //Close the file pointer.
        fclose($output);
        exit();
    }
}
