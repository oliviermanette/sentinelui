<?php

/*
alertManager.php
author : Lirone Samoun

Briefly : 

*/

namespace App\Models;

use App\Config;
use App\Utilities;
use \App\Models\UserManager;
use PDO;

class AlertManager extends \Core\Model
{


    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function createFromArr($dataArr){
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
     * create a new alert
     *
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

    public function delete(){

    }
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

    public static function alertExists()
    {
        /*$user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }*/

        return false;
    }

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

    public function getNumberInActiveAlerts($structure_id)
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
        /*$sql = 'SELECT * FROM user WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();*/
    }

    public static function findByEvent($event)
    {
    }
}
