<?php

namespace App\Models\Settings;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;


class SettingGeneralManager extends \Core\Model
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * find all the settings belong to a specific group
     *
     * @param int $groupId group id
     *
     * @return array  array which contains all the settings applied to this specific group
     */
    public static function findByGroupId($groupId)
    {
        $db = static::getDB();

        $sql = "SELECT settings.name as name_setting, value 
        FROM `group_settings`
        LEFT JOIN settings ON (settings.id = group_settings.settings_id)
        LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
        WHERE group_settings.group_id = :groupId";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $settingsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $settingsArr;
        }
    }


    /**
     * Check if a setting exist for a specific group
     *
     * @param int $groupId group id
     * @param string $settingName name of the setting
     *
     * @return boolean  true if exist
     */
    public static function checkIfSettingExistForGroup($groupId, $settingName)
    {
        $db = static::getDB();

        $sql = "SELECT * FROM group_settings
        LEFT JOIN settings ON settings.id=group_settings.settings_id
        WHERE settings.name = :settingName
        AND group_settings.group_id = :groupId";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);
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
     * @param int $groupId group id
     * @param string $settingName setting name
     * @param int $settingValue setting value
     *
     * @return boolean return true of created successfully
     */
    public static function insertSettingValueForGroup($groupId, $settingName, $settingValue)
    {
        $db = static::getDB();

        $sql = "SET @setting_id = (SELECT id FROM settings WHERE settings.name = :settingName);
        INSERT INTO group_settings (group_id, settings_id, value)
        VALUES (:groupId, @setting_id, :settingValue)
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->bindValue(':settingValue', $settingValue, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the value of a specific setting for a specific group
     * @param int $groupId group id
     *
     * @return int value that is applied for this specific setting
     */
    public static function getSettingValueForGroupOrNull($groupId, $settingName)
    {

        $db = static::getDB();

        $sql = "SELECT value AS thresh FROM group_settings
            LEFT JOIN settings ON (settings.id = group_settings.settings_id)
            LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
            WHERE group_settings.group_id = :groupId AND settings.name = :settingName";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);
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
     * update the value of a specific setting for a specific group
     *
     * @param int $groupId group id where we want to apply this setting
     * @param string $settingName name of the setting
     * @param int $value value to apply
     *
     * @return void 
     */
    public static function updateSettingValueForGroup($groupId, $settingName, $value)
    {
        $db = static::getDB();

        $sql = "UPDATE group_settings
            LEFT JOIN settings ON (settings.id = group_settings.settings_id)
            LEFT JOIN group_name ON (group_name.group_id = group_settings.group_id)
            SET group_settings.value = :value
            WHERE group_settings.group_id = :groupId AND settings.name = :settingName
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->bindValue(':value', $value, PDO::PARAM_INT);
        $stmt->bindValue(':settingName', $settingName, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return True;
        }
        return False;
    }
