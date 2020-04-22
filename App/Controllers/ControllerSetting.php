<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\UserManager;
use \App\Models\Settings\SettingGeneralManager;
use \App\Auth;
use \App\Flash;
use \App\Utilities;


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
        $first_inclination_thresh = Utilities::array_find_deep($settingsArr, "first_inclination_thresh");
        $second_inclination_thresh = Utilities::array_find_deep($settingsArr, "second_inclination_thresh");
        $third_inclination_thresh = Utilities::array_find_deep($settingsArr, "third_inclination_thresh");
        $shock_thresh = Utilities::array_find_deep($settingsArr, "shock_thresh");
        $isAlertEmailActivated = Utilities::array_find_deep($settingsArr, "isAlertEmailActivated");
        if ($first_inclination_thresh) {
            $first_inclination_thresh = $settingsArr[$first_inclination_thresh[0]];
        } else {
            $first_inclination_thresh = 0;
        }
        if ($second_inclination_thresh) {
            $second_inclination_thresh = $settingsArr[$second_inclination_thresh[0]];
        } else {
            $second_inclination_thresh = 0;
        }
        if ($third_inclination_thresh) {
            $third_inclination_thresh = $settingsArr[$third_inclination_thresh[0]];
        } else {
            $third_inclination_thresh = 0;
        }
        if ($shock_thresh) {
            $shock_thresh = $settingsArr[$shock_thresh[0]];
        } else {
            $shock_thresh = 1;
        }
        if ($isAlertEmailActivated) {
            $isAlertEmailActivated = $settingsArr[$isAlertEmailActivated[0]];
        } else {
            $isAlertEmailActivated = 0;
        }

        View::renderTemplate('Profile/settings.html', [
            'settingsFirstInclinationThresh' => $first_inclination_thresh,
            'settingsSecondInclinationThresh' => $second_inclination_thresh,
            'settingsThirdInclinationThresh' => $third_inclination_thresh,
            'settingsShockThresh' => $shock_thresh,
            'settingsAlertEmailActivated' => $isAlertEmailActivated,
        ]);
    }

    public function updateAction()
    {
        $user = Auth::getUser();


        $firstInclinationThresh = $_POST["firstInclinationThresh"];
        $secondInclinationThresh = $_POST["secondInclinationThresh"];
        $thirdInclinationThresh = $_POST["thirdInclinationThresh"];
        $shockThresh = $_POST["shockThresh"];

        if (isset($_POST["alertSwitchNotification"])) {
            $alertNotification = 1;
        } else {
            $alertNotification = 0;
        }

        $updateOk = True;
        //Check if settings exist 
        if (SettingGeneralManager::checkIfSettingExistForGroup($user->group_id, "first_inclination_thresh")) {
            SettingGeneralManager::updateSettingValueForGroup($user->group_id, "first_inclination_thresh", $firstInclinationThresh);
        } else {
            SettingGeneralManager::insertSettingValueForGroup($user->group_id, "first_inclination_thresh", $firstInclinationThresh);
        }
        if (SettingGeneralManager::checkIfSettingExistForGroup($user->group_id, "second_inclination_thresh")) {
            SettingGeneralManager::updateSettingValueForGroup($user->group_id, "second_inclination_thresh", $secondInclinationThresh);
        } else {
            SettingGeneralManager::insertSettingValueForGroup($user->group_id, "second_inclination_thresh", $secondInclinationThresh);
        }
        if (SettingGeneralManager::checkIfSettingExistForGroup($user->group_id, "third_inclination_thresh")) {
            SettingGeneralManager::updateSettingValueForGroup($user->group_id, "third_inclination_thresh", $thirdInclinationThresh);
        } else {
            SettingGeneralManager::insertSettingValueForGroup($user->group_id, "third_inclination_thresh", $thirdInclinationThresh);
        }
        if (SettingGeneralManager::checkIfSettingExistForGroup($user->group_id, "shock_thresh")) {
            SettingGeneralManager::updateSettingValueForGroup($user->group_id, "shock_thresh", $shockThresh);
        } else {
            SettingGeneralManager::insertSettingValueForGroup($user->group_id, "shock_thresh", $thirdInclinationThresh);
        }


        Flash::addMessage('Mise à jour réussie des paramètres');

        $this->redirect(Auth::getReturnToPage());
    }

    public function getSettingsForCurrentUser()
    {
        $user = Auth::getUser();
        //var_dump($user);
        $settingsArr = SettingGeneralManager::findByGroupId($user->group_id);
        $isAlertEmailActivated = SettingGeneralManager::checkIfAlertActivated($user->email);
        $tmpArr = array("isAlertEmailActivated" => $isAlertEmailActivated);
        array_push($settingsArr, $tmpArr);

        return $settingsArr;
    }
}
