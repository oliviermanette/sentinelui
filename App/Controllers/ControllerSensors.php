<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\SensorManager;
use \App\Models\APIObjenious\SensorAPI;
use \App\Models\InclinometerManager;
use \App\Auth;
use \App\Flash;
use App\Models\AlertManager;
use App\Models\BatteryManager;
use App\Models\ChocManager;
use App\Models\SettingManager;

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

        $user = Auth::getUser();
        $group_name = $user->getGroupName();

        $label_device = $this->route_params["deviceid"];
        $deveui = SensorAPI::getDeveuiFromLabel($label_device);
        $id_objenious = SensorAPI::getDeviceIdObjeniousFromLabel($label_device);
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
        $date_min_max = SensorManager::getDateMinMaxActivity($deveui);
        $firstActivity = $date_min_max[0];
        $lastActivity = $date_min_max[1];

        //Get alerts of the sensors
        $activeAlertsArr = AlertManager::getActiveAlertsInfoTableForSensor($deveui);
        $processedAlertsArr = AlertManager::getProcessedAlertsInfoTableForSensor($deveui);

        if (empty($activeAlertsArr) ){
            Flash::addAlert("Statut : OK. Pas d'alertes concernant cet équipement", Flash::OK);
        }else {
            Flash::addAlert("Attention, des alertes ont été soulevées", Flash::WARNING);
        }

        //Get inclinometer data
        //References values
        $inclinaisonRefArr = InclinometerManager::getValuesReference($deveui, -1);
        //var_dump($inclinaisonRefArr);
        
        //1.Variation 1 month (30 days)
        $variationMonthArr = InclinometerManager::computePercentageVariationAngleValueForLast($deveui, false, 30, $precision = 3);
        //2.Variation 1 week (7 days)
        $variationWeekArr = InclinometerManager::computePercentageVariationAngleValueForLast($deveui, false, 7, $precision = 3);
        //3.Variation 1 day 
        $variationDayArr = InclinometerManager::computePercentageVariationAngleValueForLast($deveui, false, 1, $precision = 3);

        $totalVariationArr = array($variationDayArr, $variationWeekArr, $variationMonthArr);
        
        //4. chart data
        //Inclinometer raw data
        $inclinometerDataMonthArr = InclinometerManager::getInclinometerDataForLast($deveui, 30);
        $inclinometerDataMonthArr = json_encode($inclinometerDataMonthArr);
        $inclinometerDataWeekArr = InclinometerManager::getInclinometerDataForLast($deveui, 7);
        $inclinometerDataWeekArr = json_encode($inclinometerDataWeekArr);
        $inclinometerDataDayArr = InclinometerManager::getInclinometerDataForLast($deveui, -1);
        $inclinometerDataDayArr = json_encode($inclinometerDataDayArr);

        //percentage variation
        $percentageVariationDayArr = InclinometerManager::computeDailyVariationPercentageAngleForLast($deveui, false, -1);
        $percentageVariationDayArr = json_encode($percentageVariationDayArr);
        $percentageVariationWeekArr = InclinometerManager::computeWeeklyVariationPercentageAngleForLast($deveui, false, -1);
        $percentageVariationWeekArr = json_encode($percentageVariationWeekArr);
        $percentageVariationMonthArr = InclinometerManager::computeMonthlyVariationPercentageAngleForLast($deveui, false, -1);
        $percentageVariationMonthArr = json_encode($percentageVariationMonthArr);
        //Choc
        //Nb choc
        $nbChocDataMonthArr = ChocManager::getNbChocForLast($deveui,30);
        $nbChocDataMonthArr = json_encode($nbChocDataMonthArr);
        $nbChocDataWeekArr = ChocManager::getNbChocForLast($deveui, 7);
        $nbChocDataWeekArr = json_encode($nbChocDataWeekArr);
        $nbChocDataDay = ChocManager::getNbChocForLast($deveui, 1);
        $nbChocDataDay = json_encode($nbChocDataDay);

        //Power choc
        $powerChocDataMonthArr = ChocManager::getPowerChocForLast($deveui, 30);
        $powerChocDataMonthArr = json_encode($powerChocDataMonthArr);
        $powerChocDataWeekArr = ChocManager::getPowerChocForLast($deveui, 7);
        $powerChocDataWeekArr = json_encode($powerChocDataWeekArr);
        $powerChocDataDayArr = ChocManager::getPowerChocForLast($deveui, 1);
        $powerChocDataDayArr = json_encode($powerChocDataDayArr);

        //Temperature data
        $tempArr = InclinometerManager::getTemperatureRecordsForSensor($deveui,-1);
        $tempArr = json_encode($tempArr);

        //Get settings
        $inclinometerRangeThresh = SettingManager::getInclinometerRangeThresh($group_name);

        //Alerts
        $alertsActiveDataArr = AlertManager::getActiveAlertsInfoTable($group_name, $deveui);
        $alertsProcessedDataArr = AlertManager::getProcessedAlertsInfoTable($group_name, $deveui);
        

        View::renderTemplate('Sensors/infoDevice.html', [
            'deveui' => $deveui,
            'inclinometerRangeThresh' => $inclinometerRangeThresh,
            'firstActivity' => $firstActivity,
            'lastActivity' => $lastActivity,
            'infoArr' => $infoArr,
            'dataMapArray' => $dataMapArr,
            'activeAlertsArr' => $activeAlertsArr,
            'processedAlertsArr' => $processedAlertsArr,
            'recordRawArr' => $recordRawArr,
            'totalVariationArr' => $totalVariationArr,
            'inclinometerDataMonthArr' => $inclinometerDataMonthArr,
            'inclinometerDataWeekArr' => $inclinometerDataWeekArr,
            'inclinometerDataDayArr' => $inclinometerDataDayArr,
            'nbChocDataMonthArr' => $nbChocDataMonthArr,
            'nbChocDataWeekArr' => $nbChocDataWeekArr,
            'nbChocDataDay' => $nbChocDataDay,
            'powerChocDataMonthArr' => $powerChocDataMonthArr,
            'powerChocDataDayArr' => $powerChocDataDayArr,
            'powerChocDataWeekArr' => $powerChocDataWeekArr,
            'percentageVariationDayArr' => $percentageVariationDayArr,
            'percentageVariationWeekArr' => $percentageVariationWeekArr,
            'percentageVariationMonthArr' => $percentageVariationMonthArr,
            'inclinaisonRefArr' => $inclinaisonRefArr,
            'temperatureArr' => $tempArr,
            'alerts_active_info_arr' => $alertsActiveDataArr,
            'alerts_processed_info_arr' => $alertsProcessedDataArr,
        ]);

    }

    public function getChartDataNbChocAction(){
        if (isset($_POST["deveui"]) && isset($_POST["startDate"]) && isset($_POST["endDate"])){
            
            $startDate = $_POST["startDate"];
            $endDate = $_POST["endDate"];
            $deveui = $_POST["deveui"];
            $nbChocData = ChocManager::getNbChocPerDayForDates($deveui, $startDate, $endDate);
            

            print json_encode($nbChocData);
        }
    }

    public function getChartDataPowerChocAction()
    {
        if (isset($_POST["deveui"]) && isset($_POST["startDate"]) && isset($_POST["endDate"])) {

            $startDate = $_POST["startDate"];
            $endDate = $_POST["endDate"];
            $deveui = $_POST["deveui"];
            $nbChocData = ChocManager::getPowerChocPerDayForDates($deveui, $startDate, $endDate);


            print json_encode($nbChocData);
        }
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
