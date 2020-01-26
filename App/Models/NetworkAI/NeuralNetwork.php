<?php

namespace App\Models\NetworkAI;

use \Core\View;
use PDO;
use App\Config;
use \App\Models\Peak;
use \App\Models\NetworkAI\Layer;
use \App\Models\RecordManager;
use App\Utilities;

/**
 * Neural Network object
 *
 * PHP version 7.0
 */
class NeuralNetwork extends \Core\Model
{
    private $id;
    private $pool_id;

    private $nbLayers;
    private $listLayerArr;
    //private $layer;
    
    private $nbNeuronsSensoriel;
    private $nbNeuronsRelation;
    private $nbNeuronsAssociative;
    private $nbNeuronsAggregateur;
    private $nbNeuronsCategory;
    private $nbNeuronsSuperCategory;

      /**
     * constructor
     *
     * @return void
     */
    function __construct($nbLayers, $pool_id)
    {
        $this->id = spl_object_id($this);
        $this->listLayerArr = array();
        $this->nbLayers = $nbLayers;
        $this->pool_id = $pool_id;

        for ($i = 0; $i < $nbLayers; $i++){
            $layer = new Layer($pool_id, 0);
            array_push($this->listLayerArr, $layer);
        }
        /*$this->listLayerArr[0]->setTypeLayer("sensoriel");
        $this->listLayerArr[1]->setTypeLayer("relation");
        $this->listLayerArr[2]->setTypeLayer("aggregateur");
        $this->listLayerArr[3]->setTypeLayer("category");
        $this->listLayerArr[4]->setTypeLayer("super_category");*/
        //We have now three empty layers
    }

    public function setUp($peakArr){
        $nbrePeak = count($peakArr);

        $this->setUpAll($peakArr);
        exit();
        //Creation des neurons sensoriels 
        $this->listLayerArr[0]->setTypeLayer("sensoriel");
        //We multiply by two because we want to have a neuron for frequencies and another one for amplitude
        //and for each peak
        $nbreNeurons = $nbrePeak * 2;
        $this->getLayerSensoriel()->addNewNeuron($nbreNeurons, "sensoriel");

        echo "Layer sensoriel has now : ". $this->getLayerSensoriel()->getNumberTotalNeurons() ." neurons. \n";
        
        //Creation de la seconde couche : neuron relation and associative
        $this->listLayerArr[1]->setTypeLayer("relation");
        $nbreNeuronRelation = Utilities::nbreCombinaison(2, $nbrePeak);
        $nbreNeuronRelation *= 2;//For x and y 

        $this->getLayerRelations()->addNewNeuron($nbreNeuronRelation, "relation");
        $neuron_sensoriel_x1 = $this->getLayerSensoriel()->getNeuronId(1);
        $neuron_sensoriel_x2 = $this->getLayerSensoriel()->getNeuronId(2);
        $neuron_sensoriel_y1 = $this->getLayerSensoriel()->getNeuronId(3);
        $neuron_sensoriel_y2 = $this->getLayerSensoriel()->getNeuronId(4);

        $this->getLayerRelations()->getNeuronId(1)->setInput($neuron_sensoriel_x1, $neuron_sensoriel_x2);
        $this->getLayerRelations()->getNeuronId(2)->setInput($neuron_sensoriel_y1, $neuron_sensoriel_y2);
        $nbreNeuronAssociative = $nbreNeuronRelation;
        $this->getLayerRelations()->addNewNeuron($nbreNeuronAssociative, "associative");
        $this->getLayerRelations()->getNeuronId(3)->setInput($neuron_sensoriel_x1, $neuron_sensoriel_x2);
        $this->getLayerRelations()->getNeuronId(4)->setInput($neuron_sensoriel_y1, $neuron_sensoriel_y2);

        echo "Layer relation has now : " . $this->getLayerSensoriel()->getNumberTotalNeurons() . " neurons. \n";

        //Creation de la troisième couche : neurons aggreagateur
        $this->listLayerArr[2]->setTypeLayer("aggregateur");
        $this->getLayerAggregateur()->addNewNeuron(2, "aggregateur");
        $neuron_relation_x1 = $this->getLayerRelations()->getNeuronId(1);
        $neuron_associative_x1 = $this->getLayerRelations()->getNeuronId(3);
        $this->getLayerAggregateur()->getNeuronId(1)->setInput($neuron_relation_x1, $neuron_associative_x1);
        $neuron_relation_y1 = $this->getLayerRelations()->getNeuronId(2);
        $neuron_associative_y1 = $this->getLayerRelations()->getNeuronId(4);
        $this->getLayerAggregateur()->getNeuronId(2)->setInput($neuron_relation_y1, $neuron_associative_y1);
        
        echo "Layer aggregateur has now : " . $this->getLayerAggregateur()->getNumberTotalNeurons() . " neurons. \n";

        //Creation de la quatrième couche : neurones category
        $this->listLayerArr[3]->setTypeLayer("category");
        $nbreNeuronCategory = $nbreNeuronRelation;
        $this->getLayerCategory()->addNewNeuron($nbreNeuronCategory, "category");
        //Get all neuron aggregateur
        $neuronAggregateurXArr = array();
        $neuron_aggregateur_x1 = $this->getLayerAggregateur()->getNeuronId(1);
        array_push($neuronAggregateurXArr, $neuron_aggregateur_x1);
        $this->getLayerCategory()->getNeuronId(1)->setInput($neuronAggregateurXArr);
        $neuronAggregateurYArr = array();
        $neuron_aggregateur_y1 = $this->getLayerAggregateur()->getNeuronId(2);
        array_push($neuronAggregateurYArr, $neuron_aggregateur_y1);
        $this->getLayerCategory()->getNeuronId(2)->setInput($neuronAggregateurYArr);

        echo "Layer category has now : " . $this->getLayerCategory()->getNumberTotalNeurons() . " neurons. \n";

        //Creation de la dernière couche : supercategory
        $this->listLayerArr[4]->setTypeLayer("superCategory");
        $this->getLayerSuperCategory()->addNewNeuron(1, "superCategory");
        echo "Layer superCategory has now : " . $this->getLayerSuperCategory()->getNumberTotalNeurons() . " neurons. \n";
    }

