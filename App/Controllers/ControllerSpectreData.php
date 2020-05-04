<?php

namespace App\Controllers;

use ZipStream;
use \Core\View;
use \App\Auth;
use App\Models\ChartsManager;
use \App\Models\InclinometerManager;
use \App\Models\SiteManager;
use \App\Models\SpectreManager;
use \App\Models\TimeSeriesManager;
use \App\Models\TimeSeries;
use \App\Models\EquipementManager;
use \App\Models\SensorManager;
use \App\Models\ScoreManager;
use \App\Models\ChocManager;
use \App\Models\RecordManager;

/**
 * Controller data
 * Handle the data displayed on http://[...]/search-data and http://[...]/search-choc
 * Basically corresponds to the form when the user want to retrieve the data recevied by the sensors
 * PHP version 7.0
 */


class ControllerSpectreData extends Authenticated
{

    public $loggedin;

    /**
     * Show the index page for when the user want to retrieve spectre data from the form
     *  /search-spectre
     *
     * @return void
     */
    public function searchSpectreViewAction()
    {
        $user = Auth::getUser();
        $group_name = $user->getGroupName();

        $sites = SiteManager::getSites($user->group_id);
        $all_equipment = EquipementManager::getEquipements($user->group_id);
        $date_min_max = RecordManager::getDateMinMaxFromRecord();

        $min_date = $date_min_max[0];
        $max_date = $date_min_max[1];

        View::renderTemplate('Data/index.html', [
            'all_site'    => $sites,
            'all_equipment' => $all_equipment,
            'min_date' => $min_date,
            'max_date' => $max_date,
        ]);
    }

    /**
     * Get the number of spectres
     *
     * @return void
     */
    public function getNumberSpectresAction()
    {
        $deveui = $_POST["sensor_deveui_request"];
        if (SensorManager::checkProfileGenerationSensor($deveui) == 2) {
            $total_spectres = SpectreManager::countTotalNumberSpectresForForSensorSecondGeneration($deveui);
        } else {
            $total_spectres = SpectreManager::countTotalNumberSpectresForForSensorFirstGeneration($deveui);
        }
        print $total_spectres;
    }

    /**
     * Get all charts
     *
     * @return void
     */
    public function getAllChartsAction()
    {
        //$site_id = $_POST["site_request"];
        $deveui = $_POST["sensor_deveui_request"];
        $startDate = $_POST["startDate"];
        $endDate = $_POST["endDate"];
        $page_start = null;
        $total_per_page = null;
        if (isset($_POST["page_num"])) {
            $page_start = $_POST["page_num"];
        }
        if (isset($_POST["rows_per_page"])) {
            $total_per_page = $_POST["rows_per_page"];
        }

        $fullSpectreArr = array();
        //Check if the sensor if generation 2 or 1 because the treatment of the spectres are differents
        if (SensorManager::checkProfileGenerationSensor($deveui) == 2) {
            //echo "\nProfile 2\n";
            //Reconstruct spectres
            $spectresArr = SpectreManager::reconstituteAllSpectreForSensorSecondGeneration($deveui, $page_start, $total_per_page);
        } else {
            //echo "\nProfile 1\n";
            //Reconstruct spectres
            $spectresArr = SpectreManager::reconstituteAllSpectreForSensorFirstGeneration($deveui, $page_start, $total_per_page);
        }
        $fullSpectreArr["spectres"] = $spectresArr;
        $fullSpectreArr["deveui"] = $deveui;

        print json_encode($fullSpectreArr);
    }

    public function downloadSpectreAction()
    {
        //$equipement_id = $_GET['equipementID'];
        $deveui = $_GET['deveui'];
        $requestedDate = $_GET['requestedDate'];
        if (SensorManager::checkProfileGenerationSensor($deveui) == 2) {
            //$allSubSpectresArr = SpectreManager::reconstituteOneSpectreForSensorFirstGeneration($deveui, $requestedDate);
            $allSubSpectresArr = SpectreManager::reconstituteOneSpectreForSensorSecondGeneration($deveui, $requestedDate);
        } else {
            $allSubSpectresArr = SpectreManager::reconstituteOneSpectreForSensorFirstGeneration($deveui, $requestedDate);
        }

        //var_dump($allSubSpectresArr);
        $timeSerie = new TimeSeries();
        $timeSerie->createFromSpectreArr($allSubSpectresArr);
        //print_r($timeSerie->getTimeSerieData());

        ControllerInit::downloadCSV($timeSerie->getTimeSerieData(), $requestedDate);
    }

