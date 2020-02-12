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

    /**
     * Show the index page for /profile
     *
     * @return void
     */
    public function indexAction()
    {
        Auth::rememberRequestedPage();
        View::renderTemplate('Profile/index.html', []);
    }

    /**
     * Show the index page for /support
     *
     * @return void
     */
    public function supportAction(){
        View::renderTemplate('Support/index.html', []);
    }

    public function updateAction(){


        $this->redirect(Auth::getReturnToPage());
    }
}
