<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\UserManager;
use \App\Models\SentiveAIManager;
use \App\Models\EquipementManager;
use \App\Models\SiteManager;
use \App\Auth;
use \App\Flash;
use App\Models\SensorManager;
use \App\Utilities;
use Spatie\Async\Pool;

ini_set('max_execution_time', 0);

/**
 * Sentive AI controller
 *
 * PHP version 7.0
 */
class ControllerSentiveAI extends Authenticated
{

    public function indexview()
    {
        $user = Auth::getUser();

        if ($user->isSuperAdmin()) {
            $sites = SiteManager::getAllSites();
            $all_equipment = EquipementManager::getAllEquipements();
        } else {
            $sites = SiteManager::getSites($user->group_id);
            $all_equipment = EquipementManager::getEquipements($user->group_id);
        }
        $versionSentive = SentiveAIManager::getVersionSentive();
        //var_dump($versionSentive);
        $context = [
            'version' => $versionSentive,
            'all_site'    => $sites,
            'all_equipment' => $all_equipment,
        ];
        View::renderTemplate('SentiveAI/index.html', $context);
    }

    public function initAllNetworksAction()
    {
        SentiveAIManager::initAllNetworks();
        echo "OK INIT";
    }

    public function computeImagesNetworkAction()
    {

        SentiveAIManager::computeImagesOnNetwork("2001002");
        echo "OK INIT";
    }


    public function runUnsupervisedOnNetworkAction()
    {
        if (isset($_GET['networkId'])) {
            $networkId = $_GET['networkId'];
            SentiveAIManager::runUnsupervisedOnNetwork($networkId);
            SentiveAIManager::computeImagesOnNetwork($networkId);
            echo "OK unsupervised";
        }
    }

    public function runUnsupervisedOnNetworksAction()
    {
        SentiveAIManager::runUnsupervisedOnAllNetworks();
    }

    public function getInputGraphAction()
    {
        if (isset($_GET['networkId'])) {
            $networkId = $_GET['networkId'];
            $url = SentiveAIManager::getInputNetworkGraph($networkId);
            echo $url;
        }
    }

    public function getNetworkGraphAction()
    {
        if (isset($_GET['networkId'])) {
            $networkId = $_GET['networkId'];
            $url = SentiveAIManager::getChartNetworkGraph($networkId);
            echo $url;
        }
    }

    public function getActivitiesNeuronsCategoriesChartAction()
    {
        if (isset($_GET['networkId'])) {
            $networkId = $_GET['networkId'];
            $url = SentiveAIManager::getAtivityNeuronGraph($networkId);
            echo $url;
        }
    }

    public function getDetectedCategoriesChartAction()
    {
        if (isset($_GET['networkId'])) {
            $networkId = $_GET['networkId'];
            $url = SentiveAIManager::getChartDetectedCategory($networkId);
            echo $url;
        }
    }
}
