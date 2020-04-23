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

 */


class ControllerChocData extends Authenticated
{

    public $loggedin;

    /**
     * Show the index for When the user want to retrieve choc data from the form
     * /search-choc
     *
     * @return void
     */
    public function searchChocViewAction()
    {
        $user = Auth::getUser();
        $group_name = $user->getGroupName();

        $choc_data_arr = ChocManager::getAllChocDataForGroup($group_name);
        $all_equipment = EquipementManager::getEquipements($user->group_id);
        $all_site = SiteManager::getSites($user->group_id);
        $date_min_max = RecordManager::getDateMinMaxFromRecord();

        $min_date = $date_min_max[0];
        $max_date = $date_min_max[1];

        View::renderTemplate('Chocs/index.html', [
            'all_site'    => $all_site,
            'all_equipment' => $all_equipment,
            'min_date' => $min_date,
            'max_date' => $max_date,
            'choc_data_array' => $choc_data_arr,
        ]);
    }



    /**
     * When the user perform the search through the form, display basic infos
     * sensor_id, device_number ,ligneHT, equipement, equipementId
     * last message received date, lastScore, nb_choc_received_today,
     * lastChocPower, temperature
     *
     * @return void
     */
    /*
    public function getResultsFromChocFormAction()
    {
        $user = Auth::getUser();
        $allStructureData = array();

        $recordManager = new RecordManager();
        $chocManager = new ChocManager();
        $siteManager = new SiteManager();
        $inclinometerManager = new InclinometerManager();
        $sensorManager = new SensorManager();

        $searchSpecificEquipement = false;
        if (isset($_POST['siteID'])) {
            $siteID = $_POST['siteID'];
        }

        if (!isset($_POST['equipmentID'])) {
            $equipement_id = $_POST['equipmentID'];
            $searchSpecificEquipement = true;
        }
        $startDate = "";
        $endDate = "";
        if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {
            $startDate = $_POST['startDate'];
            $endDate = $_POST['endDate'];
            $searchByDate = true;
        }

        $searchSpecificEquipement = False;

        if ($searchSpecificEquipement) {
            $equipementInfo = EquipementManager::getEquipementFromId($equipement_id);

            $equipement_pylone = $equipementInfo['equipement'];
            $equipement_name = $equipementInfo['ligneHT'];
            //Get the sensor ID on the associated structure
            $sensor_id = EquipementManager::getSensorIdOnEquipement($equipement_id);
            //Get the device number
            $device_number = SensorManager::getDeviceNumberFromSensorId($sensor_id);

            //Get the last date where the sensor received
            $lastdate = RecordManager::getDateLastReceivedData($equipement_id);
            //Get the status of the device
            $status = SensorManager::getStatusDevice($sensor_id);
            //Get the choc data
            $choc_power_data = ChocManager::getLastChocPowerValueForSensor($sensor_id);
            if (!empty($choc_power_data)) {
                $last_choc_power = $choc_power_data[0]['power'];
                $last_choc_date = $choc_power_data[0]['date'];
            } else {
                $last_choc_power = 0;
            }

            $nb_choc_received_today = $chocManager->getNbChocReceivedTodayForSensor($sensor_id);
            $nb_choc_received_today = $nb_choc_received_today['nb_choc_today'];

            $allStructureData[0] = array(
                'sensor_id' => $sensor_id,
                'status' => $status,
                'device_number' => $device_number,
                'ligneHT' => $equipement_name,
                'equipement' => $equipement_pylone,
                'equipementId' => $equipement_id,
                'lastDate' => $lastdate,
                'nb_choc_received_today' => $nb_choc_received_today,
                'lastChocPower' => $last_choc_power,
                'startDate' => $startDate, 'endDate' => $endDate
            );
        } else {

            $equipements_site = EquipementManager::getEquipementsBySiteId($siteID, $user->group_id);

            $count = 0;
            foreach ($equipements_site as $equipement) {
                $index_array = "equipement_" . $count;
                //Get equipement data
                $equipement_id = $equipement['equipement_id'];
                $equipement_pylone = $equipement['equipement'];
                $equipement_name = $equipement['ligneHT'];

                //Get the sensor ID on the associated structure
                $sensorsDeveuiArr = SensorManager::getDeveuiFromEquipement($equipement_id);
                //Get the device number
                $device_number = $sensorManager->getDeviceNumberFromSensorId($sensor_id);

                //Get the last date where the sensor received
                $lastdate = $recordManager->getDateLastReceivedData($equipement_id);
                //Get the status of the device
                $status = $sensorManager->getStatusDevice($sensor_id);
                //Get the choc data
                $choc_power_data = $chocManager->getLastChocPowerValueForSensor($sensor_id);
                if (!empty($choc_power_data)) {
                    $last_choc_power = $choc_power_data[0]['power'];
                    $last_choc_date = $choc_power_data[0]['date'];
                } else {
                    $last_choc_power = 0;
                }

                $nb_choc_received_today = $chocManager->getNbChocReceivedTodayForSensor($sensor_id);
                $nb_choc_received_today = $nb_choc_received_today['nb_choc_today'];

                $allStructureData[$index_array] = array(
                    'sensor_id' => $sensor_id,
                    'status' => $status,
                    'device_number' => $device_number,
                    'ligneHT' => $equipement_name,
                    'equipement' => $equipement_pylone,
                    'equipementId' => $equipement_id,
                    'lastDate' => $lastdate,
                    'nb_choc_received_today' => $nb_choc_received_today,
                    'lastChocPower' => $last_choc_power,
                    'startDate' => $startDate, 'endDate' => $endDate
                );

                $count += 1;
            }
        }

        View::renderTemplate('Chocs/viewDataChocCards.html', [
            'all_structure_data' => $allStructureData,
            'user' => $user,
            'site' => $siteID,
        ]);
    }
*/
    public function getResultsFromChocFormAction()
    {
        $user = Auth::getUser();
        $allStructureData = array();

        $searchSpecificSensor = false;
        if (isset($_POST['siteID'])) {
            $siteId = $_POST['siteID'];
        }

        if (!isset($_POST['deveui'])) {
            $deveui = $_POST['deveui'];
            $searchSpecificSensor = true;
        }
        $startDate = "";
        $endDate = "";
        if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {
            $startDate = $_POST['startDate'];
            $endDate = $_POST['endDate'];
            $searchByDate = true;
        }

        if ($searchSpecificSensor) {
            //TODO
        } else {

            //Loop over all sensors in a specific site
            $sensorsInfoArr = SensorManager::getAllSensorsInfoFromSite($siteId, $user->group_id);
            $count = 0;
            foreach ($sensorsInfoArr as $sensor) {

                $index_array = "equipement_" . $count;
                $deveui = $sensor["deveui"];
                $device_number = $sensor["device_number"];
                $transmission_line_name = $sensor["transmission_line_name"];
                $structure_name = $sensor["structure_name"];


                $lastdate = SensorManager::getLastDataReceivedData($deveui);
                $status = SensorManager::getStatusDevice($deveui);

                $choc_power_data = ChocManager::getLastChocPowerValueForSensor($deveui);

                if (is_null($choc_power_data)) {
                    $last_choc_power = 0;
                } else {
                    $last_choc_power = $choc_power_data['power'];
                    $last_choc_date = $choc_power_data['date'];
                }

                $nb_choc_received_today = ChocManager::getNbChocReceivedTodayForSensor($deveui);
                $nb_choc_received_today = $nb_choc_received_today['nb_choc_today'];

                $allStructureData[$index_array] = array(
                    'deveui' => $deveui,
                    'status' => $status,
                    'device_number' => $device_number,
                    'ligneHT' => $transmission_line_name,
                    'structure_name' => $structure_name,
                    'lastDate' => $lastdate,
                    'nb_choc_received_today' => $nb_choc_received_today,
                    'lastChocPower' => $last_choc_power,
                    'startDate' => $startDate, 'endDate' => $endDate
                );

                $count += 1;
            }
        }

        View::renderTemplate('Chocs/viewDataChocCards.html', [
            'all_structure_data' => $allStructureData,
        ]);
    }

