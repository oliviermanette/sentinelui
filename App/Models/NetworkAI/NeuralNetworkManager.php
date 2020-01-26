<?php

namespace App\Models;
use \App\Models\NeuralNetwork;

use PDO;


class NeuralNetworkManager extends \Core\Model
{

    public function __construct()
    {
    
    }

    public static function createNeuralNetworkFromTable($pool_id){
        $dataArr = NeuralNetworkManager::getNeuralNetworkFromTable($pool_id);
        $neuralNetwork = new NeuralNetwork(3, $pool_id);
        $neuron_id = 1;
        print_r($dataArr);
        foreach ($dataArr as $neuron){
            $type = $neuron["type"];
            switch ($type){
                case "input":
                    $ratioValX =  $neuron["ratioValX"];
                    $ratioValY =  $neuron["ratioValY"];
                    $seuil =  $neuron["seuil"];
                    $nb_obervation =  $neuron["nb_obervation"];
                    $neuralNetwork->getLayerInput()->addNewNeuronInput($neuron_id);
                    $neuralNetwork->getLayerInput()->getNeuronId($neuron_id)->setParameters($ratioValX, $ratioValY, $nb_obervation, $seuil);
                    break;
                case "relation":
                    echo "relation";
                    break;
                case "category":
                    $label =  $neuron["label"];
                    $neurone1 =  $neuron["id_neuron_1"];
                    $neurone2 =  $neuron["id_neuron_2"];
                    break;
            }
            
            $neuron_id;
        }

        return $neuralNetwork;
        
    }


    public static function getNeuralNetworkFromTable($pool_id)
    {
        $db = static::getDB();

        $sql = "SELECT * FROM `neuron` AS n 
        WHERE pool_id = :pool_id";

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




}