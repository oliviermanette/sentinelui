<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AlertManager;
use \App\Auth;
use \App\Flash;


/**
 * Alert controller
 *
 * PHP version 7.0
 */
class ControllerAlert extends \Core\Controller
{

    public function __construct()
    {
    }

    public function indexAction()
    {
        $group_name = $_SESSION['group_name'];
        
        $alertManager = new AlertManager();
        $alertsDataArr = $alertManager->getAlertsInfoTable($group_name);

        View::renderTemplate('Alerts/index.html', [
            'alerts_info_arr' => $alertsDataArr
        ]);
    }


    public function updateAlertAction(){
        $id_alert = $_GET['id_alert'];
        $status_alert = $_GET['status_alert'];
        $alertManager = new AlertManager();
        $isUpdated = $alertManager->updateStatus($id_alert, $status_alert);
        # Get the information from the URL

        if ($isUpdated) {
            $this->redirect('/ControllerAlert/showUpdateSuccessMessage');
        } else {
            $this->redirect('/ControllerAlert/showUpdateErrorMessage');
        }
        View::renderTemplate('Alerts/index.html', [
        ]);

    }

    public function deleteAlertAction(){
        $id_alert = $_GET['id_alert'];
        $alertManager = new AlertManager();
        $isDeleted = $alertManager->delete($id_alert);

        if ($isDeleted){
            $this->redirect('/ControllerAlert/showDeleteSuccessMessage');
        }else{
            $this->redirect('/ControllerAlert/showDeleteErrorMessage');
        }

        

    }
    public function createAction()
    {

        $alertManager = new AlertManager();
        //$alertManager->create();
       
    }

    /**
     *
     * @return void
     */
    public function showDeleteSuccessMessageAction()
    {
        Flash::addMessage('Alert has been deleted successfully');

        $this->redirect('/alerts');
    }

    /**
     *
     * @return void
     */
    public function showUpdateSuccessMessageAction()
    {
        Flash::addMessage('Status has been changed');

        $this->redirect('/alerts');
    }

    /**
     *
     * @return void
     */
    public function showDeleteErrorMessageAction()
    {
        Flash::addMessage('Error during delete alert', $type = 'error' );

        $this->redirect('/alerts');
    }

    /**
     *
     * @return void
     */
    public function showUpdateErrorMessageAction()
    {
        Flash::addMessage('Error during changing alert status', $type = 'error');

        $this->redirect('/alerts');
    }

}
