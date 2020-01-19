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
        $alertsActiveDataArr = $alertManager->getActiveAlertsInfoTable($group_name);
        $alertsProcessedDataArr = $alertManager->getProcessedAlertsInfoTable($group_name);

        View::renderTemplate('Alerts/index.html', [
            'alerts_active_info_arr' => $alertsActiveDataArr,
            'alerts_processed_info_arr' => $alertsProcessedDataArr
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


    public function getAlertsFromAPIAction(){

        $url = "https://api.objenious.com/v1/devices/lora:0004A30B00E80AC9/state";
        $results_api = ControllerDataObjenious::CallAPI("GET", $url);
        //$state_device = $results_api["states"];
        print_r($results_api);
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