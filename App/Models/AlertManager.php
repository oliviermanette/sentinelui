<?php

/*
alertManager.php
Handle the alert CRUD on the database
author : Lirone Samoun

*/

namespace App\Models;

use App\Config;
use App\Utilities;
use App\Controllers\ControllerDataObjenious;
use \App\Models\UserManager;
use PDO;

class AlertManager extends \Core\Model
{

    /**
     * constructor
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /** Create a new alert on the database from an array data received
     *
     * @param array $dataArr array which contain the data that will serve to add a new alert on the DB
     * @return void  
     */
    public function createFromArr($dataArr)
    {
        $label = $dataArr["label"];
        $deveui = $dataArr["deveui"];
        $date_time =  $dataArr["date_time"];
        $structure_id = $dataArr["equipement_id"];
        $value = $dataArr["value"];

        //Check if type alert does not exist, otherwise, add it
        AlertManager::insertTypeEvent($label);

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
    /**
     * Create a new alert on the database
     * @param string $label label to attribute for the alert
     * @param string $deveui deveui of the sensor
     * @param datetime $date_time date_time format when the alert occured
     * @param int $structure_d id of the structure where the alert ocurred 
     * @param float $value value of the alert
     * @return void  
     */
    public function create($label, $deveui, $date_time, $structure_id, $value)
    {

        //Check if type alert does not exist, otherwise, add it
        AlertManager::insertTypeEvent($label);

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
    public function delete($id_alert)
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
    public function updateStatus($id_alert, $status_alert)
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
     * Insert type of event in the database
     * @param string $label 
     * @return void
     */
    public static function insertTypeEvent($label)
    {
        $db = static::getDB();

        $sql = "INSERT INTO type_alert (label)
        SELECT :label
        WHERE NOT EXISTS (
            SELECT label FROM type_alert WHERE label = :label
        ) LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':label', $label, PDO::PARAM_STR);

        return $stmt->execute();
    }

   
    /**
     * Get all the active alert from the database
     * alert_id | date_time | label | criticality | name equipement | Ligne HT | Cause | Deveui | Valeur
     * @param string $group_name check alert for a specific group 
     * @return void
     */
    public static function getActiveAlertsInfoTable($group_name, $limit = null)
    {
        $db = static::getDB();

        $query_alerts_data = "SELECT alerts.id AS alert_id, alerts.date_time AS date_time, 
        type_alert.label AS label, 
        type_alert.criticality AS criticality, 
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        alerts.cause AS cause, 
        (SELECT sensor.device_number FROM sensor WHERE sensor.deveui = alerts.deveui) AS device_number,
         alerts.deveui AS deveui, alerts.status AS status, alerts.valeur
        FROM alerts 
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN group_site ON (group_site.site_id = site.id)
        LEFT JOIN group_name ON (group_name.group_id = group_site.group_id)
        WHERE group_name.name = :group_name
        AND alerts.status = 1 ";

        if (isset($limit)) {
            $query_alerts_data .= "LIMIT :limit";
        }

        $stmt = $db->prepare($query_alerts_data);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
        if (isset($limit)) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        if ($stmt->execute()) {
            $resArr = $stmt->fetchAll();
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

        $query_alerts_data = "SELECT DISTINCT alerts.id AS alert_id, alerts.date_time AS date_time, sensor.device_number, sensor.deveui,
        type_alert.label AS label, 
        type_alert.criticality AS criticality, 
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        alerts.cause AS cause, 
        (SELECT sensor.device_number FROM sensor WHERE sensor.deveui = alerts.deveui) AS device_number,
         alerts.deveui AS deveui, alerts.status AS status, alerts.valeur
        FROM alerts 
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN record ON (record.structure_id = structure.id)
        LEFT JOIN sensor ON (sensor.id = record.sensor_id)
        WHERE alerts.status = 1
        AND sensor.deveui LIKE :deveui
         ";

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

        $query_alerts_data = "SELECT DISTINCT alerts.id AS alert_id, alerts.date_time AS date_time, sensor.device_number, sensor.deveui,
        type_alert.label AS label, 
        type_alert.criticality AS criticality, 
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        alerts.cause AS cause, 
        (SELECT sensor.device_number FROM sensor WHERE sensor.deveui = alerts.deveui) AS device_number,
         alerts.deveui AS deveui, alerts.status AS status, alerts.valeur
        FROM alerts 
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN record ON (record.structure_id = structure.id)
        LEFT JOIN sensor ON (sensor.id = record.sensor_id)
        WHERE alerts.status = 0
        AND sensor.deveui LIKE :deveui";

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
    public function getProcessedAlertsInfoTable($group_name)
    {
        $db = static::getDB();

        $query_alerts_data = "SELECT alerts.id AS alert_id, alerts.date_time AS date_time, type_alert.label AS label, 
        type_alert.criticality AS criticality, 
        structure.nom AS equipement_name, structure.transmision_line_name AS ligneHT,
        alerts.cause AS cause, 
        (SELECT sensor.device_number FROM sensor WHERE sensor.deveui = alerts.deveui) AS device_number,
         alerts.deveui AS deveui, alerts.status AS status, alerts.valeur
        FROM alerts 
        LEFT JOIN type_alert ON (type_alert.id = alerts.id_type_event)
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN group_site ON (group_site.site_id = site.id)
        LEFT JOIN group_name ON (group_name.group_id = group_site.group_id)
        WHERE group_name.name = :group_name
        AND alerts.status = 0";

        $stmt = $db->prepare($query_alerts_data);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

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
    public function getNumberActiveAlertsForGroup($group_name)
    {
        $db = static::getDB();

        $sql_nb_active_alert = "SELECT count(*) as nb_active_alerts
        FROM alerts 
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN group_site ON (group_site.site_id = site.id)
        LEFT JOIN group_name ON (group_name.group_id = group_site.group_id)
        WHERE status = 1 
        AND group_name.name = :group_name
        ";

        $stmt = $db->prepare($sql_nb_active_alert);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

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
    public function getNumberInactiveAlertsForGroup($group_name)
    {
        $db = static::getDB();

        $sql_nb_inactive_alert = "SELECT count(*) as nb_inactive_alerts
        FROM alerts 
        LEFT JOIN structure ON (structure.id = alerts.structure_id)
        LEFT JOIN site ON (site.id = structure.site_id)
        LEFT JOIN group_site ON (group_site.site_id = site.id)
        LEFT JOIN group_name ON (group_name.group_id = group_site.group_id)
        WHERE status = 0 
        AND group_name.name = :group_name
        ";

        $stmt = $db->prepare($sql_nb_inactive_alert);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

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
    public static function sendAlert($email, $phone_number)
    {
        $userManager = new UserManager();
        $user = $userManager->findByEmail($email);

        if ($user) {
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
        alerts.cause AS cause, 
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
        $results_api = ControllerDataObjenious::CallAPI("GET", $url);
        $alerts_data = $results_api["alerts"];

        return $alerts_data;
    }

    public function getAlertsFromApiForGroup($group, $state = "open",  $acknowledged = "true")
    {

        $url = "https://api.objenious.com/v1/alerts?group=" . $group . "&state=" . $state . "&acknowledged=" . $acknowledged;
        $results_api = ControllerDataObjenious::CallAPI("GET", $url);
        $alerts_data = $results_api["alerts"];

        return $alerts_data;
    }

    public static function getAllAlertsFromAPI()
    {
        $results_api = ControllerDataObjenious::CallAPI("GET", "https://api.objenious.com/v1/alerts");
        $alerts_data = $results_api["alerts"];

        return $alerts_data;
    }
}
