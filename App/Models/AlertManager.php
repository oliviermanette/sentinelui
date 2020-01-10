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

    public function __construct()
    {
    }

    /**
     * create a new alert
     *
     * @return void
     */
    public function create(){

        $db = static::getDB();
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


}