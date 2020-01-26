<?php

namespace App\Models\NetworkAI;

use PDO;

/**
 * Neuron Input object
 *
 * PHP version 7.0
 */
class NeuronAggregateur extends Neuron
{

    private $neuronInput_1;
    private $neuronInput_2;
    private $meanIterative;


    public function __construct($pool_id)
    {
        $this->neuronInput_1 = null;
        $this->neuronInput_2 = null;
        $this->output = null;

        $this->pool_id = $pool_id;

        $this->meanIterative = 0;
        $this->type = "aggregateur";
    }

    /*
    public function __construct($pool_id, $id, $neuronInput_1, $neuronInput_2)
    {
        if ($neuronInput_1->type != "relation"){
            echo "neuron input 1 need to be type relation";
            return;
        }
        if ($neuronInput_2->type != "relation") {
            echo "neuron input 2 need to be type relation";
            return;
        }
        $this->neuronInput_1 = $neuronInput_1;
        $this->neuronInput_2 = $neuronInput_2;
        $this->output = null;

        $this->pool_id = $pool_id;
        $this->neuron_id = $id;

        $this->meanIterative = 0;
        $this->type = "aggregateur";
    }
*/

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

        $this->activityIsComputed = true;
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
        $inputArr = array(
            "neuronInput1" => $this->neuronInput_1,
            "neuronInput2" => $this->neuronInput_2
        );
        return $inputArr;
    }
    public function setInput($neuronInput_1, $neuronInput_2)
    {

        if ($neuronInput_1->type != "relation") {
            echo "neuron input 1 need to be type relation";
            return;
        }
        if ($neuronInput_2->type != "associative") {
            echo "neuron input 2 need to be type associative";
            return;
        }
        $this->neuronInput_1 = $neuronInput_1;
        $this->neuronInput_2 = $neuronInput_2;
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

    public function getInfoConnection()
    {
        if (isset($this->neuronInput_1) && isset($this->neuronInput_2)) {
            $tagNeuronInput_1 = $this->neuronInput_1->getTag();
            $tagNeuronInput_2 = $this->neuronInput_2->getTag();

            $typeNeuronInput_1 = $this->neuronInput_1->type();
            $typeNeuronInput_2 = $this->neuronInput_2->type();

            $valueNeuronInput_1 = $this->neuronInput_1->getInput();
            $valueNeuronInput_2 = $this->neuronInput_2->getInput();

            echo "\n Neuron aggregateur is connected to a neuron Input of type " . $typeNeuronInput_1 . " with tag " . $tagNeuronInput_1 .
                " and a neuron Input of type " . $typeNeuronInput_2 . " with tag " . $tagNeuronInput_2 . "\n";
            echo "==> Input 1 = "  . "\n";
            echo "==> Input 2 = " . "\n";
        } else {
            echo "\n Neuron aggregateur is not yet fully connected \n";
        }
    }
}
