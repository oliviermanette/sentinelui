<?php

namespace App\Models;

use App\Config;

/**
 * Peak object
 *
 * PHP version 7.0
 */
class Peak
{
    private $valX;
    private $valY;

    /**
     * constructor
     *
     * @param float $valX valX correspond to frequence axis value
     * @param float $valY valY correspond to amplitude axis value
     * @return void
     */
    function __construct($valX, $valY)
    {
        $this->valX = $valX;
        $this->valY = $valY;
    }

    /**
     * set frequence axis value
     *
     * @param float $valX valX correspond to frequence axis value
     * @return void
     */
    public function setValX($valX){
        $this->valX = $valX;
    }

    /**
     * set amplitude axis value
     *
     * @param float $valY valY correspond to amplitude axis value
     * @return void
     */
    public function setValY($valY)
    {
        $this->valY = $valY;
    }

    /**
     * get frequence axis value
     *
     * @return float
     */
    public function getValX(){
        return $this->valX;
    }

    /**
     * get amplitude axis value
     *
     * @return float
     */
    public function getValY()
    {
        return $this->valY;
    }
}