    public function setUpAll($peakArr){

        //$nbrePeak = count($peakArr);
        $nbrePeak = 3;
        $nbreNeuronsSensorielTotal = $nbrePeak * 2;

        //Set up the first layer (neuron sensoriel)
        $this->setUpNeuronSensoriel($nbreNeuronsSensorielTotal);
        //Set up the second layer (associative and relations)
        $this->setUpSecondLayer($nbrePeak);
        //Set up the third layer (aggregateur)
        $this->setUpNeuronAggregateur($nbrePeak);
        
    }

    public function setUpNeuronSensoriel($nbreNeuronsSensoriel){
        //Creation des neurons sensoriels 
        $this->listLayerArr[0]->setTypeLayer("sensoriel");
        //We multiply by two because we want to have a neuron for frequencies and another one for amplitude
        //and for each peak
        $this->getLayerSensoriel()->addNewNeuron($nbreNeuronsSensoriel/2, "sensoriel", "x");
        $this->getLayerSensoriel()->addNewNeuron($nbreNeuronsSensoriel/2, "sensoriel", "y");

        echo "Layer sensoriel has now : " . $this->getLayerSensoriel()->getNumberTotalNeurons() . " neurons. \n";
        echo "  ==> " . $this->getLayerSensoriel()->getNumberTotalNeurons("x") . " neurons X. \n";
        echo "  ==> " . $this->getLayerSensoriel()->getNumberTotalNeurons("y") . " neurons Y. \n";

    }

