<?php

namespace App\Models\NetworkAI;

use PDO;

/**
 * Neuron Input object
 *
 * PHP version 7.0
 */
class NeuronSensoriel extends Neuron
{

    private $ratioVal;
    private $thresh;
    private $input;
    private $meanIterative;

    private $isActivated;

    public function __construct($pool_id)
    {
        $this->input = null;
        $this->output = null;

        $this->pool_id = $pool_id;
        //$this->neuron_id = $id;

        $this->thresh = 0;
        $this->meanIterative = 0;
        $this->ratioVal = 0;

        $this->nbObervations = 1;
        $this->isActivated = false;
        $this->type = "sensoriel";
    }

    public function save()
    {

        $db = static::getDB();

        $sql = "
        ";
        $stmt = $db->prepare($sql);

        //$stmt->bindValue(':typeNeuron', "input", PDO::PARAM_STR);

        $ok = $stmt->execute();
        if ($ok) {
            return true;
        } else {
            return false;
        }
    }

    public function saveActivity($date_time)
    {
        if ($this->activityIsComputed) {
            $db = static::getDB();



            $sql = "";
            $stmt = $db->prepare($sql);

            //$stmt->bindValue(':ratioValX', $this->ratioValX, PDO::PARAM_STR);


            $ok = $stmt->execute();
            if ($ok) {
                return true;
            } else {
                return false;
            }
        }
    }


    public function computeActivity()
    {
        if (isset($this->input)) {
            $activity = $this->getInput();
            $this->output = $activity;

            $this->activityIsComputed = true;
        } else {
            echo "input need to be set";
        }
    }


    public function getActivityFromTable()
    {
        $db = static::getDB();

        $sql = "";

        $stmt = $db->prepare($sql);
    }


    public function getOutput()
    {
        return $this->output;
    }
    public function getInput()
    {
        return $this->input;
    }
    public function setInput($valInput)
    {
        $this->input = $valInput;
    }

    public function getRatioVal()
    {
        return $this->ratioVal;
    }

    public function setRatioVal($val)
    {
        $this->ratioVal = $val;
    }

    public function getMeanInterative()
    {
        return $this->meanIterative;
    }

    public function setMeanInterative($val)
    {
        $this->meanIterative = $val;
    }



    public function getThresh()
    {
        return $this->thresh;
    }


    public function setThresh($thresh)
    {
        return $this->thresh = $thresh;
    }

    public function activate()
    {
        $this->isActivated = true;
    }

    public function deactivate()
    {
        $this->isActivated = false;
    }
}
