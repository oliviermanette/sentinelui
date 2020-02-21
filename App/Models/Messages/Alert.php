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

    public function getProperMessageFromLabel(){
        switch($this->label){
            case 'ChangeStatusInactive':
                $msg = "Le capteur est devenu inactif.";
                break;
            case 'ChangeStatusActive':
                $msg = "Le capteur est devenu actif.";
                break;
            case 'ChangeStatusError':
                $msg = "Une erreur est survenu avec le capteur.";
                break;
            case 'high_choc':
                $msg = "Choc important";
                break;
            default:
                break;
        }

        return $msg;
    }
    
}
