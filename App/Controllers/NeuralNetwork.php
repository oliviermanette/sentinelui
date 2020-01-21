<?php

namespace App\Models;

use \Core\View;
use PDO;
use App\Config;
use \App\Models\Peak;
use \App\Models\RecordManager;
use App\Utilities;

/**
 * Neural Network object
 *
 * PHP version 7.0
 */
class NeuralNetwork extends \Core\Controller
{
    private $id;
    private $pool_id;

    private $nbLayers;
    private $listLayerArr;
    
    private $nbNeuronsInput;
    private $nbNeuronsRelation;
    private $nbNeuronsCategory;

      /**
     * constructor
     *
     * @return void
     */
    function __construct()
    {
        $this->id = spl_object_id($this);
    }

    public function create(){

    }

    public function delete(){
        //Empty layers

        //Delete object
    }

    public function update(){

    }

    public function computeNbNeuronsRelations(){

    }

    public function getNeuronsInputToActivate(){

    }

    public function getActiveInputNeurons(){

    }

    public function createCategory(){

    }

    public function getLayers(){
        return $this->listLayerArr;
    }

    public function getNbLayers()
    {
        return $this->nbLayers;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPoolId(){
        return $this->pool_id;
    }

    public function getNbNeuronsInput(){
        return $this->nbNeuronsInput;
    }

    public function getNbNeuronsRelations()
    {
        return $this->nbNeuronsRelations;
    }

    public function getNbNeuronsCategory()
    {
        return $this->nbNeuronsCategory;
    }

    function __destruct()
    {
        
    }
}