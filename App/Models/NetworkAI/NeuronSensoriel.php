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

        $this->thresh = 15;
        $this->meanIterative = 0;
        $this->ratioVal = 0;

        $this->nbObservations = 1;
        $this->isActivated = false;
        $this->type = "sensoriel";
    }

    public function save()
    {

        $neuron_id_inserted = $this->insertNeuron();
        //Insert parameters associated to the neuron
        $this->insertParameters();
    }

    public function checkIfExistOnDB()
    {
        $isFind = false;
        $db = static::getDB();

        $sql = "SELECT id FROM neuron
            WHERE neuron.type = :type";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':type', $this->type(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            //print_r($results);
        }

        $sql = "SELECT * from parameters_sensoriel 
            WHERE neuron_id = :neuron_id";
        $stmt = $db->prepare($sql);
        foreach ($results as $neuron_id) {
            //On parcoure l'ensemble des parametres
            $stmt->bindValue(':neuron_id', $neuron_id, PDO::PARAM_INT);

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //print_r($results);
            //echo "RATIO à comparer : ". $this->ratioVal ."\n";
            if (!empty($results)) {
                if ($this->getTag() === "x") {
                    $ratioVal = $results[0]["moyenneValX"];
                }
                if ($this->getTag() === "y") {
                    $ratioVal = $results[0]["moyenneValY"];
                }
                //echo "RATIOVal: " . $ratioVal . "\n";
                if ($ratioVal == $this->ratioVal) {
                    echo "\nFind ! \n";
                    $isFind = true;
                    return true;
                }
            }
        }

        if ($isFind) {
            return true;
        }
    }

    private function insertParameters()
    {
        $db = static::getDB();

        if ($this->getTag() === "x") {
            $sql = "INSERT INTO `parameters_sensoriel` (neuron_id, moyenneValX, seuil)
                VALUES (:neuron_id, :ratioValX, :seuil)";
        }
        if ($this->getTag() === "y") {
            $sql = "INSERT INTO `parameters_sensoriel` (neuron_id, moyenneValY, seuil)
                VALUES (:neuron_id, :ratioValY, :seuil)";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':neuron_id', $this->neuron_id, PDO::PARAM_INT);
        $stmt->bindValue(':seuil', $this->thresh, PDO::PARAM_STR);

        if ($this->getTag() === "x") {
            $stmt->bindValue(':ratioValX', $this->ratioVal, PDO::PARAM_STR);
        }
        if ($this->getTag() === "y") {
            $stmt->bindValue(':ratioValY', $this->ratioVal, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
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
