<?php


namespace App\Models\Messages;

use App\Utilities;
use App\Models\EquipementManager;

/**
 * 
 *
 * PHP version 7.0
 */
class Alert extends Message
{

    public function __construct($label, $deveui, $dateTime, $triggerValue = null) 
    {
        $this->label = $label;
        $this->deveui = $deveui;
        $this->dateTime = $dateTime;
        $this->triggerValue = $triggerValue;

        $this->equipementId = EquipementManager::getEquipementIdBySensorDeveui($deveui);
        
    }
}
