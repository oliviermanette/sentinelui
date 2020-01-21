<?php

namespace App\Models;

/**
 * Neuron Input object
 *
 * PHP version 7.0
 */
class NeuronInput extends Neuron
{

    private $ratioValX;
    private $ratioValY;

    private $thresh;
    private $output;
    private $nbObervations;

    private $isActivated;

    public function __construct()
    {
    }

    protected function computeActivity(){

    }
    protected function update(){
        
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function computeRatioValX()
    {
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