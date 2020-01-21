<?php

namespace App\Models;

/*

Controllers are classes that contain methods that are actions.

*/

abstract class Neuron
{
    protected $pool_id;
    protected $dataType;
    protected $valActivity;
    protected $dateTime;

    public function __construct()
    {
        $this->valActivity = 0;
    }

    abstract protected function computeActivity();
    abstract protected function update();


    protected function getPoolId(){
        return $this->pool_id;
    }


    protected function setPoolId($pool_id)
    {
        return $this->pool_id = $pool_id;
    }

    protected function getDateTime()
    {
        return $this->dateTime;
    }

    protected function getDataType()
    {
        return $this->dataType;
    }


}