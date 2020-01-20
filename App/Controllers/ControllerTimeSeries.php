<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\RecordManager;
use \App\Flash;



/**
 * Time Series controller
 * PHP version 7.0
 */

class ControllerTimeSeries extends \Core\Controller
{

    public function __construct()
    {
    }

    /**
     * TESTING PURPOSE
     * @return void
     */
    public function goTimeSeriesAction()
    {
        $group_name = "RTE";
        
        //INIT POOL
        $recordManager = new RecordManager();
        $recordManager->initPool($group_name);
    }


}