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
}
