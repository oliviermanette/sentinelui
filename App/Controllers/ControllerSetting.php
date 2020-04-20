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
        var_dump($settingsArr);
        //Get the shock threshold and inclinometer threshold
        //TODO : change and make it more reusable

        $settingsShock = 0; //$settingsArr[0];
        $settingsInclinometer = 0; // $settingsArr[1];
        $settingsTimePeriod = 0; //$settingsArr[2];
        $settingsRangeInclinometer = 0; // $settingsArr[3];
        $settingsAlertEmailActivated = 0; // $settingsArr[4];


        View::renderTemplate('Profile/settings.html', [
            'settingsShock' => $settingsShock,
            'settingsInclinometer' => $settingsInclinometer,
            'settingsTimePeriod' => $settingsTimePeriod,
            'settingsRangeInclinometer' => $settingsRangeInclinometer,
            'settingsAlertEmailActivated' => $settingsAlertEmailActivated,
        ]);
    }

    public function updateAction()
    {
        $user = Auth::getUser();
        $user_email = $user->email;
        $group_name = $_SESSION['group_name'];
        $shockThresh = $_POST["shockThresh"];
        $inclinometerThresh = $_POST["inclinometerThresh"];
        $timePeriod = $_POST["rangeDateCheck"];
        $inclinometerRangeThresh = $_POST["inclinometerRangeThresh"];
        if (isset($_POST["alertSwitchNotification"])) {
            $alertNotification = 1;
        } else {
            $alertNotification = 0;
        }
        if (
            SettingManager::updateShockThresh($group_name, $shockThresh) &&
            SettingManager::updateInclinometerThresh($group_name, $inclinometerThresh) &&
            SettingManager::updateTimePeriodCheck($group_name, $timePeriod)
            && SettingManager::updateInclinometerRangeThresh($group_name, $inclinometerRangeThresh)
            && SettingManager::updateAlertNotification($user_email, $alertNotification)
        ) {
            Flash::addMessage('Mise à jour réussie des paramètres');
        } else {
            Flash::addMessage('Erreur avec la mise à jour des paramètres', Flash::WARNING);
        }
        $this->redirect(Auth::getReturnToPage());
    }

    public function getSettingsForCurrentUser()
    {
        $user = Auth::getUser();
        $user_id = $user->id;
        var_dump($_SESSION['group_name']);
        $group_name = $_SESSION['group_name'];

        $settingsArr = SettingManager::findByGroupName($group_name);

        $isAlertEmailActivated = SettingManager::checkIfAlertActivated($user->email);
        $tmpArr = array("isAlertEmailActivated" => $isAlertEmailActivated);
        array_push($settingsArr, $tmpArr);
        //$settingsArr["isAlertEmailActivated"] = $isAlertEmailActivated;
        return $settingsArr;
    }
}
