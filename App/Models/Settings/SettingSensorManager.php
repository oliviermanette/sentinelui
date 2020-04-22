<?php

namespace App\Models\Settings;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;


class SettingSensorManager extends \Core\Model
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }


    /**
     * find all the settings belong to a specific sensor
     *
     * @param string $deveui deveui of the sensors
     *
     * @return array  array which contains all the settings applied to this specific sensor
     */
    public static function findByDeveui($deveui)
    {
        $db = static::getDB();

        $sql = "SELECT settings_sensors_name.name as name_setting, sensor_settings.value , sensor_settings.activated 
        FROM sensor_settings
        LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
        LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
        WHERE sensor.deveui = :deveui";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $settingsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $settingsArr;
        }
    }


    public static function checkIfAlertByEmailActivatedForUser($email)
    {
        $db = static::getDB();

        $sql = "SELECT user.send_alert FROM `user` 
            WHERE user.email = :email";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $isAlertEmailActivated = $stmt->fetch(PDO::FETCH_COLUMN);
            if ($isAlertEmailActivated == 1) {
                return true;
            }
            return false;
        }
    }

    /**
     * Check if a setting exist for a specific sensor
     *
     * @param string $deveui deveui
     * @param string $settingName name of the setting
     *
     * @return boolean  true if exist
     */
    public static function checkIfSettingExistForSensor($deveui, $settingName)
    {
        $db = static::getDB();

        $sql = "SELECT *
        FROM sensor_settings
        LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
        LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
        WHERE settings_sensors_name.name = :settingName
        AND sensor.deveui = :deveui";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_COLUMN);
            if (empty($result)) {
                return false;
            }
            return true;
        }
    }

    /**
     * insert the value for a specific setting
     * @param string $deveui deveui of the sensor
     * @param string $settingName setting name
     * @param int $settingValue setting value
     *
     * @return boolean return true of created successfully
     */
    public static function insertSettingValueForSensor($deveui, $settingName, $settingValue)
    {
        $db = static::getDB();

        $sql = "SET @setting_id = (SELECT id FROM settings_sensors_name WHERE settings_sensors_name.name = :settingName);
        SET @sensor_deveui = (SELECT id FROM sensor WHERE sensor.deveui= :deveui);
        INSERT INTO sensor_settings (sensor_id, setting_name_id, value)
        VALUES (@sensor_deveui, @setting_id, :settingValue)
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':settingValue', $settingValue, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the value of a specific setting for a specific sensor
     * @param string $deveui deveui
     *  @param string $settingName setting name
     * @return int value that is applied for this specific setting
     */
    public static function getSettingValueForSensorOrNull($deveui, $settingName)
    {

        $db = static::getDB();

        $sql = "SELECT sensor_settings.value AS thresh
        FROM sensor_settings
        LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
        LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
        WHERE sensor.deveui = :deveui
        AND settings_sensors_name.name = :settingName";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $value = $stmt->fetch(PDO::FETCH_COLUMN);
            if (isset($value)) {
                return (int) $value;
            }
            return Null;
        }
    }

    /**
     *Check if a specific setting for a specific sensor is activated
     * @param string $deveui deveui
     * @param string $settingName setting name
     * @return int value that is applied for this specific setting
     * 
     *  @return boolean  true if setting activated
     */
    public static function isSettingActivatedForSensor($deveui, $settingName)
    {

        $db = static::getDB();

        $sql = "SELECT sensor_settings.activated
        FROM sensor_settings
        LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
        LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
        WHERE sensor.deveui = :deveui
        AND settings_sensors_name.name = :settingName";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $value = $stmt->fetch(PDO::FETCH_COLUMN);
            if ($value == 0) {
                return False;
            }
            return True;
        }
    }

    /**
     * update the value of a specific setting for a specific sensor
     *
     * @param string $deveui sensor deveuil where we want to apply this setting
     * @param string $settingName name of the setting
     * @param int $value value to apply
     *
     *  @return boolean  true if update correctly
     */
    public static function updateSettingValueForSensor($deveui, $settingName, $value)
    {
        $db = static::getDB();

        $sql = "UPDATE sensor_settings
            LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
            LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
            SET sensor_settings.value = :value
            WHERE sensor.deveui = :deveui
            AND settings_sensors_name.name = :settingName
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':value', $value, PDO::PARAM_INT);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return True;
        }
        return False;
    }

    /**
     *  update the activation for specific setting for a specific sensor
     *
     * @param string $deveui sensor deveuil where we want to apply this setting
     * @param string $settingName name of the setting
     *
     *  @return boolean  true if activte correclty
     */
    public static function updateActivateSettingForSensor($deveui, $settingName, $toActivate)
    {
        $db = static::getDB();

        $sql = "UPDATE sensor_settings
            LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
            LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
            SET sensor_settings.activated = :toActivate
            WHERE sensor.deveui = :deveui
            AND settings_sensors_name.name = :settingName
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':toActivate', $toActivate, PDO::PARAM_BOOL);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return True;
        }
        return False;
    }
    /**
     *  activate a specific setting for a specific sensor
     *
     * @param string $deveui sensor deveuil where we want to apply this setting
     * @param string $settingName name of the setting
     *
     *  @return boolean  true if activte correclty
     */
    public static function activateSettingForSensor($deveui, $settingName)
    {
        $db = static::getDB();

        $sql = "UPDATE sensor_settings
            LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
            LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
            SET sensor_settings.activated = true
            WHERE sensor.deveui = :deveui
            AND settings_sensors_name.name = :settingName
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return True;
        }
        return False;
    }


    /**
     *  deactivate a specific setting for a specific sensor
     *
     * @param string $deveui sensor deveuil where we want to apply this setting
     * @param string $settingName name of the setting
     *
     *  @return boolean  true if deactivte correclty
     */
    public static function deactivateSettingForSensor($deveui, $settingName)
    {
        $db = static::getDB();

        $sql = "UPDATE sensor_settings
            LEFT JOIN settings_sensors_name ON settings_sensors_name.id = sensor_settings.setting_name_id
            LEFT JOIN sensor ON sensor.id = sensor_settings.sensor_id
            SET sensor_settings.activated = false
            WHERE sensor.deveui = :deveui
            AND settings_sensors_name.name = :settingName
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':deveui', $deveui, PDO::PARAM_STR);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return True;
        }
        return False;
    }


    public static function updateAlertEmailNotification($email, $receiveNotification)
    {
        $db = static::getDB();

        $sql = "UPDATE user
        SET user.send_alert = :receiveNotification
        WHERE user.email = :email";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':receiveNotification', $receiveNotification, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
