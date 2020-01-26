<?php

namespace App\Models\NetworkAI;

use PDO;

/**
 * Neuron Input object
 *
 * PHP version 7.0
 */
class NeuronRelation extends Neuron
{

    private $neuronInput_1;
    private $neuronInput_2;
    private $ratioVal;

    public function __construct($pool_id)
    {
        $this->neuronInput_1 = null;
        $this->neuronInput_2 = null;
        $this->output = null;

        $this->pool_id = $pool_id;

        $this->ratioVal = null;
        $this->nbObservations = 1;
        $this->type = "relation";
    }

    /*public function __construct($pool_id, $neuronInput_1, $neuronInput_2)
    {

        $this->neuronInput_1 = $neuronInput_1;
        $this->neuronInput_2 = $neuronInput_2;
        $this->output = null;

        $this->pool_id = $pool_id;
        //$this->neuron_id = $id;

        $this->ratioVal = 0;
        $this->type = "relation";
    }*/



    public function save()
    {

        $neuron_id_inserted = $this->insertNeuron();
        //Insert parameters associated to the neuron
        $this->associateToInput($this->neuronInput_1);
        $this->associateToInput($this->neuronInput_2);
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

        $sql = "SELECT * from parameters_relation
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
                    $ratioVal = $results[0]["ratioValX"];
                }
                if ($this->getTag() === "y") {
                    $ratioVal = $results[0]["ratioValY"];
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
            $sql = "INSERT INTO `parameters_relation` (neuron_id, ratioValX)
                VALUES (:neuron_id, :ratioValX)";
        }
        if ($this->getTag() === "y") {
            $sql = "INSERT INTO `parameters_relation` (neuron_id, ratioValY)
                VALUES (:neuron_id, :ratioValY)";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':neuron_id', $this->neuron_id, PDO::PARAM_INT);

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

        $this->activityIsComputed = true;
    }


    public function getActivityFromTable()
    {
        $db = static::getDB();

        $sql = "";

        $stmt = $db->prepare($sql);
    }

    public function getInfoConnection()
    {
        if (isset($this->neuronInput_1) && isset($this->neuronInput_2)) {
            $tagNeuronInput_1 = $this->neuronInput_1->getTag();
            $tagNeuronInput_2 = $this->neuronInput_2->getTag();

            $typeNeuronInput_1 = $this->neuronInput_1->type();
            $typeNeuronInput_2 = $this->neuronInput_2->type();

            $valueNeuronInput_1 = $this->neuronInput_1->getInput();
            $valueNeuronInput_2 = $this->neuronInput_2->getInput();

            echo "\n Neuron relation is connected to a neuron Input of type " . $typeNeuronInput_1 . " with tag " . $tagNeuronInput_1 .
                " and a neuron Input of type " . $typeNeuronInput_2 . " with tag " . $tagNeuronInput_2 . "\n";
            echo "==> Input 1 = " . $valueNeuronInput_1 . "\n";
            echo "==> Input 2 = " . $valueNeuronInput_2 . "\n";
        } else {
            echo "\n Neuron relation is not yet fully connected \n";
        }
    }


    public function getOutput()
    {
        return $this->output;
    }
    public function getInput()
    {
        $inputArr = array(
            "neuronInput1" => $this->neuronInput_1,
            "neuronInput2" => $this->neuronInput_2
        );
        return $inputArr;
    }
    public function setInput($neuronInput_1, $neuronInput_2)
    {
        if ($neuronInput_1->type() != "sensoriel") {
            echo "Neuron relation need to be connected to neuron sensoriel";
            return false;
        }
        if ($neuronInput_2->type() != "sensoriel") {
            echo "Neuron relation need to be connected to neuron sensoriel";
            return false;
        }
        $this->neuronInput_1 = $neuronInput_1;
        $this->neuronInput_2 = $neuronInput_2;
    }

    public function isConnectedToInput()
    {
        if (isset($this->neuronInput_1) && isset($this->neuronInput_2)) {
            return true;
        }
        return false;
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

    public function activate()
    {
        $this->isActivated = true;
    }

    public function deactivate()
    {
        $this->isActivated = false;
    }
}
