<?php

namespace App\Models;
use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;


class SettingManager extends \Core\Model
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save()
    {
    }

    public static function findByGroupName($group_name)
    {
        $db = static::getDB();

        $sql = "SELECT group_name.name, settings.name, value FROM `group_settings`
            LEFT JOIN settings ON (settings.id = group_settings.settings_id)
            LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
            WHERE group_name.name = :group_name";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $settingsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $settingsArr;
        }
    }

    public static function getShockThresh($group_name){

        $db = static::getDB();

        $sql = "SELECT value  AS thresh FROM group_settings
            LEFT JOIN settings ON (settings.id = group_settings.settings_id)
            LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
            WHERE group_name.name = :group_name AND settings.name = 'shock_thresh'";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $shockThresh = $stmt->fetch(PDO::FETCH_COLUMN);
            return (int)$shockThresh;
        }
    }

    public static function getInclinometerThresh($group_name)
    {

        $db = static::getDB();

        $sql = "SELECT value  AS thresh FROM group_settings
            LEFT JOIN settings ON (settings.id = group_settings.settings_id)
            LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
            WHERE group_name.name = :group_name AND settings.name = 'inclinometer_thresh'";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $inclinometerThresh = $stmt->fetch(PDO::FETCH_COLUMN);
            return (int) $inclinometerThresh;
        }
    }
    public static function updateShockThresh($group_name, $shockThreshValue){
        $db = static::getDB();

        $sql = "UPDATE group_settings
            LEFT JOIN settings ON (settings.id = group_settings.settings_id)
            LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
            SET group_settings.value = :shockThreshValue
            WHERE group_name.name = :group_name AND settings.name = 'shock_thresh'
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
        $stmt->bindValue(':shockThreshValue', $shockThreshValue, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function updateInclinometerThresh($group_name, $inclinometerThreshValue)
    {
        $db = static::getDB();

        $sql = "UPDATE group_settings
            LEFT JOIN settings ON (settings.id = group_settings.settings_id)
            LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
            SET group_settings.value = :inclinometerThreshValue
            WHERE group_name.name = :group_name AND settings.name = 'inclinometer_thresh'
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':group_name', $group_name, PDO::PARAM_STR);
        $stmt->bindValue(':inclinometerThreshValue', $inclinometerThreshValue, PDO::PARAM_INT);

        if ($stmt->execute()){
            return true;
        }
        return false;
        
    }
}