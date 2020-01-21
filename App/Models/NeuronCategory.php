<?php

namespace App\Models;

/**
 * Neuron Category object
 *
 * PHP version 7.0
 */
class NeuronCategory extends Neuron
{

    private $label;
    private $thresh;

    public function __construct()
    {
    }
    
    protected function computeActivity(){

    }
    protected function update(){
        
    }


    public function getThresh()
    {
        return $this->thresh;
    }


    public function setThresh($thresh)
    {
        return $this->thresh = $thresh;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        return $this->label = $label;
    }

}
