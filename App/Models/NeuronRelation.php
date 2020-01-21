<?php

namespace App\Models;

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
    private $output;
    private $delay;

    public function __construct($neuronInputId_1, $neuronInputId_2)
    {
        $this->delay = 0;
        $this->output = 0;
        $this->ratioValX = 0;
        $this->ratioValY = 0;
    }

    protected function computeActivity()
    {
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

    public function computeRatioValX(){

    }

    public function computeRatioValY()
    {
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
