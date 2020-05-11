<?php

namespace App\Models;

use \App\Models\API\SentiveAPI;

use PDO;

/*

author : Lirone Samoun

*/

class SentiveAIManager extends \Core\Model
{

    public static function getVersionSentive()
    {
        $version = SentiveAPI::getVersion();
        return $version;
    }
}