    /**
     *Get all the chart data corresponding to choc frequencies
     *
     * @return void
     */
    public function getChartChocFrequenciesAction()
    {
        $equipementManager = new EquipementManager();
        $chocManager = new ChocManager();

        if (isset($_POST['equipementID'])) {
            $equipementID = $_POST['equipementID'];
        }
        if (isset($_POST['time_data'])) {
            $timeDisplayData = $_POST['time_data'];
        }

        $sensor_id = $equipementManager->getSensorIdOnEquipement($equipementID);

        if ($timeDisplayData == "day") {
            $nb_choc = $chocManager->getNbChocPerDayForSensor($sensor_id);
        } else if ($timeDisplayData == "week") {
            $nb_choc = $chocManager->getNbChocPerWeekForSensor($sensor_id);
        } else if ($timeDisplayData == "month") {
            $nb_choc = $chocManager->getNbChocPerMonthForSensor($sensor_id);
        }

        print json_encode($nb_choc);
    }

    /**
     *Get all the chart data corresponding to the power of choc
     *
     * @return void
     */
    public function getChartPowerChocFrequenciesAction()
    {
        $equipementManager = new EquipementManager();
        $chocManager = new ChocManager();

        if (isset($_POST['equipementID'])) {
            $equipementID = $_POST['equipementID'];
        }
        if (isset($_POST['time_data'])) {
            $timeDisplayData = $_POST['time_data'];
        }

        $sensor_id = $equipementManager->getSensorIdOnEquipement($equipementID);

        if ($timeDisplayData == "day") {
            $nb_choc = $chocManager->getPowerChocPerDayForSensor($sensor_id);
        } else if ($timeDisplayData == "week") {
            $nb_choc = $chocManager->getPowerChocPerWeekForSensor($sensor_id);
        } else if ($timeDisplayData == "month") {
            $nb_choc = $chocManager->getPowerChocPerMonthForSensor($sensor_id);
        }

        print json_encode($nb_choc);
    }


