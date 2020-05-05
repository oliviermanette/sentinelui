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
        $structure_id = $sensor["structure_id"];

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
            'structure_id' => $structure_id,
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

        if (isset($_POST['deveui'])) {
            $deveui = $_POST['deveui'];
        }
        if (isset($_POST['time_data'])) {
            $timeDisplayData = $_POST['time_data'];
        }

        if ($timeDisplayData == "day") {
            $nb_choc = ChocManager::getNbChocPerDayForSensor($deveui);
        } else if ($timeDisplayData == "week") {
            $nb_choc = ChocManager::getNbChocPerWeekForSensor($deveui);
        } else if ($timeDisplayData == "month") {
            $nb_choc = ChocManager::getNbChocPerMonthForSensor($deveui);
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

        if (isset($_POST['deveui'])) {
            $deveui = $_POST['deveui'];
        }
        if (isset($_POST['time_data'])) {
            $timeDisplayData = $_POST['time_data'];
        }

        if ($timeDisplayData == "day") {
            $nb_choc = ChocManager::getPowerChocPerDayForSensor($deveui);
        } else if ($timeDisplayData == "week") {
            $nb_choc = ChocManager::getPowerChocPerWeekForSensor($deveui);
        } else if ($timeDisplayData == "month") {
            $nb_choc = ChocManager::getPowerChocPerMonthForSensor($deveui);
        }

        print json_encode($nb_choc);
    }

    private function getChocDataChart($deveui, $startDate = null, $endDate = null)
    {
        $sensor = SensorManager::getSensorInfo($deveui);
        $device_number = $sensor["device_number"];
        $transmission_line_name = $sensor["transmission_line_name"];
        $structure_name = $sensor["structure_name"];
        $structure_id = $sensor["structure_id"];

        if (!empty($startDate) && !empty($endDate)) {
            $nb_choc_per_day = ChocManager::getNbChocPerDayForDates($deveui, $startDate, $endDate);
            $power_choc_per_day = ChocManager::getPowerChocPerDayForDates($deveui, $startDate, $endDate);
            //$angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui, $startDate, $endDate);
        } else {
            $nb_choc_per_day = ChocManager::getNbChocPerDayForSensor($deveui);
            $power_choc_per_day = ChocManager::getPowerChocPerDayForSensor($deveui);
            //$angleDataXYZ = InclinometerManager::getAngleXYZPerDayForSensor($deveui);
        }

        return array(

            'deveui' => $deveui,
            'device_number' => $device_number,
            'structure_name' => $structure_name,
            'structure_id' => $structure_id,
            'nb_choc_per_day' => $nb_choc_per_day,
            //'angleXYZ_per_day' => $angleDataXYZ,
            'power_choc_per_day' => $power_choc_per_day,
        );
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
        $user = Auth::getUser();
        $allStructureData = array();

        $searchSpecificEquipement = true;
        $searchByDate = false;
        if (!empty($_POST['siteID'])) {
            $siteId = $_POST['siteID'];
        }
        if (empty($_POST['deveui'])) {
            $searchSpecificEquipement = false;
        }
        if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {
            $startDate = $_POST['startDate'];
            $endDate = $_POST['endDate'];
            $searchByDate = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

        if ($searchSpecificEquipement) {
            $deveui = $_POST['deveui'];
            $sensor = SensorManager::getSensorInfo($deveui);

            $dataArr = $this->getChocDataChart($deveui, $startDate, $endDate);

            $allStructureData["equipement_0"] = $dataArr;
        } else {
            $sensorsInfoArr = SensorManager::getAllSensorsInfoFromSite($siteId, $user->group_id);
            $count = 0;
            foreach ($sensorsInfoArr as $sensor) {
                $index_array = "equipement_" . $count;
                $deveui = $sensor["deveui"];
                $dataArr = $this->getChocDataChart($deveui, $startDate, $endDate);

                $allStructureData[$index_array] = $dataArr;

                $count += 1;
            }
        }

        print json_encode($allStructureData);
    }
}