    public function downloadAllSpectresZipAction()
    {
        if (isset($_GET['exportDataFormat']) && isset($_GET['deveui']) && isset($_GET['type'])) {
            $format = $_GET['exportDataFormat'];
            $deveui = $_GET['deveui'];
            $type =  $_GET['type'];
            $device_number = SensorManager::getDeviceNumberFromDeveui($deveui);
            $structure_info = SensorManager::getStructureWhereIsInstalled($deveui);


            if ($type == "spectre") {
                if (SensorManager::checkProfileGenerationSensor($deveui) == 2) {
                    $dataArr = SpectreManager::reconstituteAllSpectreForSensorSecondGeneration($deveui);
                } else {
                    $dataArr = SpectreManager::reconstituteAllSpectreForSensorFirstGeneration($deveui);
                }

                # enable output of HTTP headers
                $options = new ZipStream\Option\Archive();
                $options->setSendHttpHeaders(true);

                # create a new zipstream object
                $timestamp_now = time();
                $filename_zip = 'Export_data_spectres_' . $device_number . '_' . $timestamp_now . '.zip';
                $zip = new ZipStream\ZipStream($filename_zip, $options);

                foreach ($dataArr as $spectreArr) {
                    $timeSerie = new TimeSeries();
                    $timeSerie->createFromSpectreArr($spectreArr);
                    $dataArr = $timeSerie->getTimeSerieData();

                    $dateTime = $spectreArr['date_time'];
                    $timestamp = strtotime($dateTime);
                    $structure_name = $spectreArr['structure_name'];

                    //tower225kv_3_spectre_data_19010011_2020-02-12 21_21_22.
                    $filename_csv = $structure_name . '_spectre_data_' . $device_number . '_' . $timestamp . '.csv';

                    $columnNames = array();
                    if (!empty($dataArr)) {
                        //We only need to loop through the first row of our result
                        //in order to collate the column names.
                        $firstRow = $dataArr[0];
                        foreach ($firstRow as $colName => $val) {
                            $columnNames[] = $colName;
                        }
                    }
                    $output = fopen("php://temp/maxmemory:1048576", "w");
                    if (false === $output) {
                        die('Failed to create temporary file');
                    }
                    // write the data to csv
                    fputcsv($output, $columnNames);
                    foreach ($dataArr as $row) {
                        fputcsv($output, $row);
                    }
                    // return to the start of the stream
                    rewind($output);
                    //var_dump($dataArr);

                    $zip->addFileFromStream($filename_csv, $output);
                    //Close the file pointer.
                    fclose($output);

                    # finish the zip stream


                    //print_r($spectreArr);
                }
                $zip->finish();
                exit();
                //$this->downloadSpectreActivityData($deveui, $format);
            }
        }
    }

    private function downloadSpectreActivityData($deveui, $format)
    {
        $dataArr = SpectreManager::getActivityData($deveui);
        if (strcmp($format, "csv") == 0) {

            $timestamp = time();
            $filename = 'Export_spectre_data_sensors_' . $timestamp . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"$filename\"");

            $columnNames = array();
            if (!empty($dataArr)) {
                //We only need to loop through the first row of our result
                //in order to collate the column names.
                $firstRow = $dataArr[0];
                foreach ($firstRow as $colName => $val) {
                    $columnNames[] = $colName;
                }
            }

            $output = fopen("php://output", "w");

            fputcsv($output, $columnNames);

            foreach ($dataArr as $row) {
                fputcsv($output, $row);
            }

            //Close the file pointer.
            fclose($output);
            exit();
        } else if (strcmp($format, "excel") == 0) {

            $timestamp = time();
            $filename = 'Export_spectre_data_sensors_' . $deveui . '_' . $timestamp . '.xls';

            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            $isPrintHeader = false;

            $columnNames = array();
            if (!empty($dataArr)) {
                //We only need to loop through the first row of our result
                //in order to collate the column names.
                $firstRow = $dataArr[0];
                if (!$isPrintHeader) {
                    foreach ($firstRow as $colName => $val) {
                        echo $colName . "\t";
                        //echo implode("\t", array_keys($colName)) . "\n";
                        $isPrintHeader = true;
                    }
                    echo "\n";
                }
                foreach ($dataArr as $row) {
                    echo implode("\t", array_values($row)) . "\n";
                }
                echo "\n";
            }
        }
    }
}
