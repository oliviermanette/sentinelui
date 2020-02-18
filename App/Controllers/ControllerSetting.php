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
        $settingsTimePeriod = $settingsArr[2];
        $settingsRangeInclinometer= $settingsArr[3];

        View::renderTemplate('Profile/settings.html', [
            'settingsShock' => $settingsShock,
            'settingsInclinometer' => $settingsInclinometer,
            'settingsTimePeriod' => $settingsTimePeriod,
            'settingsRangeInclinometer' => $settingsRangeInclinometer,
        ]);

    }

    public function updateAction(){

        $group_name = $_SESSION['group_name'];
        $shockThresh = $_POST["shockThresh"];
        $inclinometerThresh = $_POST["inclinometerThresh"];
        $timePeriod = $_POST["rangeDateCheck"];
        $inclinometerRangeThresh = $_POST["inclinometerRangeThresh"];

        if (SettingManager::updateShockThresh($group_name, $shockThresh) &&
        SettingManager::updateInclinometerThresh($group_name, $inclinometerThresh) &&
        SettingManager::updateTimePeriodCheck($group_name, $timePeriod) 
        && SettingManager::updateInclinometerRangeThresh($group_name, $inclinometerRangeThresh)){
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
        $group_name = $_SESSION['group_name'];

        $settings = SettingManager::findByGroupName($group_name);
        
        return $settings;
    }

}
