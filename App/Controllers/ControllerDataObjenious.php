<?php


namespace App\Controllers;

use \Core\View;
use \App\Models\RecordManager;

ini_set('error_reporting', E_ALL);
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "./log/error.log");
/*
ControllerDataObjenious.php
author : Lirone Samoun

Briefly : get uplink data (sensors) from Objenious platform. In Objenious, a HTTP request is sent.
This class handle this HTTP request

/data

*/
class ControllerDataObjenious extends \Core\Controller{


  /**
   * When Objenious send an uplink, it goes here
   * @return void
   */
  public function receiveRawDataFromObjeniousAction(){
    //Get the JSON content from the HTTP request
    $data = json_decode(file_get_contents('php://input'), true);
    //Parse the JSON content to insert into the DB
    $recordManager = new RecordManager();
    print_r($data);
    //file_put_contents('php://stdout', print_r($data, TRUE));
    //file_put_contents('./logs/logs.txt', $data);
    error_log("\nData received\n" . json_encode($data));
    
    //$recordManager->parseJsonDataAndInsert($data);

  }


  /**
   * TESTING PURPOSE
   * @return void
   */
  public function testChocAction()
  {
    //Get the JSON content from the HTTP request
    $data = json_decode(file_get_contents('php://input'), true);
    //Parse the JSON content to insert into the DB
    $recordManager = new RecordManager();
    $recordManager->parseJsonDataAndInsert($data);
  }


  /**
   * init a request for calling API
   * @return void
   */
  public static function CallAPI($method, $url, $json_encode = true, $data = false)
  {
    $curl = curl_init();

    switch ($method) {
      case "POST":
        curl_setopt($curl, CURLOPT_POST, 1);

        if ($data)
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
      case "PUT":
        curl_setopt($curl, CURLOPT_PUT, 1);
        break;
      case "GET":
        break;
      default:
        if ($data)
          $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'apikey: ' . \App\Config::OBJENIOUS_API_KEY
    ));
   
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    if ($json_encode){
      $result = json_decode($result, true);

      return $result;
    }

    return $result;
  }


  
}
