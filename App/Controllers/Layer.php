<?php

namespace App\Models;

use \Core\View;
use PDO;
use App\Config;
use App\Utilities;
use \App\Models\Neuron;
use \App\Models\NeuronInput;
use \App\Models\NeuronRelation;
use \App\Models\NeuronCategory;

/**
 * Layer object
 *
 * PHP version 7.0
 */
class Layer extends \Core\Controller
{

    private $nbNeurons;//Nb neurons that have the layer
    private $listNeuronsArr; //List of neurons that have the layer
    private $typeNeurons; //Type of neurons that have the layer
    /**
     * constructor
     *
     * @return void
     */
    function __construct($nbNeurons, $typeNeurons)
    {
        $this->nbNeurons = $nbNeurons;
        $this->listNeuronsArr = array();
        //Fill the layer with new neurons
        for ($i = 0; $i < $nbNeurons; $i++){
            if ($typeNeurons = "input"){
                $neuron = new NeuronInput();
            }else if ($typeNeurons = "relation"){
                $neuron = new NeuronRelation();
            }else if ($typeNeurons = "category") {
                $neuron = new NeuronCategory();
            }
            array_push($this->listNeuronsArr, $neuron);
            
        }
        
    }

    public function update(){

    }

    public function addNewNeuron($nbNeurons, $typeNeurons){
        for ($i = 0; $i < $nbNeurons; $i++) {
            if ($typeNeurons = "input") {
                $neuron = new NeuronInput();
            } else if ($typeNeurons = "relation") {
                $neuron = new NeuronRelation();
            } else if ($typeNeurons = "category") {
                $neuron = new NeuronCategory();
            }
            array_push($this->listNeuronsArr, $neuron);
        }
    }

}