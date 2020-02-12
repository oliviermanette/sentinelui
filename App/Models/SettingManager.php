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

    public static function findByUserId($user_id)
    {
        $db = static::getDB();

        $sql = 'SELECT user_id, email, name, value FROM `user_settings`
            LEFT JOIN settings ON (settings.id = user_settings.settings_id)
            LEFT JOIN user ON (user.id = user_settings.user_id)
            WHERE user_id = :user_id';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $settingsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $settingsArr;
        }
    }

    public static function updateShockThresh($user_id, $shockThreshValue){
        $db = static::getDB();

        $sql = "UPDATE user_settings
        LEFT JOIN settings ON (settings.id = user_settings.settings_id)
        LEFT JOIN user ON (user.id = user_settings.user_id)
        SET user_settings.value = :shockThreshValue
        WHERE user_id = :user_id AND settings.name = 'shock_thresh'
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':shockThreshValue', $shockThreshValue, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function updateInclinometerThresh($user_id, $inclinometerThreshValue)
    {
        $db = static::getDB();

        $sql = "UPDATE user_settings
        LEFT JOIN settings ON (settings.id = user_settings.settings_id)
        LEFT JOIN user ON (user.id = user_settings.user_id)
        SET user_settings.value = :inclinometerThreshValue
        WHERE user_id = :user_id AND settings.name = 'inclinometer_thresh'
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':inclinometerThreshValue', $inclinometerThreshValue, PDO::PARAM_INT);

        if ($stmt->execute()){
            return true;
        }
        return false;
        
    }
}