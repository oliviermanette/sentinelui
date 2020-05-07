<?php

/*
alertManager.php
Handle the alert CRUD on the database
author : Lirone Samoun

*/

namespace App\Models;

use App\Config;
use App\Utilities;
use App\Models\API\API;
use \App\Models\UserManager;
use \Core\View;
use \App\Mail;
use PDO;

class AlertManager extends \Core\Model
{


    /** Create a new alert on the database from an array data received
     *
     * @param array $dataArr array which contain the data that will serve to add a new alert on the DB
     * @return void
     */
    public static function insert($alert, $status = "ACTIVE")
    {

        if ($alert->type == "inclination") {
            AlertManager::insertInclinationAlert($alert);
        } else if ($alert->type == "shock") {
            AlertManager::insertShockAlert($alert);
        } else if ($alert->type == "event") {
            AlertManager::insertEventAlert($alert);
            SensorManager::updateStatut($alert->deveui, $status);
        }
    }


    private static function insertInclinationAlert($alert)
    {
        $db = static::getDB();

        $sql = "INSERT INTO alerts(id_type_event, deveui, msg, structure_id, status, date_time, valueX, valueY)
        SELECT * FROM
        (SELECT (SELECT id FROM type_alert WHERE type_alert.label LIKE :label),
        :deveui AS deveui, :msg AS msg, :structure_id as structure_id, 1 as status, :date_time as date_time, :valueX as valueX, :valueY as valueY) AS alert_record
        ";


        $stmt = $db->prepare($sql);

        $stmt->bindValue(':structure_id', $alert->equipementId, PDO::PARAM_INT);
        $stmt->bindValue(':deveui', $alert->deveui, PDO::PARAM_STR);
        $stmt->bindValue(':msg', $alert->msg, PDO::PARAM_STR);
        $stmt->bindValue(':valueX', $alert->valueX, PDO::PARAM_STR);
        $stmt->bindValue(':valueY', $alert->valueY, PDO::PARAM_STR);
        $stmt->bindValue(':label', $alert->label, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $alert->dateTime, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "\n NEW ALERT INCLINATION CREATED \n";
            return true;
        } else {
            return false;
        }
    }

    private static function insertShockAlert($alert)
    {
        $db = static::getDB();

        $sql = "INSERT INTO alerts(id_type_event, deveui, msg, structure_id, status, date_time, valueShock)
        SELECT * FROM
        (SELECT (SELECT id FROM type_alert WHERE type_alert.label LIKE :label),
        :deveui AS deveui, :msg AS msg, :structure_id as structure_id, 1 as status, :date_time as date_time, :valueShock as valueShock) AS alert_record
        ";


        $stmt = $db->prepare($sql);

        $stmt->bindValue(':structure_id', $alert->equipementId, PDO::PARAM_INT);
        $stmt->bindValue(':deveui', $alert->deveui, PDO::PARAM_STR);
        $stmt->bindValue(':msg', $alert->msg, PDO::PARAM_STR);
        $stmt->bindValue(':valueShock', $alert->valueShock, PDO::PARAM_STR);
        $stmt->bindValue(':label', $alert->label, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $alert->dateTime, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "\n NEW ALERT SHOCK CREATED \n";
            return true;
        } else {
            return false;
        }
    }

    private static function insertEventAlert($alert)
    {
        $db = static::getDB();

        $sql = "INSERT INTO alerts(id_type_event, deveui, msg, structure_id, status, date_time)
        SELECT * FROM
        (SELECT (SELECT id FROM type_alert WHERE type_alert.label LIKE :label),
        :deveui AS deveui, :msg AS msg,:structure_id as structure_id, 1 as status, :date_time as date_time) AS alert_record
        ";


        $stmt = $db->prepare($sql);

        $stmt->bindValue(':structure_id', $alert->equipementId, PDO::PARAM_INT);
        $stmt->bindValue(':deveui', $alert->deveui, PDO::PARAM_STR);
        $stmt->bindValue(':msg', $alert->msg, PDO::PARAM_STR);
        $stmt->bindValue(':label', $alert->label, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $alert->dateTime, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "\n NEW ALERT EVENT CREATED \n";
            return true;
        } else {
            return false;
        }
    }


    /**
     * Insert type of event in the database
     * @param string $label
     * @return void
     */
    public static function insertTypeEvent($label, $criticality, $description = "")
    {
        $db = static::getDB();
        $sql = "INSERT INTO type_alert (`label`, `criticality`, `description`)
                SELECT * FROM (
                    SELECT
                        :label,
                        :criticality,
                        :description) AS id_type
                WHERE NOT EXISTS
                    (SELECT label FROM type_alert WHERE label = :label)
                LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':label', $label, PDO::PARAM_STR);
        $stmt->bindValue(':criticality', $criticality, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);

