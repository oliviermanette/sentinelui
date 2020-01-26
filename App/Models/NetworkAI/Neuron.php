<?php

namespace App\Models\networkAI;

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