<?php

namespace App\Models\NetworkAI;

use PDO;

/**
 * Neuron Category object
 *
 * PHP version 7.0
 */
class NeuronSuperCategory extends Neuron
{

    private $neuronInputArr;
    private $meanIterative;
    private $nbOutputSuper;
    private $thresh;


    public function __construct($pool_id)
    {
        $this->neuronInputArr = array();
        $this->output = null;

        $this->pool_id = $pool_id;

        $this->meanIterative = null;
        $this->nbOutputSuper = 0;
        $this->thresh = 15;
        $this->type = "superCategory";
    }
    /*
    public function __construct($pool_id, $id, $neuronInputArr)
    {
        //Loop to check if the type of neuron is type of agregateur
        foreach ($neuronInputArr as $neuron){

        }
        $this->neuronInputArr = $neuronInputArr;
        $this->output = null;

        $this->pool_id = $pool_id;
        $this->neuron_id = $id;

        $this->meanIterative = 0;
        $this->type = "category";
    }*/

    public function computeActivity()
    {

        $this->activityIsComputed = true;
    }

    public function save()
    {
        $neuron_id_inserted = $this->insertNeuron();
        //Insert parameters associated to the neuron
        foreach ($this->neuronInputArr as $neuron) {
            $this->associateToInput($neuron);
        }

        $this->insertParameters();
    }

    private function insertParameters()
    {
        $db = static::getDB();

        $sql = "INSERT INTO `parameters_super_category` (neuron_id, seuil, nbOutputSuper, moyenneIterative)
                VALUES (:neuron_id, :seuil, :nbOutputSuper, :meanIterative)";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':neuron_id', $this->neuron_id, PDO::PARAM_INT);
        $stmt->bindValue(':seuil', $this->thresh, PDO::PARAM_STR);
        $stmt->bindValue(':nbOutputSuper', $this->nbOutputSuper, PDO::PARAM_STR);
        $stmt->bindValue(':meanIterative', $this->meanIterative, PDO::PARAM_STR);

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


    public function getThresh()
    {
        return $this->thresh;
    }


    public function setThresh($thresh)
    {
        return $this->thresh = $thresh;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getInput()
    {
        return $this->neuronInputArr;
    }

    public function setInput($neuronInputArr)
    {
        foreach ($neuronInputArr as $neuron) {
            if ($neuron->type() != "category") {
                echo "Neuron Category need to be connected to neuron category";
                return false;
            }
        }
        $this->neuronInputArr = $neuronInputArr;
    }
}