    /**
     * Get all the chart data corresponding to choc
     * number of shocks per day
     * Shock power per day
     * Angle of each sensor per day
     *
     * @return void
     */
    public function getChartsChocAction()
    {
        $group_name = $_SESSION['group_name'];

        $equipementManager = new EquipementManager();
        $inclinometerManager = new InclinometerManager();
        $chocManager = new ChocManager();

        $searchSpecificEquipement = false;
        $searchByDate = false;
        if (!empty($_POST['siteID'])) {
            $siteID = $_POST['siteID'];
        }
        if (!empty($_POST['equipmentID'])) {
            $equipement_id = $_POST['equipmentID'];
            $searchSpecificEquipement = true;
        }
        if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {
            $startDate = $_POST['startDate'];
            $endDate = $_POST['endDate'];
            $searchByDate = true;
        }

        //Attention à la date valide (inferieur data d'activité et installation)

        if ($searchSpecificEquipement) {

            $equipementInfo = $equipementManager->getEquipementFromId($equipement_id);

            $equipement_pylone = $equipementInfo['equipement'];
            $equipement_name = $equipementInfo['ligneHT'];
            #Retrieve the sensor id
            $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
            $deveui = EquipementManager::getDeveuiSensorOnEquipement($equipement_id);
            if ($searchByDate) {
                $nb_choc_per_day = ChocManager::getNbChocPerDayForDates($deveui, $startDate, $endDate);
                $power_choc_per_day = ChocManager::getPowerChocPerDayForDates($deveui, $startDate, $endDate);
                $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui, $startDate, $endDate);
            } else {
                $nb_choc_per_day = $chocManager->getNbChocPerDayForSensor($sensor_id);
                $power_choc_per_day = $chocManager->getPowerChocPerDayForSensor($sensor_id);
                $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui);
            }

            $allStructureData["equipement_0"] = array(

                'deveui' => $deveui,
                'sensor_id' => $sensor_id,
                'equipement_name' => $equipement_pylone,
                'equipementId' => $equipement_id,
                'nb_choc_per_day' => $nb_choc_per_day,
                'angleXYZ_per_day' => $angleDataXYZ,
                'power_choc_per_day' => $power_choc_per_day,
            );
        } else {
            $equipements_site = $equipementManager->getEquipementsBySiteId($siteID, $group_name);
            $allStructureData = array();
            $count = 0;
            foreach ($equipements_site as $equipement) {
                $index_array = "equipement_" . $count;

                $equipement_id = $equipement['equipement_id'];
                $equipement_pylone = $equipement['equipement'];
                $equipement_name = $equipement['ligneHT'];

                $equipement_id = $equipements_site[$count]['equipement_id'];
                $deveui = EquipementManager::getDeveuiSensorOnEquipement($equipement_id);
                #Retrieve the sensor id
                $sensor_id = $equipementManager->getSensorIdOnEquipement($equipement_id);
                //print_r($equipement_id);
                if ($searchByDate) {
                    $nb_choc_per_day = ChocManager::getNbChocPerDayForDates($deveui, $startDate, $endDate);
                    $power_choc_per_day = ChocManager::getPowerChocPerDayForDates($deveui, $startDate, $endDate);
                    $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui, $startDate, $endDate);
                } else {
                    $nb_choc_per_day = $chocManager->getNbChocPerDayForSensor($sensor_id);
                    $power_choc_per_day = $chocManager->getPowerChocPerDayForSensor($sensor_id);
                    //Get inclinometer data angle
                    $angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui);
                }

                $allStructureData[$index_array] = array(
                    'sensor_id' => $sensor_id,
                    'equipement_name' => $equipement_pylone,
                    'equipementId' => $equipement_id,
                    'nb_choc_per_day' => $nb_choc_per_day,
                    'angleXYZ_per_day' => $angleDataXYZ,
                    'power_choc_per_day' => $power_choc_per_day,
                );

                $count += 1;
            }
        }

        print json_encode($allStructureData);
    }
}
