<?php

namespace App\Models\NetworkAI;

use \Core\View;
use PDO;
use App\Config;
use App\Utilities;
use \App\Models\NetworkAI\Neuron;
use \App\Models\NetworkAI\NeuronSensoriel;
use \App\Models\NetworkAI\NeuronRelation;
use \App\Models\NetworkAI\NeuronAssociative;
use \App\Models\NetworkAI\NeuronAggregateur;
use \App\Models\NetworkAI\NeuronCategory;

/**
 * Layer object
 *
 * PHP version 7.0
 */
class Layer extends \Core\Model
{
    private $pool_id;
    private $typeLayer;
    private $nbNeurons; //Nb neurons that have the layer
    private $listNeuronsArr; //List of neurons that have the layer
    private $typeNeurons; //Type of neurons that have the layer

    /**
     * constructor
     *
     * @return void
     */
    function __construct($pool_id)
    {
        $this->pool_id = $pool_id;
        $this->nbNeurons = 0;
        $this->listNeuronsArr = array();
        //Fill the layer with new neurons

    }

    public function addNewNeuron($nb, $type, $tag = "")
    {
        for ($i = 0; $i < $nb; $i++) {
            switch ($type) {
                case "sensoriel":
                    $neuron = new NeuronSensoriel($this->pool_id);
                    break;
                case "relation":
                    $neuron = new NeuronRelation($this->pool_id);
                    break;
                case "associative":
                    $neuron = new NeuronAssociative($this->pool_id);
                    break;
                case "aggregateur":
                    $neuron = new NeuronAggregateur($this->pool_id);
                    break;
                case "category":
                    $neuron = new NeuronCategory($this->pool_id);
                    break;
                case "superCategory":
                    $neuron = new NeuronSuperCategory($this->pool_id);
                    break;
                default:
                    break;
            }
            $neuron->setType($type);
            $neuron->setTag($tag);
            array_push($this->listNeuronsArr, $neuron);
            $this->nbNeurons++;
        }
    }

    public function addNewNeuronInput($neuron_id)
    {
        if ($this->getTypeLayer() == "input") {
            $neuron = new NeuronInput($this->pool_id, $neuron_id);
            //$neuron->setThresh($thresh = 10);
            array_push($this->listNeuronsArr, $neuron);
            $this->nbNeurons++;
        } else {
            echo "\n Layer type is not correct ! \n";
        }
    }

    public function addNewNeuronRelation($neuron_id, $neuronInput_Id1, $neuronInput_Id2)
    {
        if ($this->getTypeLayer() == "relations") {
            $neuron = new NeuronRelation($this->pool_id, $neuron_id,  $neuronInput_Id1, $neuronInput_Id2);
            array_push($this->listNeuronsArr, $neuron);
            $this->nbNeurons++;
        } else {
            echo "\n Layer type is not correct ! \n";
        }
    }

    public function getInputNeuronsActivityFromTable($pool_id)
    {
        $db = static::getDB();

        $sql = "SELECT n.id AS neuron_id, JSON_EXTRACT(a.activity, '$.activityX') AS 'activityX', 
        JSON_EXTRACT(a.activity, '$.activityY' ) AS 'activityY' FROM activation_neuron AS a 
        LEFT JOIN neuron AS n ON (n.id = a.neuron_id)
        WHERE pool_id = :pool_id AND n.type LIKE 'input'";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':pool_id', $pool_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 0) {
                return $results;
            } else {
                return array();
            }
        }
    }

    /*
    public function addNewNeuron($typeNeurons, $neuron_id){
        if ($typeNeurons = "input") {
            $neuron = new NeuronInput($this->pool_id, $neuron_id);
            $neuron->setThresh($thresh = 10);

        } else if ($typeNeurons = "relation") {
            //$neuron = new NeuronRelation();
        } else if ($typeNeurons = "category") {
            $neuron = new NeuronCategory();
        }
        array_push($this->listNeuronsArr, $neuron);
        $this->nbNeurons++;
    }
*/

    public function computeActivity()
    {
        for ($i = 0; $i < count($this->listNeuronsArr); $i++) {
            //Compute activity
            $this->listNeuronsArr[$i]->computeActivity();
        }
    }


    public static function getLayerFromTable($pool_id, $type)
    {

        $db = static::getDB();

        $sql = "SELECT * FROM `neuron` AS n 
        WHERE pool_id = :pool_id AND type = :type";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':pool_id', $pool_id, PDO::PARAM_INT);
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 0) {
                return $results;
            } else {
                return array();
            }
        }
    }


    public function getNeuronId($id)
    {
        return $this->listNeuronsArr[$id - 1]; //Because we attribute ID from 1 and not from 0
    }



    public function setTypeLayer($name)
    {
        $this->typeLayer = $name;
    }

    public function getTypeLayer()
    {
        return $this->typeLayer;
    }

    public function getNumberTotalNeurons($tag = null)
    {
        if (isset($tag)) {
            $countNbreNeuronTag = 0;
            foreach ($this->listNeuronsArr as $neuron) {
                if ($neuron->getTag() === $tag) {
                    $countNbreNeuronTag++;
                }
            }
            return $countNbreNeuronTag;
        }

        return $this->nbNeurons;
    }

    public function getNumberTotalNeuronsOftype($type)
    {

        $countNbreNeuronType = 0;
        foreach ($this->listNeuronsArr as $neuron) {
            if ($neuron->type() === $type) {
                $countNbreNeuronType++;
            }
        }
        return $countNbreNeuronType;
    }

    public function getNeuronsWithTag($tag)
    {
        $neuronsTaggedArr = array();
        foreach ($this->listNeuronsArr as $neuron) {
            if ($neuron->getTag() === $tag) {
                array_push($neuronsTaggedArr, $neuron);
            }
        }
        return $neuronsTaggedArr;
    }

    public function getNeuronsWithTypeAndTag($type, $tag)
    {
        $neuronsTypeArr = array();
        foreach ($this->listNeuronsArr as $neuron) {
            if (($neuron->type() === $type) && $neuron->getTag() === $tag ) {
                array_push($neuronsTypeArr, $neuron);
            }
        }
        return $neuronsTypeArr;
    }

    public function getNeuronsArr()
    {
        return $this->listNeuronsArr;
    }

    public function getNeuronAtIndex($index)
    {
        return $this->listNeuronsArr[$index];
    }
}
