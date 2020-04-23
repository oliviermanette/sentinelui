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


    private function getChocData($deveui)
    {
        $sensor = SensorManager::getSensorInfo($deveui);
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

        return array(
            'deveui' => $deveui,
            'status' => $status,
            'device_number' => $device_number,
            'ligneHT' => $transmission_line_name,
            'structure_name' => $structure_name,
            'lastDate' => $lastdate,
            'nb_choc_received_today' => $nb_choc_received_today,
            'lastChocPower' => $last_choc_power,
            //'startDate' => $startDate, 'endDate' => $endDate
        );
    }
    public function getResultsFromChocFormAction()
    {
        $user = Auth::getUser();
        $allStructureData = array();

        $searchSpecificSensor = true;
        if (isset($_POST['siteID'])) {
            $siteId = $_POST['siteID'];
        }

        if (empty($_POST['deveui'])) {
            $searchSpecificSensor = false;
        }
        $startDate = "";
        $endDate = "";
        if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {
            $startDate = $_POST['startDate'];
            $endDate = $_POST['endDate'];
            $searchByDate = true;
        }

        if ($searchSpecificSensor) {
            $deveui = $_POST['deveui'];

            $dataArr = $this->getChocData($deveui);

            $allStructureData[0] = $dataArr;
        } else {

            //Loop over all sensors in a specific site
            $sensorsInfoArr = SensorManager::getAllSensorsInfoFromSite($siteId, $user->group_id);
            $count = 0;
            foreach ($sensorsInfoArr as $sensor) {

                $index_array = "equipement_" . $count;
                $deveui = $sensor["deveui"];
                $dataArr = $this->getChocData($deveui);
                $allStructureData[$index_array] = $dataArr;

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
