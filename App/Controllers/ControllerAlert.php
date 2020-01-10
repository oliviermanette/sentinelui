<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AlertManager;
use \App\Auth;
use \App\Flash;


/**
 * Alert controller
 *
 * PHP version 7.0
 */
class ControllerAlert extends \Core\Controller
{

    public function __construct()
    {
    }

    public function indexAction()
    {

    }


    public function createAction()
    {

        $alertManager = new AlertManager();
        $alertManager->create();
       
    }

}
