<?php

namespace Core;

use PDO;
use App\Config;


/**
 * Base model
 *
 * PHP version 7.0
 */
abstract class Model
{

    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            if (Config::DEV_MOD) {
                $dsn = 'mysql:host=' . Config::DB_HOST_DEV . ';dbname=' . Config::DB_NAME_DEV . ';charset=utf8';
                $db = new PDO($dsn, Config::DB_USER_DEV, Config::DB_PASSWORD_DEV);
            } else {
                $dsn = 'mysql:host=' . Config::DB_HOST_PROD . ';dbname=' . Config::DB_NAME_PROD . ';charset=utf8';
                $db = new PDO($dsn, Config::DB_USER_PROD, Config::DB_PASSWORD_PROD);
            }
            // Throw an Exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }
}
