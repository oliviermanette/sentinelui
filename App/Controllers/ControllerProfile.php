<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\UserManager;
use \App\Auth;
use \App\Flash;


/**
 * Profile controller
 *
 * PHP version 7.0
 */
class ControllerProfile extends Authenticated
{

    public function __construct()
    {
    }

    public function indexAction()
    {
        View::renderTemplate('Profile/index.html', []);
    }

    public function supportAction(){
        View::renderTemplate('Support/index.html', []);
    }
}
