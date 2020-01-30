<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\SensorManager;
use \App\Auth;
use \App\Flash;
use App\Models\AlertManager;
use App\Models\BatteryManager;

/**
 * Sensors controller
 *
 * PHP version 7.0
 */
class ControllerSensors extends Authenticated
{

    /**
     * Show the index page for /sensors
     *
     * @return void
     */
    public function indexAction()
    {
        $group_name = $_SESSION['group_name'];
        //Get some info from the device
        $infoArr = SensorManager::getBriefInfoForGroup($group_name);
        View::renderTemplate('Sensors/index.html', [
            'info_sensors_array' => $infoArr
        ]);
    }

    /**
     * Show the info page for each sensor
     *
     * @return void
     */
    public function infoAction(){
        $label_device = $this->route_params["deviceid"];
        $deveui = SensorManager::getDeveuiFromLabel($label_device);
        $id_objenious = SensorManager::getDeviceIdObjeniousFromLabel($label_device);
        //Get brief info from sensors
        $infoArr = SensorManager::getBriefInfoForSensor($deveui);
        $lastMsgReceived = SensorManager::getLastMessageReceivedFromDeveui($deveui);
        $lastBatteryLevel = SensorManager::getLastBatteryStateFromDeveui($deveui);
        $nbreTotMsg = SensorManager::getNbTotalMessagesFromDeveui($deveui);
        $infoArr['id_objenious'] = $id_objenious;
        $infoArr['lastMsgReceived'] = $lastMsgReceived;
        $infoArr['nbreTotMsg'] = $nbreTotMsg;
        $infoArr['lastBatteryLevel'] = $lastBatteryLevel[0]["battery_level"];

        //Data map
        $dataMapArr = json_encode($infoArr);
        
        //get activity of sensors
        $recordRawArr = SensorManager::getRecordsFromDeveui($deveui);

        //Get alerts of the sensors
        $activeAlertsArr = AlertManager::getActiveAlertsInfoTableForSensor($deveui);
        $processedAlertsArr = AlertManager::getProcessedAlertsInfoTableForSensor($deveui);

        if (empty($activeAlertsArr) ){
            Flash::addAlert("Statut : OK. Pas d'alertes concernant cet équipement", Flash::OK);
        }else {
            Flash::addAlert("Attention, des alertes ont été soulevées", Flash::WARNING);
        }
        
        
        View::renderTemplate('Sensors/infoDevice.html', [
            'infoArr' => $infoArr,
            'dataMapArray' => $dataMapArr,
            'activeAlertsArr' => $activeAlertsArr,
            'processedAlertsArr' => $processedAlertsArr,
            'recordRawArr' => $recordRawArr
        ]);

    }

    /**
     * allow the user to download activity data from a specific sensor
     *
     * @return void
     */
    public function downloadActivityDataAction()
    {

        if (isset($_GET['exportDataFormat']) && isset($_GET['deveui'])){
            $format = $_GET['exportDataFormat'];
            $deveui = $_GET['deveui'];

            //Get activity data
            $recordRawArr = SensorManager::getRecordsFromDeveui($deveui);
            //print_r($recordRawArr);
            if (strcmp($format, "csv") == 0) {
                $timestamp = time();
                $filename = 'Export_data_sensors_' . $timestamp . '.csv';

                header('Content-Type: text/csv; charset=utf-8');
                header("Content-Disposition: attachment; filename=\"$filename\"");

                $columnNames = array();
                if (!empty($recordRawArr)) {
                    //We only need to loop through the first row of our result
                    //in order to collate the column names.
                    $firstRow = $recordRawArr[0];
                    foreach ($firstRow as $colName => $val) {
                        $columnNames[] = $colName;
                    }
                }

                $output = fopen("php://output", "w");
                //Start off by writing the column names to the file.
                fputcsv($output, $columnNames);
                //If we want to personalize the names
                /*fputcsv($output, array('Deveui', 'Site', 'Equipement', 'Date Time',
                'payload', 'Type message', 'payload', 'Amplitude 1', 'Amplitude 2',
                'Time 1', 'Time 2', 'X', 'Y', 'Z', 'Temperature', 'Batterie'));*/
                //Then, loop through the rows and write them to the CSV file.
                foreach ($recordRawArr as $row) {
                    fputcsv($output, $row);
                }

                //Close the file pointer.
                fclose($output);
                exit();
            } else if (strcmp($format, "excel") == 0) {

                $timestamp = time();
                $filename = 'Export_data_sensors_'.$deveui.'_' . $timestamp . '.xls';
                
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=\"$filename\"");

                $isPrintHeader = false;

                $columnNames = array();
                if (!empty($recordRawArr)) {
                    //We only need to loop through the first row of our result
                    //in order to collate the column names.
                    $firstRow = $recordRawArr[0];
                    if (!$isPrintHeader) {
                        foreach ($firstRow as $colName => $val) {
                            echo $colName . "\t";
                            //echo implode("\t", array_keys($colName)) . "\n";
                            $isPrintHeader = true;
                        }
                        echo "\n";
                    }
                    foreach ($recordRawArr as $row) {
                        echo implode("\t", array_values($row)) . "\n";
                    }
                    echo "\n";
                }
            }
        }


    }

}
