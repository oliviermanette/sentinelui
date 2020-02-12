<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\UserManager;
use \App\Models\SettingManager;
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

        Auth::rememberRequestedPage();

        //Get the settings of the current user
        $settingsArr = $this->getSettingsForCurrentUser();

        //Get the shock threshold and inclinometer threshold
        //TODO : change and make it more reusable
        $settingsShock = $settingsArr[0];
        $settingsInclinometer = $settingsArr[1];

        View::renderTemplate('Profile/settings.html', [
            'settingsShock' => $settingsShock,
            'settingsInclinometer' => $settingsInclinometer
        ]);

    }

    public function updateAction(){
        $user = Auth::getUser();
        $user_id = $user->id;

        $shockThresh = $_POST["shockThresh"];
        $inclinometerThresh = $_POST["inclinometerThresh"];

        if (SettingManager::updateShockThresh($user_id, $shockThresh) &&
        SettingManager::updateInclinometerThresh($user_id, $inclinometerThresh)){
            Flash::addMessage('Mise à jour réussie des paramètres');
        }
        else {
            Flash::addMessage('Erreur avec la mise à jour des paramètres', Flash::WARNING);
        }
        $this->redirect(Auth::getReturnToPage());
        
    }

    public function getSettingsForCurrentUser(){
        $user = Auth::getUser();
        $user_id = $user->id;
        $settings = SettingManager::findByUserId($user_id);

        return $settings;
    }

}