    public function setUpSecondLayer($nbreNeuronSensorielInput){
        //Creation de la seconde couche : neuron relation and associative
        $this->listLayerArr[1]->setTypeLayer("relation");

        $nbreNeuronRelation = Utilities::nbreCombinaison(2, $nbreNeuronSensorielInput);

        $nbreNeuronRelation *= 2; //For x and y 
        //echo "Nbre neuron relation needed : ". $nbreNeuronRelation ."\n";
        $this->getLayerRelations()->addNewNeuron($nbreNeuronRelation/2, "relation", "x");
        $this->getLayerRelations()->addNewNeuron($nbreNeuronRelation / 2, "relation", "y");
        $nbreNeuronAssociative = $nbreNeuronRelation;
        $this->getLayerRelations()->addNewNeuron($nbreNeuronAssociative / 2, "associative", "x");
        $this->getLayerRelations()->addNewNeuron($nbreNeuronAssociative/2, "associative","y");

        
        //To automatize
        //$arrayCombination = array((array(1,2))); 
        $arrayCombination = array(array(1, 2), array(1, 3), array(2, 3)); 
        //for each relation X 
        $idNbNeuronRelation = 1;
        foreach ($arrayCombination as $combination){
            print_r($combination);
            $id1=$combination[0];
            $id2=$combination[1];
           
            $neuronsSensorielsTaggedX = $this->getLayerSensoriel()->getNeuronsWithTag("x");
            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputX_1 = $neuronsSensorielsTaggedX[$id1 - 1];
            $neuronSensorielInputX_2 = $neuronsSensorielsTaggedX[$id2 - 1];

            $neuronSensorielInputY_1 = $neuronsSensorielsTaggedY[$id1 - 1];
            $neuronSensorielInputY_2 = $neuronsSensorielsTaggedY[$id2 - 1];
            

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputX_1, $neuronSensorielInputX_2 );

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;
            
        }
        //For each relation Y
        foreach ($arrayCombination as $combination) {
            print_r($combination);
            $id1 = $combination[0];
            $id2 = $combination[1];

            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputY_1 = $neuronsSensorielsTaggedY[$id1 - 1];
            $neuronSensorielInputY_2 = $neuronsSensorielsTaggedY[$id2 - 1];

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputY_1, $neuronSensorielInputY_1);

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;

        }
        //for each associative X
        foreach ($arrayCombination as $combination) {
            print_r($combination);
            $id1 = $combination[0];
            $id2 = $combination[1];

            $neuronsSensorielsTaggedX = $this->getLayerSensoriel()->getNeuronsWithTag("x");
            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputX_1 = $neuronsSensorielsTaggedX[$id1 - 1];
            $neuronSensorielInputX_2 = $neuronsSensorielsTaggedX[$id2 - 1];

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputX_1, $neuronSensorielInputX_2);

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;
        }
        ////For each associative Y
        foreach ($arrayCombination as $combination) {
            print_r($combination);
            $id1 = $combination[0];
            $id2 = $combination[1];

            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputY_1 = $neuronsSensorielsTaggedY[$id1 - 1];
            $neuronSensorielInputY_2 = $neuronsSensorielsTaggedY[$id2 - 1];

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputY_1, $neuronSensorielInputY_1);

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;
        }

        echo "Layer relation has now : " . $this->getLayerRelations()->getNumberTotalNeurons() . " neurons. \n";
        echo "  ==> " . $this->getLayerRelations()->getNumberTotalNeurons("x") . " neurons X. \n";
        echo "  ==> " . $this->getLayerRelations()->getNumberTotalNeurons("y") . " neurons Y. \n";
        echo "          ==> " . $this->getLayerRelations()->getNumberTotalNeuronsOftype("associative") . " associative. \n";
        echo "          ==> " . $this->getLayerRelations()->getNumberTotalNeuronsOftype("relation") . " relation. \n";


    }

    public function setUpNeuronAggregateur($nbrePeak){
        $nbreNeuronAggregateurs = $nbrePeak;
        $nbreNeuronAggregateurs *= 2;
        //Creation de la troisième couche : neurons aggreagateur
        $this->listLayerArr[2]->setTypeLayer("aggregateur");
        $this->getLayerAggregateur()->addNewNeuron($nbreNeuronAggregateurs, "aggregateur");

        $neuronsAssociativeXArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("associative","x");
        $neuronsRelationsXArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("relation", "x");

        $countAggregateur = 1;
        //count(neuronRelationArr) = count(neuronsAssociativeArr)
        //For X
        for ($i = 0; $i < count($neuronsRelationsXArr); $i++ ){
            $neuron_relation_x1 = $neuronsRelationsXArr[$i];
            $neuron_associative_x1 = $neuronsAssociativeXArr[$i];
            $this->getLayerAggregateur()->getNeuronId($countAggregateur)->setInput($neuron_relation_x1, $neuron_associative_x1);
            $this->getLayerAggregateur()->getNeuronId($countAggregateur)->getInfoConnection();
            $countAggregateur++;
        }

        //For Y
        $neuronsAssociativeYArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("associative", "y");
        $neuronsRelationsYArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("relation", "y");
        for ($i = 0; $i < count($neuronsRelationsYArr); $i++) {
            $neuron_relation_y1 = $neuronsRelationsYArr[$i];
            $neuron_associative_y1 = $neuronsAssociativeYArr[$i];
            $this->getLayerAggregateur()->getNeuronId($countAggregateur)->setInput($neuron_relation_y1, $neuron_associative_y1);
            $this->getLayerAggregateur()->getNeuronId($countAggregateur)->getInfoConnection();
            $countAggregateur++;
        }

        echo "Layer aggregateur has now : " . $this->getLayerAggregateur()->getNumberTotalNeurons() . " neurons. \n";
        echo "\n count aggregateur ".($countAggregateur-1) ."\n";
    }
    /*
    public function setUpOLD($peakArr, $date_time){
        
        //Set up neurons input
        $this->setUpNeuronsInput($peakArr, $date_time);


        //Set up neurons relations
        // 1. Compute number of relations needed
        $this->setUpNeuronsRelation($date_time);

        //echo "nbre neuron input : ". $this->listLayerArr[0]->getNumberTotalNeurons() ."\n";
        //print_r($this->listLayerArr[0]->getNeuronsArr());

        
    }*/
    /*
    public function setUpNeuronsInput($peakArr, $date_time){
        $neuron_id = 1;
        //On parcoure l'ensemble des peaks et on créer les neuronnes inputs
        foreach ($peakArr as $indexJVX => $amplitude) {
            //echo $amplitude ."\n";

            //Check if neuron activation
            $listIdNeuronsInput = NeuralNetwork::GetInputNeuronsToActivate($indexJVX);
            //print_r($listIdNeuronsInput);
            //echo "\n Liste neuron input to activte : \n";
            if (empty($listIdNeuronsInput)) {
                echo "\n Creation neuron input \n";
                $this->getLayerInput()->addNewNeuronInput($neuron_id);
                $this->getLayerInput()->getNeuronId($neuron_id)->setParameters($indexJVX, $amplitude, $nbObervation = 1, $thresh = 10);
                //Save neuronInput to DB
                $this->getLayerInput()->getNeuronId($neuron_id)->save();
                //Compute activity and save to the DB
                $this->getLayerInput()->getNeuronId($neuron_id)->computeActivity();
                $this->getLayerInput()->getNeuronId($neuron_id)->saveActivity($date_time);
                $neuron_id++;

                $this->nbNeuronsInput++;
            } else {
                //Compute activity
            }
        }
       
    }*/

    /*
    public function setUpNeuronsRelation($date_time){
        $this->computeNbNeuronsRelations($date_time);

        $listCombinationID = array(
            array(1, 2), array(1, 3), array(1, 4), array(1, 5),
            array(1, 6), array(2, 3), array(2, 4), array(2, 5), array(2, 6),
            array(3, 4), array(3, 5), array(3, 6), array(4, 5), array(4, 6),
            array(5, 6)
        );

        $neuron_id = 1;
        for ($i = 0; $i < count($listCombinationID); $i++) {
            $neuronID_1 = $listCombinationID[$i][0];
            $neuronID_2 = $listCombinationID[$i][1];
            $neuron_1 = $this->getLayerInput()->getNeuronId($neuronID_1);
            $neuron_2 = $this->getLayerInput()->getNeuronId($neuronID_2);

            $this->getLayerRelations()->addNewNeuronRelation($neuron_id, $neuron_1, $neuron_2);
            $this->getLayerRelations()->getNeuronId($neuron_id)->save();

            $this->getLayerRelations()->getNeuronId($neuron_id)->computeActivity();
            $this->getLayerRelations()->getNeuronId($neuron_id)->saveActivity($date_time);
            $neuron_id++;
            $this->nbNeuronsRelation++;
        }
    }*/


    public function computeNbNeuronsRelations($date_time)
    {
        $nbNeuronsRelation = Utilities::nbreCombinaison(2, $this->nbNeuronsInput);
        $this->nbNeuronsRelation = $nbNeuronsRelation;

        return $nbNeuronsRelation;
    }

    /**
     * On recherche dans la table neurone 
     * tous les enregistrements qui ont un seuil et un ratio Vx non null. 
     * neuron_id | pool_id | ratioValX
     * 
     */
    public static function GetInputNeuronsToActivate($valueX)
    {

        $db = static::getDB();

        $sql = "SELECT DISTINCT neuron_id, pool_id, ratioValX FROM (SELECT n.id AS neuron_id, d.id AS datatype_id, p.id AS pool_id, t.date_time AS date_time, n.ratioValX AS ratioValX
        FROM neuron AS n
        LEFT JOIN pool AS p ON (p.id = n.pool_id)
        LEFT JOIN dataType AS d ON (d.id = n.datatype_id)
        LEFT JOIN timeseries AS t ON (t.dataType_id = d.id)
        LEFT JOIN record AS r ON (r.id = t.record_id)
        WHERE n.ratioValX IS NOT NULL 
        AND n.seuil IS NOT NULL 
        AND id_neuron_1 IS NULL 
        AND id_neuron_2 IS NULL
        AND (n.ratioValX >= :valueX - :valueX *(n.seuil/100) AND n.ratioValX <= :valueX + :valueX*(n.seuil/100) ))AS tmp";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':valueX', $valueX, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 0) {
                return $results;
            } else {
                return array();
            }
        }
    }

    public function setUpFromTable(){

    }

    public function setParametersLayerInput(){

    }


    public function getLayerSensoriel()
    {
        return $this->listLayerArr[0];
    }


    public function getLayerRelations()
    {
        return $this->listLayerArr[1];
    }

    public function getLayerAggregateur()
    {
        return $this->listLayerArr[2];
    }

    public function getLayerCategory()
    {
        return $this->listLayerArr[3];
    }

    public function getLayerSuperCategory()
    {
        return $this->listLayerArr[4];
    }
  

    public function delete(){
        //Empty layers

        //Delete object
    }

    public function update(){

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