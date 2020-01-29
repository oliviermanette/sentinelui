<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\UserManager;
use \App\Auth;
use \App\Flash;


/**
 * Setting controller
 *
 * PHP version 7.0
 */
class ControllerSetting extends Authenticated
{

    /**
     * Show the index page for /settings
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Profile/settings.html', [
        ]);

    }

}
