<?php

namespace App\Models\NetworkAI;

use PDO;
/**
 * Neuron Category object
 *
 * PHP version 7.0
 */
class NeuronCategory extends Neuron
{

    private $neuronInputArr;
    private $meanIterative;

    private $thresh;


    public function __construct($pool_id)
    {
        $this->neuronInputArr = array();
        $this->output = null;

        $this->pool_id = $pool_id;

        $this->meanIterative = null;
        $this->thresh = 15;
        $this->type = "category";
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

        $sql = "INSERT INTO `parameters_category` (neuron_id, seuil)
                VALUES (:neuron_id, :seuil)";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':neuron_id', $this->neuron_id, PDO::PARAM_INT);
        $stmt->bindValue(':seuil', $this->thresh, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function computeActivity()
    {

        $this->activityIsComputed = true;
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
            if ($neuron->type() != "aggregateur"){
                echo "Neuron Category need to be connected to neuron aggregateur";
                return false;
            }
        }
        $this->neuronInputArr = $neuronInputArr;
      
    }

    public function getInfoConnection()
    {
        if (isset($this->neuronInputArr)) {
            $nbreNeuronInput = count($this->neuronInputArr);

            echo "\n Neuron category is connected to ". $nbreNeuronInput ." neuron aggregateur \n";
        } else {
            echo "\n Neuron category is not yet fully connected \n";
        }
    }


}
