<?php


namespace App\Controllers;

use \Core\View;
use \App\Utilities;
use \App\Models\Settings\SettingSensorManager;
use \App\Models\RecordManager;
use \App\Models\InclinometerManager;
use \App\Models\EquipementManager;
use \App\Models\SpectreManager;
use \App\Models\ChocManager;
use \App\Models\SensorManager;
use App\Models\API\TemperatureAPI;
use App\Models\TemperatureManager;
use \App\Models\API\SensorAPI;

ini_set('error_reporting', E_ALL);
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "./log/error.log");
/*
ControllerDataObjenious.php
author : Lirone Samoun

Briefly : for testing purpose

/data

*/
class ControllerTest extends \Core\Controller
{

    public function testApiAction()
    {

        SensorAPI::getNbStatutsSensorsFromApi("RTE");
    }

    public function debugAction()
    {

        View::render('debug.php', []);
    }


    public function testSQLAction()
    {

        $deveui = '0004A30B00E829A7';
        $date_time_first_measure = '2020-03-29 20:49:36';
        //$variationArr = InclinometerManager::computeAverageDailyVariationPercentageAngleForLast($deveui, false, -1);
        //$height = EquipementManager::getEquipementHeightBySensorDeveui($deveui);
        //$dataArr = TemperatureAPI::getCurrentDataWeather('43.86801', '4.568677', $API_NAME = "DARKSKY");

        //$fullSpectreArr = SpectreManager::reconstituteAllSpectreForSensorSecondGeneration($deveui);
        $results = InclinometerManager::computeDirectionVariationForLast($deveui, $time_period = -1, $limit = 30);
        $percentageVariationDayArr = InclinometerManager::computeVariationPercentageAngleForLast($deveui, false, -1);
        var_dump(RecordManager::getBriefInfoFromAllRecords());

        $x = array_reverse($percentageVariationDayArr);

        //$percentageVariationDayArr = InclinometerManager::computeVariationPercentageAngleForLast($deveui, false, -1);
        //var_dump($x);
        //var_dump($percentageVariationDayArr);
    }


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
        //$coupleArr = $recordManager->getCoupleStructureIDSensorIDFromRecord($group_name);

        //ControllerInit::getTimeSerie(28, 3, "2019-10-24");
        exit();


        /*Pour chaque couple (structure/sensor) de la table pool
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
        */
    }

    public function goTestTimeSeriesAction()
    {
        $structure_id = 2;
        $site_id = 26;
        $date_request = '2020-01-10 15:39:02';
        //$allSubSpectresArr = SpectreManager::reconstituteOneSpectreForSensorFirstGeneration($site_id, $structure_id, $date_request);
        //var_dump($allSubSpectresArr);
        //$timeSerie = new TimeSeries();
        //$timeSerie->createFromSpectreArr($allSubSpectresArr);
        //print_r($timeSerie->getTimeSerieData());
        //ControllerInit::downloadCSV($timeSerie->getTimeSerieData(), $date_request);
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
    }
    */
}
