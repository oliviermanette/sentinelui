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
    public function profileViewAction()
    {
        Auth::rememberRequestedPage();

        $user = Auth::getUser();

        View::renderTemplate('Profile/index.html', [
            'user' => $user
        ]);
    }

    /**
     * Show the index page for /support
     *
     * @return void
     */
    public function supportViewAction()
    {
        View::renderTemplate('Support/index.html', []);
    }

    /**
     * update the profil after having submitted the form in the profile page
     *
     * @return void
     */
    public function updateAction()
    {
        //Get data from form
        $dataProfil = $_POST;
        //Get the current user
        $user = Auth::getUser();
        $user_id = $user->id;
        //Edit account by updating the DB
        if (UserManager::editAccount($user_id, $dataProfil)) {
            Flash::addMessage('Mise à jour réussie du profil');
        } else {
            Flash::addMessage('Erreur avec la mise à jour du profil', Flash::WARNING);
        }

        $this->redirect(Auth::getReturnToPage());
    }
}
