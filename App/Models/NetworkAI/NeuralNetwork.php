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

    private $needToAddNewNeurons;

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

        $this->needToAddNewNeurons = true;

        for ($i = 0; $i < $nbLayers; $i++){
            $layer = new Layer($pool_id, 0);
            array_push($this->listLayerArr, $layer);
        }
    }

    public function save(){

    }

    public function setUp($peakArr){
        $nbrePeak = count($peakArr);

        $this->setUpAll($peakArr);
    }

    private function setUpAll($peakArr){

        $nbrePeak = count($peakArr);
        //$nbrePeak = 3;
        $nbreNeuronsSensorielTotal = $nbrePeak * 2;

        //Set up the first layer (neuron sensoriel)
        $this->setUpNeuronSensoriel($nbreNeuronsSensorielTotal);
        //Set up the second layer (associative and relations)
        $this->setUpSecondLayer($nbrePeak);
        //Set up the third layer (aggregateur)
        $this->setUpNeuronAggregateur($nbrePeak);
        //Set up the fourth later (category)
        $this->setUpNeuronCategory();
        //Set up the last layer
        $this->setUpNeuronSuperCategory();

        echo "\n Set up is done \n";
        echo "Neural Network has now : " . $this->getNbTotalNeurons() . " neurons. \n";
    }

    private function setUpNeuronSensoriel($nbreNeuronsSensoriel){
        //Creation des neurons sensoriels 
        $this->listLayerArr[0]->setTypeLayer("sensoriel");
        //We multiply by two because we want to have a neuron for frequencies and another one for amplitude
        //and for each peak
        $this->getLayerSensoriel()->addNewNeuron($nbreNeuronsSensoriel/2, "sensoriel", "x");
        $this->getLayerSensoriel()->addNewNeuron($nbreNeuronsSensoriel/2, "sensoriel", "y");
        $this->nbNeuronsSensoriel = $nbreNeuronsSensoriel;
        echo "\n##Layer sensoriel has now : " . $this->getLayerSensoriel()->getNumberTotalNeurons() . " neurons. \n";
        echo "  ==> " . $this->getLayerSensoriel()->getNumberTotalNeurons("x") . " neurons X. \n";
        echo "  ==> " . $this->getLayerSensoriel()->getNumberTotalNeurons("y") . " neurons Y. \n";

        //Save
        //$isExist =  $this->getLayerSensoriel()->checkIfAreadyExistOnDB();
       
        $isExist = false;
        $this->needToAddNewNeurons = false;
        if ($this->needToAddNewNeurons){
            $this->getLayerSensoriel()->saveNeuronsToDB();
        }
       

    }

    private function setUpSecondLayer($nbreNeuronSensorielInput){
        //Creation de la seconde couche : neuron relation and associative
        $this->listLayerArr[1]->setTypeLayer("relation");

        $nbreNeuronsRelation = Utilities::nbreCombinaison(2, $nbreNeuronSensorielInput);

        $nbreNeuronsRelation *= 2; //For x and y 
        //echo "Nbre neuron relation needed : ". $nbreNeuronRelation ."\n";
        $this->getLayerRelations()->addNewNeuron($nbreNeuronsRelation/2, "relation", "x");
        $this->getLayerRelations()->addNewNeuron($nbreNeuronsRelation / 2, "relation", "y");
        $this->nbNeuronsRelation = $nbreNeuronsRelation;

        $nbNeuronsAssociative = $nbreNeuronsRelation;
        $this->getLayerRelations()->addNewNeuron($nbNeuronsAssociative / 2, "associative", "x");
        $this->getLayerRelations()->addNewNeuron($nbNeuronsAssociative/2, "associative","y");
        $this->nbNeuronsAssociative = $nbNeuronsAssociative;

        $arrayCombination = Utilities::getCombinations(2, $nbreNeuronSensorielInput);

        //$arrayCombination = array((array(1,2))); 
        //$arrayCombination = array(array(1, 2), array(1, 3), array(2, 3)); 
        //for each relation X 
        $idNbNeuronRelation = 1;
        foreach ($arrayCombination as $combination){
            $key = array_keys($combination)[0];
            $value = array_values($combination)[0];
           
            $id1= $key;
            $id2= $value;
           
            $neuronsSensorielsTaggedX = $this->getLayerSensoriel()->getNeuronsWithTag("x");
            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputX_1 = $neuronsSensorielsTaggedX[$id1 - 1];
            $neuronSensorielInputX_2 = $neuronsSensorielsTaggedX[$id2 - 1];

            $neuronSensorielInputY_1 = $neuronsSensorielsTaggedY[$id1 - 1];
            $neuronSensorielInputY_2 = $neuronsSensorielsTaggedY[$id2 - 1];
            

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputX_1, $neuronSensorielInputX_2 );

            //$this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;
            
        }
        //For each relation Y
        foreach ($arrayCombination as $combination) {
            $key = array_keys($combination)[0];
            $value = array_values($combination)[0];

            $id1 = $key;
            $id2 = $value;
            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputY_1 = $neuronsSensorielsTaggedY[$id1 - 1];
            $neuronSensorielInputY_2 = $neuronsSensorielsTaggedY[$id2 - 1];

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputY_1, $neuronSensorielInputY_2);

            //$this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;

        }
        //for each associative X
        foreach ($arrayCombination as $combination) {
            $key = array_keys($combination)[0];
            $value = array_values($combination)[0];

            $id1 = $key;
            $id2 = $value;

            $neuronsSensorielsTaggedX = $this->getLayerSensoriel()->getNeuronsWithTag("x");
            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputX_1 = $neuronsSensorielsTaggedX[$id1 - 1];
            $neuronSensorielInputX_2 = $neuronsSensorielsTaggedX[$id2 - 1];

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputX_1, $neuronSensorielInputX_2);

            //$this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;
        }
        ////For each associative Y
        foreach ($arrayCombination as $combination) {
            $key = array_keys($combination)[0];
            $value = array_values($combination)[0];

            $id1 = $key;
            $id2 = $value;

            $neuronsSensorielsTaggedY = $this->getLayerSensoriel()->getNeuronsWithTag("y");

            $neuronSensorielInputY_1 = $neuronsSensorielsTaggedY[$id1 - 1];
            $neuronSensorielInputY_2 = $neuronsSensorielsTaggedY[$id2 - 1];

            $this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->setInput($neuronSensorielInputY_1, $neuronSensorielInputY_2);

            //$this->getLayerRelations()->getNeuronId($idNbNeuronRelation)->getInfoConnection();
            $idNbNeuronRelation++;
        }

        echo "\n##Layer relation has now : " . $this->getLayerRelations()->getNumberTotalNeurons() . " neurons. \n";
        echo "  ==> " . $this->getLayerRelations()->getNumberTotalNeurons("x") . " neurons X. \n";
        echo "  ==> " . $this->getLayerRelations()->getNumberTotalNeurons("y") . " neurons Y. \n";
        echo "          ==> " . $this->getLayerRelations()->getNumberTotalNeuronsOftype("associative") . " associative. \n";
        echo "          ==> " . $this->getLayerRelations()->getNumberTotalNeuronsOftype("relation") . " relation. \n";

        if ($this->needToAddNewNeurons){
            $this->getLayerRelations()->saveNeuronsToDB();
        }
       

    }

    private function setUpNeuronAggregateur($nbrePeak){
        $nbreNeuronsAggregateurs = $nbrePeak;
    
        $nbreNeuronRelationX = count($this->getLayerRelations()->getNeuronsWithTypeAndTag("relation","x"));
        $nbreNeuronsAggregateurs = $nbreNeuronRelationX;
        //Creation de la troisième couche : neurons aggreagateur
        $this->listLayerArr[2]->setTypeLayer("aggregateur");
        $this->getLayerAggregateur()->addNewNeuron($nbreNeuronsAggregateurs, "aggregateur", "x");
        $this->getLayerAggregateur()->addNewNeuron($nbreNeuronsAggregateurs, "aggregateur", "y");

        $this->nbNeuronsAggregateur = $nbreNeuronsAggregateurs*2;
        $neuronsAssociativeXArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("associative","x");
        $neuronsRelationsXArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("relation", "x");

        $countAggregateur = 1;
        //count(neuronRelationArr) = count(neuronsAssociativeArr)
        //For X
        for ($i = 0; $i < count($neuronsRelationsXArr); $i++ ){
            $neuron_relation_x1 = $neuronsRelationsXArr[$i];
            $neuron_associative_x1 = $neuronsAssociativeXArr[$i];
            $this->getLayerAggregateur()->getNeuronId($countAggregateur)->setInput($neuron_relation_x1, $neuron_associative_x1);
            //$this->getLayerAggregateur()->getNeuronId($countAggregateur)->getInfoConnection();
            $countAggregateur++;
        }

        //For Y
        $neuronsAssociativeYArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("associative", "y");
        $neuronsRelationsYArr = $this->getLayerRelations()->getNeuronsWithTypeAndTag("relation", "y");
        for ($i = 0; $i < count($neuronsRelationsYArr); $i++) {
            $neuron_relation_y1 = $neuronsRelationsYArr[$i];
            $neuron_associative_y1 = $neuronsAssociativeYArr[$i];
            $this->getLayerAggregateur()->getNeuronId($countAggregateur)->setInput($neuron_relation_y1, $neuron_associative_y1);
            //$this->getLayerAggregateur()->getNeuronId($countAggregateur)->getInfoConnection();
            $countAggregateur++;
        }

         echo "\n##Layer aggregateur has now : " . $this->getLayerAggregateur()->getNumberTotalNeurons() . " neurons. \n";
        
        if ($this->needToAddNewNeurons) {
            echo "GOOOO !";
            $this->getLayerAggregateur()->saveNeuronsToDB();
        }
        
    }

    private function setUpNeuronCategory(){
        $this->listLayerArr[3]->setTypeLayer("category");
        $this->getLayerCategory()->addNewNeuron(2, "category");
        $this->nbNeuronsCategory = 2;

        $neuronsAggregateurXArr = $this->getLayerAggregateur()->getNeuronsWithTag("x");
        $neuronsAggregateurYArr = $this->getLayerAggregateur()->getNeuronsWithTag("y");

        $this->getLayerCategory()->getNeuronId(1)->setInput($neuronsAggregateurXArr);
        //$this->getLayerCategory()->getNeuronId(1)->getInfoConnection();
        $this->getLayerCategory()->getNeuronId(2)->setInput($neuronsAggregateurYArr);
        //$this->getLayerCategory()->getNeuronId(2)->getInfoConnection();

        echo "\n##Layer category has now : " . $this->getLayerCategory()->getNumberTotalNeurons() . " neurons. \n";

        if ($this->needToAddNewNeurons) {
            $this->getLayerCategory()->saveNeuronsToDB();
        }
    }

    private function setUpNeuronSuperCategory(){
        //Creation de la dernière couche : supercategory
        $this->listLayerArr[4]->setTypeLayer("superCategory");

        $neuronsCategoryXArr = $this->getLayerCategory()->getNeuronsWithTag("x");
        $neuronsCategoryYArr = $this->getLayerCategory()->getNeuronsWithTag("y");

        $this->getLayerSuperCategory()->addNewNeuron(1, "superCategory");
        $this->getLayerSuperCategory()->getNeuronId(1)->setInput($neuronsCategoryXArr);
        $this->getLayerSuperCategory()->getNeuronId(1)->setInput($neuronsCategoryYArr);
        $this->nbNeuronsSuperCategory = 1;
        echo "\n##Layer superCategory has now : " . $this->getLayerSuperCategory()->getNumberTotalNeurons() . " neurons. \n";

        if ($this->needToAddNewNeurons) {
            $this->getLayerSuperCategory()->saveNeuronsToDB();
        }
    }
   

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

    public function getNbNeuronsSensoriel(){
        return $this->nbNeuronsSensoriel;
    }

    public function getNbNeuronsRelations()
    {
        return $this->nbNeuronsRelation;
    }

    public function getNbNeuronsAssociative()
    {
        return $this->nbNeuronsAssociative;
    }
    public function getNbNeuronsAggregateurs()
    {
        return $this->nbNeuronsAggregateur;
    }

    public function getNbNeuronsCategory()
    {
        return $this->nbNeuronsCategory;
    }
    public function getNbNeuronsSuperCategory()
    {
        return $this->nbNeuronsSuperCategory;
    }

    public function getNbTotalNeurons(){
        /*echo "\n nbre neuron sensoriel : ". $this->getNbNeuronsSensoriel() ."\n";
        echo "\n nbre neuron relations : " . $this->getNbNeuronsRelations() . "\n";
        echo "\n nbre neuron associatives : " . $this->getNbNeuronsAssociative() . "\n";
        echo "\n nbre neuron aggregateurs : " . $this->getNbNeuronsAggregateurs() . "\n";
        echo "\n nbre neuron category : " . $this->getNbNeuronsCategory() . "\n";
        echo "\n nbre neuron super category : " . $this->getNbNeuronsSuperCategory() . "\n";*/
        $nbreTot = $this->getNbNeuronsSensoriel() + $this->getNbNeuronsRelations() 
        + $this->getNbNeuronsAssociative() + $this->getNbNeuronsAggregateurs()
        + $this->getNbNeuronsCategory() + $this->getNbNeuronsSuperCategory();
        return $nbreTot;
    }

    function __destruct()
    {
        
    }
}