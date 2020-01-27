<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;


/**
 * Setting controller
 *
 * PHP version 7.0
 */
class ControllerSensors extends Authenticated
{

    public function __construct()
    {
    }

    public function indexAction()
    {
        View::renderTemplate('Sensors/index.html', []);
    }
}