        return $stmt->execute();
    }
    /**
     * Create a new alert on the database
     * @param string $label label to attribute for the alert
     * @param string $deveui deveui of the sensor
     * @param datetime $date_time date_time format when the alert occured
     * @param int $structure_d id of the structure where the alert ocurred
     * @param float $value value of the alert
     * @return void
     */
    public function create($label,  $criticality, $msg, $deveui, $date_time, $structure_id, $value)
    {

        //Check if type alert does not exist, otherwise, add it
        AlertManager::insertTypeEvent($label, $criticality, $msg);

        $db = static::getDB();

        $sql = "INSERT INTO alerts(id_type_event, deveui, structure_id, status, date_time, valeur)
        SELECT * FROM
        (SELECT (SELECT id FROM type_alert WHERE type_alert.label LIKE :label),
        :deveui, :structure_id, 1, :date_time, :data_value) AS alert_record
        WHERE NOT EXISTS (
            SELECT date_time, structure_id FROM alerts WHERE date_time = :date_time
            AND structure_id = :structure_id
            )";
        //echo "\n QUERY : $sql";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':data_value', $value, PDO::PARAM_STR);
        $stmt->bindValue(':label', $label, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "\n NEW ALERT CREATED";
            return true;
        } else {
            return false;
        }
    }


    /** Delete an alert from the database
     *
     * @param int $id_alert if of the alert to delete
     * @return void
     */
    public static function delete($id_alert)
    {
        $alert = static::findByID($id_alert);

        if ($alert) {
            $alert->startDelete($id_alert);
            return true;
        }

        return false;
    }

    /** Update a status of an alert from the database
     *
     * @param int $id_alert if of the alert to update
     * @param int $status_alert status (1 or 0)
     * @return void
     */
    public static function updateStatus($id_alert, $status_alert)
    {
        $alert = static::findByID($id_alert);

        if ($alert) {
            $alert->startUpdateStatus($id_alert, $status_alert);
            return true;
        }

        return false;
    }

    /**
     * Start the delete process of an alert
     * @param int $id_alert id alert to delete
     * @return void
     */
    protected function startDelete($id_alert)
    {
        $db = static::getDB();

        $sql = "DELETE FROM alerts WHERE id = :id";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id_alert, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Start the update  process
     * @param int $id_alert id alert to update
     * @param int $status_alert id status (1 or 0)
     * @return void
     */
    protected function startUpdateStatus($id_alert, $status_alert)
    {
        if ($status_alert == 0) {
            $status_alert = 1;
        } else {
            $status_alert = 0;
        }
        $db = static::getDB();

        $sql = "UPDATE alerts
            SET status = :status_alert
            WHERE id = :id;";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id_alert, PDO::PARAM_STR);
        $stmt->bindValue(':status_alert', $status_alert, PDO::PARAM_INT);

        return $stmt->execute();
    }




    /**
     * Get all the active alert from the database
     * alert_id | date_time | label | criticality | name equipement | Ligne HT | Cause | Deveui | Valeur
     * @param string $group_name check alert for a specific group
     * @return void
     */
    public static function getActiveAlertsInfoTable($groupId, $deveui = null, $limit = null)
    {
        $db = static::getDB();

        $query_alerts_data = "SELECT DISTINCT group_name.name, alerts.id AS alert_id, alerts.date_time AS date_time,
        type_alert.label AS label,
        type_alert.criticality AS criticality,
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        site.nom as site,
        alerts.msg AS message,
        (SELECT sensor.device_number FROM sensor WHERE sensor.deveui = alerts.deveui) AS device_number,
        alerts.deveui AS deveui, alerts.status AS status, alerts.valueX as valueX, alerts.valueY as valueY, alerts.valueShock as valueShock
        FROM alerts
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN sensor ON sensor.structure_id = structure.id
        LEFT JOIN sensor_group ON sensor_group.sensor_id = sensor.id
        LEFT JOIN group_name ON (group_name.group_id = sensor_group.groupe_id) 
        LEFT JOIN site ON (site.id = structure.site_id) ";

        if (isset($deveui)) {
            $query_alerts_data .= " WHERE sensor.deveui = :deveui AND group_name.group_id = :groupId
            AND alerts.status = 1";
        } else {
            $query_alerts_data .= "WHERE group_name.group_id = :groupId
            AND alerts.status = 1 ";
        }

        if (isset($limit)) {
            $query_alerts_data .= "LIMIT :limit";
        }

        $stmt = $db->prepare($query_alerts_data);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);
        if (isset($limit)) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        if (isset($deveui)) {
            $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            $resArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resArr;
        }
    }

    /**
     * Get all the active alert from the database for a specific sensor
     * alert_id | date_time | label | criticality | name equipement | Ligne HT | Cause | Deveui | Valeur
     * @param string $group_name check alert for a specific group
     * @return void
     */
    public static function getActiveAlertsInfoTableForSensor($deveui, $limit = null)
    {
        $db = static::getDB();

        $query_alerts_data = "SELECT DISTINCT alerts.id AS alert_id, alerts.date_time AS date_time, alerts.deveui,
        type_alert.label AS label,
        type_alert.criticality AS criticality,
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        alerts.msg AS message,
        alerts.deveui AS deveui, alerts.status AS status, alerts.valueX as valueX, alerts.valueY as valueY, alerts.valueShock as valueShock
        FROM alerts
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        WHERE alerts.status = 1
        AND alerts.deveui = :deveui";

        if (isset($limit)) {
            $query_alerts_data .= "LIMIT :limit";
        }

        $stmt = $db->prepare($query_alerts_data);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        if (isset($limit)) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        if ($stmt->execute()) {
            $resArr = $stmt->fetchAll();
            return $resArr;
        }
    }

    /**
     * Get all the processed alert from the database for a specific sensor
     * alert_id | date_time | label | criticality | name equipement | Ligne HT | Cause | Deveui | Valeur
     * @param string $group_name check alert for a specific group
     * @return void
     */
    public static function getProcessedAlertsInfoTableForSensor($deveui)
    {
        $db = static::getDB();

        $query_alerts_data = "SELECT DISTINCT alerts.id AS alert_id, alerts.date_time AS date_time, alerts.deveui,
        type_alert.label AS label,
        type_alert.criticality AS criticality,
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        alerts.msg AS message,
        alerts.deveui AS deveui, alerts.status AS status, alerts.valueX as valueX, alerts.valueY as valueY, alerts.valueShock as valueShock
        FROM alerts
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        WHERE alerts.status = 1
        AND alerts.deveui = :deveui ";

        $stmt = $db->prepare($query_alerts_data);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $resArr = $stmt->fetchAll();
            return $resArr;
        }
    }
    /**
     * Get all the processed alert from the database
     * alert_id | date_time | label | criticality | name equipement | Ligne HT | Cause | Deveui | Valeur
     * @param string $group_name check alert for a specific group
     * @return void
     */
    public static function getProcessedAlertsInfoTable($groupId)
    {
        $db = static::getDB();

        $query_alerts_data = "SELECT alerts.id AS alert_id, alerts.date_time AS date_time, type_alert.label AS label,
        type_alert.criticality AS criticality,
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        site.nom as site,
        alerts.msg AS message,
        (SELECT sensor.device_number FROM sensor WHERE sensor.deveui = alerts.deveui) AS device_number,
        alerts.deveui AS deveui, alerts.status AS status, alerts.valueX as valueX, alerts.valueY as valueY, alerts.valueShock as valueShock
        FROM alerts
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN sensor ON sensor.structure_id = structure.id
        LEFT JOIN sensor_group ON sensor_group.sensor_id = sensor.id
        LEFT JOIN group_name ON (group_name.group_id = sensor_group.groupe_id) 
        LEFT JOIN site ON (site.id = structure.site_id)
        WHERE group_name.group_id = :groupId
        AND alerts.status = 0";

        $stmt = $db->prepare($query_alerts_data);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $resArr = $stmt->fetchAll();
            return $resArr;
        }
    }

    /**
     * Get the number of alerts for a specific structure
     * @param int $structure_id structure id for checking the number of alert
     * @return array
     */
    public function getNumberActiveAlertsOnStructure($structure_id)
    {
        $db = static::getDB();

        $sql_nb_active_alert = "SELECT COUNT(*) as nb_active_alerts
        FROM alerts
        WHERE status = 1
        AND structure_id = :structure_id
        ";

        $stmt = $db->prepare($sql_nb_active_alert);
        $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $results = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($results)) {
                $nb_active_alerts = $results["nb_active_alerts"];

                return $nb_active_alerts;
            } else {
                return null;
            }
        }
    }
    /**
     * Get the number of alerts for a specific group
     * @param string $group_name group for checking the number of alert
     * @return array
     */
    public static function getNumberActiveAlertsForGroup($groupId)
    {
        $db = static::getDB();

        $sql_nb_active_alert = "SELECT DISTINCT count(*) as nb_active_alerts
        FROM alerts
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN sensor ON sensor.structure_id = structure.id
        LEFT JOIN sensor_group ON sensor_group.sensor_id = sensor.id
        LEFT JOIN group_name ON (group_name.group_id = sensor_group.groupe_id) 
        WHERE alerts.status = 1
        AND group_name.group_id = :groupId
        GROUP BY alerts.id";

        $stmt = $db->prepare($sql_nb_active_alert);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $results = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($results)) {
                $nb_active_alerts = $results["nb_active_alerts"];

                return $nb_active_alerts;
            } else {
                return null;
            }
        }
    }

    /**
     * Get the number of inactive alerts for a specific group
     * @param int $group_name group to check
     * @return array
     */
    public function getNumberInactiveAlertsForGroup($groupId)
    {
        $db = static::getDB();

        $sql_nb_inactive_alert = "SELECT count(*) as nb_inactive_alerts
        FROM alerts
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN sensor ON sensor.structure_id = structure.id
        LEFT JOIN sensor_group ON sensor_group.sensor_id = sensor.id
        LEFT JOIN group_name ON (group_name.group_id = sensor_group.groupe_id) 
        WHERE alerts.status = 0
        AND group_name.group_id = :groupId
        ";

        $stmt = $db->prepare($sql_nb_inactive_alert);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $results = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($results)) {
                $nb_inactive_alerts = $results["nb_inactive_alerts"];
                return $nb_inactive_alerts;
            } else {
                return null;
            }
        }
    }
    /**
     * Get the number of inactive alerts for a specific structure
     * @param int $structure_id structure id for checking the number of alert
     * @return array
     */
    public function getNumberInactiveAlerts($structure_id)
    {
        $db = static::getDB();

        $sql_nb_inactive_alert = "SELECT COUNT(*) as nb_inactive_alerts
        FROM alerts
        WHERE status = 0
        AND structure_id = :structure_id
        ";

        $stmt = $db->prepare($sql_nb_inactive_alert);
        $stmt->bindValue(':structure_id', $structure_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $results = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($results)) {
                $nb_inactive_alerts = $results["nb_inactive_alerts"];

                return $nb_inactive_alerts;
            } else {
                return null;
            }
        }
    }
    /**
     * Send alert to the user specified
     *
     * @param string $email The email address
     * @param string $phone_number The phone number
     *
     * @return void
     */
    public static function sendAlert($alert, $groupId)
    {
        if (is_null($alert->triggerValues)) {
            AlertManager::sensorAlert($alert, $groupId);
        } else {
            AlertManager::structureAlert($alert, $groupId);
        }
    }

    private static function sensorAlert($alert, $groupId)
    {

        $msg = $alert->getProperMessageFromLabel();
        $equipementInfoArr = EquipementManager::getEquipementFromId($alert->equipementId);
        $equipementName = $equipementInfoArr["equipement"];
        $ligneHT = $equipementInfoArr["ligneHT"];
        $device_number = $alert->device_number;
        $region = EquipementManager::getSiteLocation($alert->equipementId);
        //Find all users that want to receive alerts
        $users = UserManager::findToSendAlerts($groupId);
        $users_flod = UserManager::findToSendAlerts(73); //Flod
        foreach ($users_flod as $user) {
            array_push($users, $user);
        }
        foreach ($users as $user) {
            $email = $user["email"];
            $phone_number = $user["phone_number"];
            $firstName = $user["first_name"];
            $last_name = $user["last_name"];
            $company = $user["company"];
            echo "\n Envoie du mail à " . $firstName . "\n";

            $deveui = $alert->deveui;
            $sensorName = SensorManager::getDeviceNumberFromDeveui($deveui);
            $url = 'https://' . $_SERVER['HTTP_HOST'] . '/device/' . $sensorName . '/info#alertsStructure';

            $dateTime = explode(" ", $alert->dateTime);
            $date = date('d/m/Y', strtotime($dateTime[0]));
            $time = $dateTime[1];
            $context = [
                "firstName" => $firstName,
                "dateEventOccured" => $date,
                "timeEventOccured" => $time,
                "sensorName" => $device_number,
                "region" => $region,
                "equipement" => $equipementName,
                "label" => $alert->label,
                "msg" => $msg,
                "url" => $url,
            ];
            $text = View::getTemplate('Alerts/alertSensor_email_view.txt', $context);
            $html = View::getTemplate('Alerts/alertSensor_email_view.html', $context);

            $title =  '[capteur] Nouvelle alerte sur le capteur ' . $device_number . ' !';
            Mail::send($email, $title, $text, $html);
        }
    }

    private static function structureAlert($alert, $groupId)
    {
        $equipementInfoArr = EquipementManager::getEquipementFromId($alert->equipementId);
        $equipementName = $equipementInfoArr["equipement"];
        $ligneHT = $equipementInfoArr["ligneHT"];
        $region = EquipementManager::getSiteLocation($alert->equipementId);

        //Find all users that want to receive alerts
        $users = UserManager::findToSendAlerts($groupId);
        $users_flod = UserManager::findToSendAlerts(73); //Flod
        //Add the admin flod to the users array to send message
        foreach ($users_flod as $user) {
            array_push($users, $user);
        }

        //Get info alerts
        $typeAlert = $alert->type;

        foreach ($users as $user) {

            $email = $user["email"];
            $phone_number = $user["phone_number"];
            $firstName = $user["first_name"];
            $last_name = $user["last_name"];
            $company = $user["company"];
            echo "\n Envoie du mail à " . $firstName . "\n";
            $device_number = $alert->device_number;
            $url = "https://" . $_SERVER['HTTP_HOST'] . "/device/" . $device_number . "/info#alertsStructure";

            $dateTime = explode(" ", $alert->dateTime);
            $date = date('d/m/Y', strtotime($dateTime[0]));
            $time = $dateTime[1];

            if ($typeAlert == "inclination") {
                $context = [
                    "firstName" => $firstName,
                    "dateEventOccured" => $date,
                    "timeEventOccured" => $time,
                    "sensorName" => $device_number,
                    "region" => $region,
                    "equipement" => $equipementName,
                    "label" => $alert->label,
                    "thresh" => $alert->thresh,
                    "valueX" => $alert->valueX,
                    "valueY" => $alert->valueY,
                    "url" => $url,
                ];
                //print_r($this->label);
                $text = View::getTemplate('Alerts/alertStructureInclination_email_view.txt', $context);
                $html = View::getTemplate('Alerts/alertStructureInclination_email_view.html', $context);
            } else if ($typeAlert == "shock") {
                $context = [
                    "firstName" => $firstName,
                    "dateEventOccured" => $date,
                    "timeEventOccured" => $time,
                    "sensorName" => $device_number,
                    "region" => $region,
                    "equipement" => $equipementName,
                    "label" => $alert->label,
                    "valueShock" => $alert->valueShock,
                    "url" => $url,
                ];
                //print_r($this->label);
                $text = View::getTemplate('Alerts/alertStructureShock_email_view.txt', $context);
                $html = View::getTemplate('Alerts/alertStructureShock_email_view.html', $context);
            }

            $title =  'Capteur' . $device_number . ' - Nouvelle alerte sur la structure ' . $equipementName . ' !';
            Mail::send($email, $title, $text, $html);
        }
    }
    /**
     * Find a alert by ID
     *
     * @param string $id The alert ID
     *
     * @return mixed Alert object if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT alerts.id AS alert_id, alerts.date_time AS date_time,  (SELECT type_alert.label FROM type_alert WHERE type_alert.id = alerts.id_type_event) AS label,
        type_alert.criticality AS criticality,
        structure_id,
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        alerts.msg AS message,
        (SELECT sensor.device_number FROM sensor WHERE sensor.deveui = alerts.deveui) AS device_number,
        alerts.deveui AS deveui, alerts.status AS status
        FROM alerts
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        WHERE alerts.id = :id ';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function findByEvent($event)
    {
    }

    public function getAlertsFromApiForDevice($device_id, $state = "open",  $acknowledged = "true")
    {

        $url = "https://api.objenious.com/v1/alerts?device_id=" . $device_id . "&state=" . $state . "&acknowledged=" . $acknowledged;
        $results_api = API::CallAPI("GET", $url);
        $alerts_data = $results_api["alerts"];

        return $alerts_data;
    }

    public function getAlertsFromApiForGroup($group, $state = "open",  $acknowledged = "true")
    {

        $url = "https://api.objenious.com/v1/alerts?group=" . $group . "&state=" . $state . "&acknowledged=" . $acknowledged;
        $results_api = API::CallAPI("GET", $url);
        $alerts_data = $results_api["alerts"];

        return $alerts_data;
    }

    public static function getAllAlertsFromAPI()
    {
        $results_api = API::CallAPI("GET", "https://api.objenious.com/v1/alerts");
        $alerts_data = $results_api["alerts"];

        return $alerts_data;
    }
}
