<?php

namespace App\Models;
Use PDO;
/**
 * Neurons Relation object
 *
 * PHP version 7.0
 */
class NeuronRelation extends Neuron
{

    private $neuronInput_1;
    private $neuronInput_2;
    private $tupleNeuronInput;
    private $ratioValX;
    private $ratioValY;

    private $activityX;
    private $activityY;
    private $output;
    private $delay;
    private $meanIterative;

    public function __construct($pool_id, $id, $neuronInput_1, $neuronInput_2)
    {
        $this->pool_id = $pool_id;
        $this->neuron_id = $id;
        $this->neuronInput_1 = $neuronInput_1;
        $this->neuronInput_2 = $neuronInput_2;

        $this->activityX = 0;
        $this->activityY = 0;

        $this->delay = 0;
        $this->output = 1;
        $this->meanIterative = 1;

        $this->computeRatioValX($neuronInput_1, $neuronInput_2);
        $this->computeRatioValY($neuronInput_1, $neuronInput_2);


    }


    public function save(){

        $id1 = $this->neuronInput_1->getNeuronID();
        $id2 = $this->neuronInput_2->getNeuronID();

        echo "\n Ratio Val X : " . gettype($this->ratioValX);
        echo "\n Ratio Val Y : " . $this->ratioValY;

        $db = static::getDB();

        $sql = "INSERT INTO neuron(type, pool_id, mean_iterative, id_neuron_1, id_neuron_2, ratioValX, ratioValY, delay)
        SELECT * FROM
        (SELECT :typeNeuron AS type, :pool_id AS pool_id, :mean_iterative AS mean_iterative, :neurone_id_1 AS id_neuron_1, :neurone_id_2 AS id_neuron_2, 
        :ratioValX AS ratioValX, :ratioValY AS ratioValY, :delay AS delay ) AS tmp
         WHERE NOT EXISTS (
        SELECT * FROM neuron 
        WHERE id_neuron_1=:neurone_id_1 
        AND id_neuron_2=:neurone_id_2
        AND ratioValY=:ratioValY
        AND ratioValX=:ratioValX) 
        LIMIT 1
        ";
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':typeNeuron', "relation", PDO::PARAM_STR);
        $stmt->bindValue(':pool_id', $this->pool_id, PDO::PARAM_INT);
        $stmt->bindValue(':mean_iterative', $this->meanIterative, PDO::PARAM_STR);
        $stmt->bindValue(':neurone_id_1', $id1, PDO::PARAM_INT);
        $stmt->bindValue(':neurone_id_2', $id2, PDO::PARAM_INT);
        $stmt->bindValue(':ratioValX', $this->ratioValX, PDO::PARAM_STR);
        $stmt->bindValue(':ratioValY', $this->ratioValY, PDO::PARAM_STR);
        $stmt->bindValue(':delay', $this->delay, PDO::PARAM_INT);

        $ok = $stmt->execute();
        if ($ok) {
            return true;
        } else {
            return false;
        }
    }

    public function computeActivity()
    {
        $nbActivity = $this->getNumberActivity();
        if ($nbActivity < 3){
            $this->activityX = 1;
            $this->activityY = 1;
        }
    }

    public function getNumberActivity(){
        $id1 = $this->neuronInput_1->getNeuronID();
        $id2 = $this->neuronInput_2->getNeuronID();

        $db = static::getDB();

        $sql = "SELECT count(*) AS n FROM (SELECT * FROM neuron 
        WHERE neuron.type LIKE 'relation'
        AND id_neuron_1 = :id_neuron_1 and id_neuron_2 = :id_neuron_2) AS neuronRelation
        LEFT JOIN activation_neuron ON ((activation_neuron.neuron_id = neuronRelation.id_neuron_1)) 
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id_neuron_1', $id1, PDO::PARAM_INT);
        $stmt->bindValue(':id_neuron_2', $id2, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $n = $stmt->fetch(PDO::FETCH_COLUMN);
        }
        return $n;
    }


    public function saveActivity($date_time)
    {
        $db = static::getDB();
        echo "\n Ratio Val X : ".$this->ratioValX;
        echo "\n Ratio Val Y : " . $this->ratioValY;
        $ativityJson = '{"activityX":' . $this->activityX . ', "activityY":' . $this->activityY . '}';

        $sql = "INSERT INTO `activation_neuron` (neuron_id, activity, date_time)
        SELECT (SELECT id FROM neuron WHERE neuron.ratioValX = :ratioValX 
                AND neuron.ratioValY = :ratioValY),
                :ativityJson, :date_time
                ";
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':ratioValX', $this->ratioValX, PDO::PARAM_STR);
        $stmt->bindValue(':ratioValY', $this->ratioValY, PDO::PARAM_STR);
        $stmt->bindValue(':ativityJson', $ativityJson, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);

        $ok = $stmt->execute();
        if ($ok) {
            return true;
        } else {
            return false;
        }
    }

    public function computeRatioValX($neuronInput_1, $neuronInput_2)
    {
        $valX_input1 = $neuronInput_1->getRatioValX();
        $valX_input2 = $neuronInput_2->getRatioValX();

        $this->ratioValX = $valX_input1 / $valX_input2;

    }

    public function computeRatioValY($neuronInput_1, $neuronInput_2)
    {
        $valY_input1 = $neuronInput_1->getRatioValY();
        $valY_input2 = $neuronInput_2->getRatioValY();

        $this->ratioValY = $valY_input1 / $valY_input2;

    }



    protected function update()
    {
    }
    
    public function getOutput(){
        return $this->output;
    }

    public function getNeuronsInput(){
        return $this->tupleNeuronInput;
    }

 

    public function getRatioValX()
    {
        return $this->ratioValX;
    }

    public function getRatioValY()
    {
        return $this->ratioValY;
    }

    public function setRatioValX($valX)
    {
        return $this->ratioValX = $valX;
    }

    public function setRatioValY($valY)
    {
        return $this->ratioValY = $valY;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    public function setDelay($delay)
    {
        return $this->delay = $delay;
    }
}
