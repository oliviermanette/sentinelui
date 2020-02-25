<?php

namespace App\Models\networkAI;

use PDO;
/*

Controllers are classes that contain methods that are actions.

*/

abstract class Neuron extends \Core\Model
{
    protected $pool_id;
    protected $neuron_id;
    protected $type;
    protected $tag;
    protected $output;
    protected $dateTimeActivity;
    protected $nbObservations;
    protected $activityIsComputed;


    //Compute activity of a neuron
    abstract protected function computeActivity();
    //Get Input of the neuron
    abstract protected function getInput();
    //Save neuron to database
    abstract protected function save();


    //Just insert neuron to the neuron DB
    protected function insertNeuron()
    {
        $db = static::getDB();

        $sql = " INSERT INTO neuron(type,network_id,nb_obervation)
                SELECT :type,(SELECT id FROM network WHERE pool_id = :pool_id),:nb_observation;
                select LAST_INSERT_ID() AS neuron_inserted_id";
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':type', $this->type, PDO::PARAM_STR);
        $stmt->bindValue(':pool_id', $this->pool_id, PDO::PARAM_INT);
        $stmt->bindValue(':nb_observation', $this->nbObservations, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $neuron_id_inserted = $db->lastInsertId();
            echo "type : ".$this->type."\n";
            echo "\n Neuron id inserted = " . $neuron_id_inserted."\n";
            $this->neuron_id = $neuron_id_inserted;
            return $neuron_id_inserted;
        }
        return false;
    }


    protected function associateToInput($neuronInput)
    {
        $db = static::getDB();

        $sql = " INSERT INTO neuron_associated(neuron_id,neuron_associated_id)
                SELECT :neuron_id,:neuron_associated_id;
                ";
        $stmt = $db->prepare($sql);

        $neuron_associated_id = $neuronInput->neuron_id;
        $stmt->bindValue(':neuron_id', $this->neuron_id, PDO::PARAM_INT);
        $stmt->bindValue(':neuron_associated_id', $neuron_associated_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    protected function updateNbObservations(){
        $this->nbObservations++;
    }

    protected function getNbObservations()
    {
        return $this->nbObservations;
    }

    protected function getOutput(){
        return $this->output;
    }
    
    protected function getPoolId(){
        return $this->pool_id;
    }

    protected function getNeuronID(){
        return $this->neuron_id;
    }

    protected function setPoolId($pool_id)
    {
        return $this->pool_id = $pool_id;
    }

    protected function getDateTime()
    {
        return $this->dateTime;
    }

    public function type()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($tag){
        $this->tag = $tag;
    }


}