<?php


namespace App\Models\Messages;

use App\Utilities;
use App\Models\EquipementManager;
use App\Models\SensorManager;

/**
 *
 *
 * PHP version 7.0
 */
class Alert extends Message
{

    public function __construct($type, $label, $deveui, $dateTime, $triggerValues = null)
    {
        $this->type = $type;
        $this->label = $label;
        $this->deveui = $deveui;
        $this->device_number = SensorManager::getDeviceNumberFromDeveui($this->deveui);
        $this->dateTime = $dateTime;
        if ($this->type == "inclination") {
            $this->msg = "Forte variation d'inclinaison";
            $this->thresh = $triggerValues["thresh"];
            $this->valueX = $triggerValues["valueX"];
            $this->valueY = $triggerValues["valueY"];
        } else if ($this->type == "shock") {
            $this->msg = "Choc intense";
            $this->valueShock = $triggerValues;
        }
        $this->triggerValues = $triggerValues;
        $this->analyseLabel();

        $this->equipementId = EquipementManager::getEquipementIdBySensorDeveui($deveui);
    }

    /**
     * Check the label of an event received by the sensor
     */
    private function analyseLabel()
    {
        switch ($this->label) {
            case 'ChangeStatusInactive':
                $this->msg = "Le capteur est devenu inactif.";
                $this->criticality = "HIGH";
                break;
            case 'ChangeStatusActive':
                $msg = "Le capteur est devenu actif.";
                $this->criticality = "LOW";
                break;
            case 'ChangeStatusError':
                $this->msg = "Une erreur est survenue avec le capteur.";
                $this->criticality = "HIGH";
                break;
            case 'ChangeStatusJoined':
                $this->msg = "Le capteur vient de se joindre au réseau.";
                $this->criticality = "LOW";
                break;
            case 'high_choc':
                $this->msg = "Choc important";
                $this->criticality = "HIGH";
                break;
            case 'first_thresh_inclinometer_raised':
                $this->msg = "Variation importante";
                $this->criticality = "HIGH";
                break;
            case 'second_thresh_inclinometer_raised':
                $this->msg = "Variation importante";
                $this->criticality = "HIGH";
                break;
            case 'third_thresh_inclinometer_raised':
                $this->msg = "Variation importante";
                $this->criticality = "HIGH";
                break;
            default:
                break;
        }
    }

    public function getProperMessageFromLabel()
    {
        return $this->msg;
    }
}
