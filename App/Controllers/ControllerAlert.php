<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AlertManager;
use \App\Auth;
use \App\Flash;



/**
 * Alert controller
 * Handle the data displayed on alert page
 * PHP version 7.0
 */

class ControllerAlert extends \Core\Controller
{

    /**
     * Show the index page : when the user go to /alerts 
     *
     * @return void
     */
    public function indexAction()
    {
        $user = Auth::getUser();
        $alertsActiveDataArr = AlertManager::getActiveAlertsInfoTable($user->group_id);
        $alertsProcessedDataArr = AlertManager::getProcessedAlertsInfoTable($user->group_id);

        View::renderTemplate('Alerts/index.html', [
            'alerts_active_info_arr' => $alertsActiveDataArr,
            'alerts_processed_info_arr' => $alertsProcessedDataArr
        ]);
    }


    /**
     * update alert statut when the user delete or update an alert
     *
     * @return void
     */
    public function updateAlertAction()
    {
        $id_alert = $_GET['id_alert'];
        $status_alert = $_GET['status_alert'];

        $isUpdated = AlertManager::updateStatus($id_alert, $status_alert);
        # Get the information from the URL

        if ($isUpdated) {
            $this->redirect('/ControllerAlert/showUpdateSuccessMessage');
        } else {
            $this->redirect('/ControllerAlert/showUpdateErrorMessage');
        }
        View::renderTemplate('Alerts/index.html', []);
    }

    /**
     * delete an alert
     *
     * @return void
     */
    public function deleteAlertAction()
    {
        $id_alert = $_GET['id_alert'];
        $alertManager = new AlertManager();
        $isDeleted = AlertManager::delete($id_alert);

        if ($isDeleted) {
            $this->redirect('/ControllerAlert/showDeleteSuccessMessage');
        } else {
            $this->redirect('/ControllerAlert/showDeleteErrorMessage');
        }
    }

    /**
     * Show delete success message after deleting alert
     * @return void
     */
    public function showDeleteSuccessMessageAction()
    {
        Flash::addMessage('Alerte bien supprimée !');

        $this->redirect('/alerts');
    }

    /**
     * Show success success message after updating alert
     * @return void
     */
    public function showUpdateSuccessMessageAction()
    {
        Flash::addMessage('Le status de l\'alerte a été changé !');

        $this->redirect('/alerts');
    }

    /**
     * Show delete error message after deleting alert
     * @return void
     */
    public function showDeleteErrorMessageAction()
    {
        Flash::addMessage('Error during delete alert', $type = 'error');

        $this->redirect('/alerts');
    }

    /**
     * Show update error message after updating alert
     * @return void
     */
    public function showUpdateErrorMessageAction()
    {
        Flash::addMessage('Error during changing alert status', $type = 'error');

        $this->redirect('/alerts');
    }
}
