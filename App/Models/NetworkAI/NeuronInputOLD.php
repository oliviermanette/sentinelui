<?php

namespace App\Models;

use PDO;
/**
 * Neuron Input object
 *
 * PHP version 7.0
 */
class NeuronInput extends Neuron
{

    private $ratioValX;
    private $ratioValY;
    private $activityX;
    private $activityY;

    private $thresh;
    private $output;
    private $nbObervations;

    private $isActivated;

    public function __construct($pool_id, $id, $thresh = 0)
    {
        $this->pool_id = $pool_id;
        $this->neuron_id = $id;
        $this->thresh = 0;
        $this->ratioValX = 0;
        $this->ratioValY = 0;
        $this->activityX = 0;
        $this->activityY = 0;
        $this->output = 0;
        $this->nbObervations = 1;
        $this->isActivated = false;
        $this->dataType = "spectre";
        $this->valActivity = 0;
        


    }

    public function save(){

        $db = static::getDB();

        echo "\n Ratio Val X INPUT: " . gettype($this->ratioValX);

        $sql = "INSERT INTO neuron(type, ratioValX, ratioValY, seuil, pool_id, nb_obervation, datatype_id)
        SELECT * FROM (SELECT :typeNeuron, :ratioValX AS ratioValX, 
        :ratioValY AS ratioValY, :thresh AS thresh, :pool_id AS pool_id, :nb_observation AS nb_observation, 
        (SELECT id FROM dataType WHERE dataType.nom = 'spectre')) AS tmp
         WHERE NOT EXISTS (
        SELECT * FROM neuron 
        WHERE pool_id=:pool_id AND ratioValX=:ratioValX AND ratioValY=:ratioValY) 
        LIMIT 1
        ";
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':typeNeuron', "input", PDO::PARAM_STR);
        $stmt->bindValue(':nb_observation', $this->nbObervations, PDO::PARAM_INT);
        $stmt->bindValue(':ratioValX', $this->ratioValX, PDO::PARAM_STR);
        $stmt->bindValue(':ratioValY', $this->ratioValY, PDO::PARAM_STR);
        $stmt->bindValue(':thresh', $this->thresh, PDO::PARAM_STR);
        $stmt->bindValue(':pool_id', $this->pool_id, PDO::PARAM_INT);

        $ok = $stmt->execute();
        if ($ok){
            return true;
        }else {
            return false;
        }
        
    }

    public function saveActivity($date_time){
        $db = static::getDB();

        $ativityJson = '{"activityX":'. $this->activityX.', "activityY":' . $this->activityY . '}';

        $sql = "INSERT INTO `activation_neuron` (neuron_id, activity, date_time)
        SELECT (SELECT id FROM neuron WHERE neuron.ratioValX = :ratioValX),
         :ativityJson, :date_time
        ";
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':ratioValX', $this->ratioValX, PDO::PARAM_STR);
        $stmt->bindValue(':ativityJson', $ativityJson, PDO::PARAM_STR);
        $stmt->bindValue(':date_time', $date_time, PDO::PARAM_STR);

        $ok = $stmt->execute();
        if ($ok) {
            return true;
        } else {
            return false;
        }
        
    }



    public function setParameters($ratioValX, $ratioValY, $nbObervation, $thresh){
        $this->setRatioValX($ratioValX);
        $this->setRatioValY($ratioValY);
        $this->setNbObservation($nbObervation);
        $this->setThresh($thresh);
    }

    public function computeActivity(){
        $this->activityX = $this->ratioValX;
        $this->activityY = $this->ratioValY;
    }

    public function getActivityFromTable(){
        $db = static::getDB();

        $sql = "";

        $stmt = $db->prepare($sql);
    }

    protected function update()
    {
    }



    protected function checkIsAlreadyExist()
    {
        $db = static::getDB();

        $sql = "SELECT DISTINCT n.id AS neuron_id, d.id AS datatype_id, p.id AS pool_id, t.date_time AS date_time, t.valueX, t.valueY
        FROM neuron AS n
        LEFT JOIN pool AS p ON (p.id = n.pool_id)
        LEFT JOIN dataType AS d ON (d.id = n.datatype_id)
        LEFT JOIN timeseries AS t ON (t.dataType_id = d.id)
        LEFT JOIN record AS r ON (r.id = t.record_id)
        WHERE n.ratioValX IS NOT NULL 
        AND n.seuil IS NOT NULL 
        AND id_neuron_1 IS NULL 
        AND id_neuron_2 IS NULL";
    }


    public function getOutput()
    {
        return $this->output;
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
        $this->ratioValX = $valX;
    }

    public function setRatioValY($valY)
    {
        $this->ratioValY = $valY;
    }

    public function addNewObservation(){
        $this->nbObervations++;
    }

    public function getNbObservation(){
        return $this->nbObervations;
    }

    public function setNbObservation($nbObervation)
    {
        return $this->nbObervations = $nbObervation;
    }

    public function getThresh(){
        return $this->thresh;
    }


    public function setThresh($thresh)
    {
        return $this->thresh = $thresh;
    }

    public function activate(){
        $this->isActivated = true;

        return true;
    }

    public function deactivate()
    {
        $this->isActivated = false;

        return true;
    }
}