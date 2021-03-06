<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\SensorManager;
use \App\Models\API\SensorAPI;
use \App\Models\InclinometerManager;
use \App\Auth;
use \App\Flash;
use App\Models\AlertManager;
use App\Models\API\TemperatureAPI;
use App\Models\BatteryManager;
use App\Models\ChocManager;
use App\Models\SpectreManager;
use App\Models\Settings\SettingSensorManager;
use App\Models\TemperatureManager;
use \App\Utilities;

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
    public function indexViewAction()
    {
        $user = Auth::getUser();
        //Get some info from the device
        $infoArr = SensorManager::getBriefInfoForGroup($user->group_id);
        View::renderTemplate('Sensors/index.html', [
            'info_sensors_array' => $infoArr
        ]);
    }

    /**
     * Show the setting page for each sensor
     *
     * @return void
     */
    public function settingsViewAction()
    {
        $user = Auth::getUser();
        Auth::rememberRequestedPage();
        $label_device = $this->route_params["deviceid"];
        $deveui = SensorAPI::getDeveuiFromLabel($label_device);
        //Get the settings of the current user
        $settingsArr = $this->getSettingsForCurrentSensor($deveui);

        $first_inclinationY_thresh = Utilities::array_find_deep($settingsArr, "first_inclinationY_thresh");
        $second_inclinationY_thresh = Utilities::array_find_deep($settingsArr, "second_inclinationY_thresh");
        $third_inclinationY_thresh = Utilities::array_find_deep($settingsArr, "third_inclinationY_thresh");
        $first_inclinationX_thresh = Utilities::array_find_deep($settingsArr, "first_inclinationX_thresh");
        $shock_thresh = Utilities::array_find_deep($settingsArr, "shock_thresh");
        $isAlertEmailActivated = Utilities::array_find_deep($settingsArr, "isAlertEmailActivated");

        if ($first_inclinationY_thresh) {
            $first_inclinationY_thresh = $settingsArr[$first_inclinationY_thresh[0]];
        } else {
            $first_inclinationY_thresh = 0;
        }
        if ($first_inclinationX_thresh) {
            $first_inclinationX_thresh = $settingsArr[$first_inclinationX_thresh[0]];
        } else {
            $first_inclinationX_thresh = 0;
        }
        if ($second_inclinationY_thresh) {
            $second_inclinationY_thresh = $settingsArr[$second_inclinationY_thresh[0]];
        } else {
            $second_inclinationY_thresh = 0;
        }
        if ($third_inclinationY_thresh) {
            $third_inclinationY_thresh = $settingsArr[$third_inclinationY_thresh[0]];
        } else {
            $third_inclinationY_thresh = 0;
        }
        if ($shock_thresh) {
            $shock_thresh = $settingsArr[$shock_thresh[0]];
        } else {
            $shock_thresh = 1;
        }
        if ($isAlertEmailActivated) {
            $isAlertEmailActivated = $settingsArr[$isAlertEmailActivated[0]];
        } else {
            $isAlertEmailActivated = 0;
        }
        $context = [
            "device_number" => $label_device,
            "deveui" => $deveui,
            'settingsFirstInclinationXThresh' => $first_inclinationX_thresh,
            'settingsFirstInclinationYThresh' => $first_inclinationY_thresh,
            'settingsSecondInclinationYThresh' => $second_inclinationY_thresh,
            'settingsThirdInclinationYThresh' => $third_inclinationY_thresh,
            'settingsShockThresh' => $shock_thresh,
            'settingsAlertEmailActivated' => $isAlertEmailActivated,
        ];
        View::renderTemplate('Sensors/viewSettings.html', $context);
    }

    public function getSettingsForCurrentSensor($deveui)
    {

        $user = Auth::getUser();
        $settingsArr = SettingSensorManager::findByDeveui($deveui);
        $isAlertEmailActivated = SettingSensorManager::checkIfAlertByEmailActivatedForUser($user->email);
        $tmpArr = array("name_setting" => "isAlertEmailActivated", "value" => $isAlertEmailActivated);

        array_push($settingsArr, $tmpArr);
        return $settingsArr;
    }

    private function checkAndUpdate($deveui, $settingName, $settingValue)
    {

        if (SettingSensorManager::checkIfSettingExistForSensor($deveui, $settingName)) {
            echo "Setting :" . $settingName . " Exist";
            SettingSensorManager::updateSettingValueForSensor($deveui, $settingName, $settingValue);
        } else {
            echo "Setting :" . $settingName . " DON'T Exist";
            SettingSensorManager::insertSettingValueForSensor($deveui, $settingName, $settingValue);
        }
    }
    public function updateSettingsSensorAction()
    {

        $user = Auth::getUser();
        if (isset($_GET['deveui'])) {
            $deveui = $_GET['deveui'];
            $device_number = SensorManager::getDeviceNumberFromDeveui($deveui);
        }


        $firstInclinationXThresh = $_POST["firstInclinationXThresh"];
        $firstInclinationYThresh = $_POST["firstInclinationYThresh"];
        $secondInclinationYThresh = $_POST["secondInclinationYThresh"];
        $thirdInclinationYThresh = $_POST["thirdInclinationYThresh"];
        $shockThresh = $_POST["shockThresh"];

        $settingsValuesArr = array(
            "first_inclinationX_thresh" => $firstInclinationXThresh,
            "first_inclinationY_thresh" => $firstInclinationYThresh,
            "second_inclinationY_thresh" => $secondInclinationYThresh,
            "third_inclinationY_thresh" => $thirdInclinationYThresh,
            "shock_thresh" => $shockThresh,

        );

        foreach ($settingsValuesArr as $settingName => $SettingValue) {
            var_dump($settingName);
            $this->checkAndUpdate($deveui, $settingName, $SettingValue);
        }
        if (isset($_POST["activationAlertInclinationFirstLevelX"])) {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "first_inclinationX_thresh", True);
        } else {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "first_inclinationX_thresh", False);
        }
        if (isset($_POST["activationAlertInclinationFirstLevelY"])) {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "first_inclinationY_thresh", True);
        } else {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "first_inclinationY_thresh", False);
        }
        if (isset($_POST["activationAlertInclinationSecondLevelY"])) {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "second_inclinationY_thresh", True);
        } else {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "second_inclinationY_thresh", False);
        }
        if (isset($_POST["activationAlertInclinationThirdLevelY"])) {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "third_inclinationY_thresh", True);
        } else {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "third_inclinationY_thresh", False);
        }
        if (isset($_POST["activationAlertShock"])) {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "shock_thresh", True);
        } else {
            SettingSensorManager::updateActivateSettingForSensor($deveui, "shock_thresh", False);
        }

        if (isset($_POST["activationEmailNotification"])) {
            SettingSensorManager::updateAlertEmailNotification($user->email, True);
        } else {
            SettingSensorManager::updateAlertEmailNotification($user->email, False);
        }

        Flash::addMessage('Mise à jour réussie des paramètres');

        $link_redirect = "/device/" . $device_number . "/info";
        $this->redirect($link_redirect);
        //$this->redirect(Auth::getReturnToPage());
    }

    /**
     * Show the info page for each sensor
     *
     * @return void
     */
    public function infoViewAction()
    {

        $user = Auth::getUser();

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
        $infoArr['lastBatteryLevel'] = $lastBatteryLevel;

        $site = $infoArr["site"];
        //Data map
        $dataMapArr = json_encode($infoArr);

        //get activity of sensors
        $recordRawArr = SensorManager::getRecordsFromDeveui($deveui, 30);
        $date_min_max = SensorManager::getDateMinMaxActivity($deveui);
        $firstActivity = $date_min_max[0];
        $lastActivity = $date_min_max[1];

        //Get alerts of the sensors
        $activeAlertsArr = AlertManager::getActiveAlertsInfoTableForSensor($deveui);
        $processedAlertsArr = AlertManager::getProcessedAlertsInfoTableForSensor($deveui);

        if (empty($activeAlertsArr)) {
            Flash::addAlert("Statut : OK. Pas d'alertes concernant cet équipement", Flash::OK);
        } else {
            Flash::addAlert("Attention, des alertes ont été soulevées", Flash::WARNING);
        }

        //Get inclinometer data
        $positionInstallation = SensorManager::getPositionInstallation($deveui);

        //Direction average inclinometer
        $variationAverageDirectionArr = InclinometerManager::computeAverageDirectionVariationForLast($deveui, -1);

        //If the current sensor is on the same tower that 11, we change the referential
        if ($label_device == "2001006") {
            $variationAverageSpeedDirectionAfterArr = InclinometerManager::applyAverageDirectionReferentialFromSensor("19010011", $variationAverageDirectionArr);
            $variationAverageDirectionArr = json_encode($variationAverageSpeedDirectionAfterArr);
        } else {
            $variationAverageDirectionArr = json_encode($variationAverageDirectionArr);
        }

        //Speed average variation
        $variationAverageSpeedDirectionArr = InclinometerManager::computeAverageDerivativeSpeedVariation($deveui, -1);
        $variationAverageSpeedDirectionArr = json_encode($variationAverageSpeedDirectionArr);

        //Daily variation speed and direction 
        $directionAndSpeedArr = InclinometerManager::combineDirectionAndSpeedVariation($deveui, $time_period = -1, $limit = 30);
        //$directionAndSpeedArr = json_encode($directionAndSpeedArr);

        //absolute variation
        $percentageVariationDayArr = InclinometerManager::computeVariationPercentageAngleForLast($deveui, false, -1);
        $percentageVariationDayArr = json_encode($percentageVariationDayArr);

        $percentageVariationWeekArr = InclinometerManager::computeWeeklyVariationPercentageAngleForLast($deveui, false, -1);
        $percentageVariationWeekArr = json_encode($percentageVariationWeekArr);
        $percentageVariationMonthArr = InclinometerManager::computeMonthlyVariationPercentageAngleForLast($deveui, false, -1);
        $percentageVariationMonthArr = json_encode($percentageVariationMonthArr);
        //Choc
        //Nb choc
        $nbChocDataMonthArr = ChocManager::getNbChocForLast($deveui, 30);
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
        $tempArr = InclinometerManager::getTemperatureRecordsForSensor($deveui, -1);
        $tempArr = json_encode($tempArr);
        $site = SensorManager::getSiteWhereIsInstalled($deveui);
        $weatherDataArr = TemperatureManager::getDataWeatherForSite($deveui, $site);

        $historicalTemperatureDataArr = TemperatureManager::getHistoricalDataForSite($deveui, $site);
        $historicalTemperatureDataArr = json_encode($historicalTemperatureDataArr);
        $allDataWeather = TemperatureManager::getAllDataWeatherForSite($deveui, $site);

        $structure = SensorManager::getStructureWhereIsInstalled($deveui);
        if (isset($structure["longitude"]) && isset($structure["latitude"])) {
            $longitude = $structure["longitude"];
            $latitude = $structure["latitude"];
            //$allDataWeather = json_encode($allDataWeather);

            $link = TemperatureAPI::generateMeteogramLink($site, $latitude, $longitude);
        } else {
            $link = "";
        }

        //Get settings
        //var_dump($deveui);
        $settingArr = SettingSensorManager::findByDeveui($deveui);
        $settingArr = json_encode($settingArr);
        //var_dump($settingArr);

        //Alerts
        $alertsActiveDataArr = AlertManager::getActiveAlertsInfoTable($user->group_name, $deveui);
        $alertsProcessedDataArr = AlertManager::getProcessedAlertsInfoTable($user->group_name, $deveui);

        //Image sensor
        $image_path = SensorManager::getPathImage($deveui);
        //var_dump($directionAndSpeedArr);
        $context = [
            //Sensor
            'deveui' => $deveui,
            'location' => $site,
            'firstActivity' => $firstActivity,
            'lastActivity' => $lastActivity,
            'infoArr' => $infoArr,
            'recordRawArr' => $recordRawArr,
            'positionInstallation' => $positionInstallation,
            'image_path' => $image_path,

            //Inclinometer
            'percentageVariationDayArr' => $percentageVariationDayArr,
            'percentageVariationWeekArr' => $percentageVariationWeekArr,
            'percentageVariationMonthArr' => $percentageVariationMonthArr,
            'variationAverageDirectionArr' => $variationAverageDirectionArr,
            'variationAverageSpeedDirectionArr' => $variationAverageSpeedDirectionArr,
            'directionAndSpeedArr' => $directionAndSpeedArr,

            //Choc
            'nbChocDataMonthArr' => $nbChocDataMonthArr,
            'nbChocDataWeekArr' => $nbChocDataWeekArr,
            'nbChocDataDay' => $nbChocDataDay,
            'powerChocDataMonthArr' => $powerChocDataMonthArr,
            'powerChocDataDayArr' => $powerChocDataDayArr,
            'powerChocDataWeekArr' => $powerChocDataWeekArr,

            //Weather
            //'temperatureArr' => $tempArr,
            'weatherDataArr' => $weatherDataArr,
            'historicalTemperatureDataArr' => $historicalTemperatureDataArr,
            'allDataWeather' => $allDataWeather,
            'link' => $link,

            //Alerts
            'activeAlertsArr' => $activeAlertsArr,
            'processedAlertsArr' => $processedAlertsArr,

            //Map
            'dataMapArray' => $dataMapArr,

            //Setings
            'settingArr' => $settingArr,
        ];


        View::renderTemplate('Sensors/infoDevice.html', $context);
    }

    public function getChartDataNbChocAction()
    {
        if (isset($_POST["deveui"]) && isset($_POST["startDate"]) && isset($_POST["endDate"])) {

            $startDate = $_POST["startDate"];
            $endDate = $_POST["endDate"];
            $deveui = $_POST["deveui"];
            $nbChocData = ChocManager::getNbChocPerDayForSensor($deveui, $startDate, $endDate);


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

    public function getDeviceNumberAction()
    {

        if (isset($_GET["deveui"])) {
            $device_number = SensorManager::getDeviceNumberFromDeveui($_GET["deveui"]);
            echo $device_number;
        }
    }
    /**
     * allow the user to download activity data from a specific sensor
     *
     * @return void
     */
    public function downloadDataAction()
    {

        if (isset($_GET['exportDataFormat']) && isset($_GET['deveui']) && isset($_GET['type'])) {
            $format = $_GET['exportDataFormat'];
            $deveui = $_GET['deveui'];
            $type =  $_GET['type'];

            if ($type == "raw") {
                $this->downloadRawActivityData($deveui, $format);
            } else if ($type == "inclination") {
                $this->downloadInclinationActivityData($deveui, $format);
            } else if ($type == "shock") {
                $this->downloadShockActivityData($deveui, $format);
            } else if ($type == "spectre") {
                $this->downloadSpectreActivityData($deveui, $format);
            }
        }
    }


    private function downloadRawActivityData($deveui, $format)
    {

        //Get activity data
        $recordRawArr = SensorManager::getRecordsFromDeveui($deveui);
        //print_r($recordRawArr);
        if (strcmp($format, "csv") == 0) {
            $timestamp = time();
            $filename = 'Export_raw_data_sensors_' . $timestamp . '.csv';

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
            $filename = 'Export_data_sensors_' . $deveui . '_' . $timestamp . '.xls';

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

    private function downloadInclinationActivityData($deveui, $format)
    {
        $dataArr = InclinometerManager::getActivityData($deveui);

        if (strcmp($format, "csv") == 0) {

            $timestamp = time();
            $filename = 'Export_inclination_data_sensors_' . $timestamp . '.csv';

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
            $filename = 'Export_inclination_data_sensors_' . $deveui . '_' . $timestamp . '.xls';

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

    private function downloadShockActivityData($deveui, $format)
    {
        $dataArr = ChocManager::getActivityData($deveui);
        if (strcmp($format, "csv") == 0) {

            $timestamp = time();
            $filename = 'Export_choc_data_sensors_' . $timestamp . '.csv';

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
            $filename = 'Export_choc_data_sensors_' . $deveui . '_' . $timestamp . '.xls';

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
