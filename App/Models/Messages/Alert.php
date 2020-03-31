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
        $this->analyseLabel();

        $this->equipementId = EquipementManager::getEquipementIdBySensorDeveui($deveui);

    }
    private function analyseLabel(){
        switch($this->label){
            case 'ChangeStatusInactive':
                $this->msg = "Le capteur est devenu inactif.";
                $this->criticality = "HIGH";
                break;
            case 'ChangeStatusActive':
                $msg = "Le capteur est devenu actif.";
                $this->criticality = "LOW";
                break;
            case 'ChangeStatusError':
                $this->msg = "Une erreur est survenu avec le capteur.";
                $this->criticality = "HIGH";
                break;
            case 'ChangeStatusJoined':
                $this->msg = "Le capteur just joined.";
                $this->criticality = "LOW";
                break;
            case 'high_choc':
                $this->msg = "Choc important";
                $this->criticality = "HIGH";
                break;
            case 'high_variation':
                $this->msg = "Variation importante";
                $this->criticality = "HIGH";
                break;
            default:
                break;
        }
    }

    public function getProperMessageFromLabel(){
        return $this->msg;
    }
}
