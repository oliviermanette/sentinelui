<?php

namespace App\Models;

use \App\Models\API\SentiveAPI;
use Spatie\Async\Pool;
use PDO;

ini_set('max_execution_time', 0);
/*

author : Lirone Samoun

*/

class SentiveAIManager extends \Core\Model
{

    public static function getVersionSentive()
    {
        $version = SentiveAPI::getVersion();
        return $version;
    }

    public static function addDataToNetwork($networkId, $timeserieDataJson, $name = "DbTimeSeries")
    {
        $data = SentiveAPI::addTimeSeries($networkId, $timeserieDataJson, $name);
        return $data;
    }


    public static function resetNetwork($deveui)
    {
        $deviceNumber = SensorManager::getDeviceNumberFromDeveui($deveui);
        $networkId = $deviceNumber;

        SentiveAPI::reset($networkId);
    }
    public static function initNetworkFromSensor($deveui)
    {
        $deviceNumber = SensorManager::getDeviceNumberFromDeveui($deveui);
        $networkId = $deviceNumber;

        SentiveAPI::reset($networkId);

        if (SensorManager::checkProfileGenerationSensor($deveui) == 2) {
            $dataArr = SpectreManager::reconstituteAllSpectreForSensorSecondGeneration($deveui);
        } else {
            $dataArr = SpectreManager::reconstituteAllSpectreForSensorFirstGeneration($deveui);
        }
        //$test_count = 0;
        foreach ($dataArr as $spectreArr) {
            $timeSerie = new TimeSeries();
            $timeSerie->createFromSpectreArr($spectreArr);
            $dataArr = $timeSerie->getTimeSerieData();

            $dateTime = $spectreArr['date_time'];
            $timestamp = strtotime($dateTime);
            $timeSerie->setNetworkId($networkId);
            $timeSerie->setTimestamp($timestamp);
            $dataPayloadJson = $timeSerie->parseForSentiveAi();
            SentiveAPI::addTimeSeries($networkId, $dataPayloadJson, "DbTimeSeries");
        }
        return true;
        //SetImageBuffer : premier paramètre : fonction, fonction qui produisent un graphique puis après chaque slah, paramètre pour cette function
        //Set : 
        //Get recupere l'image static
    }

    public static function initAllNetworks()
    {
        //Get all the devices
        $all_sensors = SensorManager::getAllDevices();

        $pool = Pool::create();
        foreach ($all_sensors as $sensor) {
            $deveui = $sensor["deveui"];
            $pool->add(function () use ($deveui) {
                //Init network
                SentiveAIManager::initNetworkFromSensor($deveui);
            })->then(function ($output) use ($deveui) {
                echo "\n Network has been init for " . $deveui . "\n";
            })->catch(function (Throwable $exception) {
                echo "\n ERROR \n";
                print_r($exception);
                return false;
            });
        }
        $pool->wait();
        return true;
    }

    public static function runUnsupervisedOnAllNetworks()
    {
        $all_sensors = SensorManager::getAllDevices();

        $pool = Pool::create();
        foreach ($all_sensors as $sensor) {
            $networkId = $sensor["device_number"];
            $pool->add(function () use ($networkId) {
                //Init network
                var_dump($run = SentiveAPI::runUnsupervised($networkId));
            })->then(function ($output) use ($networkId) {
                echo "\n Network has been init for " . $networkId . "\n";
            })->catch(function (Throwable $exception) {
                echo "\n ERROR \n";
                print_r($exception);
                return false;
            });
        }
        $pool->wait();
        return true;
    }
    public static function runUnsupervisedForSensor($deveui)
    {
        $deviceNumber = SensorManager::getDeviceNumberFromDeveui($deveui);
        $networkId = $deviceNumber;
        $run = SentiveAPI::runUnsupervised($networkId);
    }

    public static function runUnsupervisedOnNetwork($networkId)
    {
        $run = SentiveAPI::runUnsupervised($networkId);
    }

    public static function computeImagesOnNetwork($networkId)
    {
        SentiveAIManager::setChartNetworkGraph($networkId);
        SentiveAIManager::setInputNetworkGraph($networkId);
        SentiveAIManager::setAtivityNeuronGraph($networkId);
        SentiveAIManager::setChartDetectedCategory($networkId);
    }

    public static function computeImagesOnAllNetworks()
    {
        $all_sensors = SensorManager::getAllDevices();

        $pool = Pool::create();
        foreach ($all_sensors as $sensor) {
            $networkId = $sensor["device_number"];
            $pool->add(function () use ($networkId) {
                //Init images
                SentiveAIManager::setChartNetworkGraph($networkId);
                SentiveAIManager::setInputNetworkGraph($networkId);
                SentiveAIManager::setAtivityNeuronGraph($networkId);
                SentiveAIManager::setChartDetectedCategory($networkId);
            })->then(function ($output) use ($networkId) {
                echo "\n Images have been computed for " . $networkId . "\n";
            })->catch(function (Throwable $exception) {
                echo "\n ERROR \n";
                print_r($exception);
                return false;
            });
        }
        $pool->wait();
        return true;
    }

    /**
     * Chart 1 : Network graph
     */
    public static function getChartNetworkGraph($networkId)
    {
        $run = SentiveAPI::getChartNetworkGraph($networkId);

        return $run;
    }
    private static function setChartNetworkGraph($networkId)
    {
        $run = SentiveAPI::setChartNetworkGraph($networkId);
    }

    /**
     * Chart 2: input graph
     */
    public static function getInputNetworkGraph($networkId)
    {
        $run = SentiveAPI::getChartInputGraph($networkId);

        return $run;
    }
    private static function setInputNetworkGraph($networkId)
    {
        $run = SentiveAPI::setChartInputGraph($networkId);
    }

    /**
     * Chart 3: activity neurons categories
     */
    public static function getAtivityNeuronGraph($networkId, $neuronType = "CATEGORY")
    {
        $run = SentiveAPI::getChartActivityNeuron($networkId, $neuronType);

        return $run;
    }
    private static function setAtivityNeuronGraph($networkId, $neuronType = "CATEGORY")
    {
        $run = SentiveAPI::setChartActivityNeuron($networkId, $neuronType);
    }

    /**
     * Chart 4: activity neurons categories
     */
    public static function getChartDetectedCategory($networkId, $neuronType = "CATEGORY")
    {
        $run = SentiveAPI::getChartDetectedCategory($networkId, $neuronType);

        return $run;
    }
    private static function setChartDetectedCategory($networkId, $neuronType = "CATEGORY")
    {
        $run = SentiveAPI::setChartDetectedCategory($networkId, $neuronType);
    }
}
