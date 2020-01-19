<?php

/*
ControllerDataObjenious.php
author : Lirone Samoun

Briefly : get uplink data (sensors) from Objenious platform. In Objenious, a HTTP request is sent.
This class handle this HTTP request
/data

*/


namespace App\Controllers;

use \Core\View;
use \App\Models\RecordManager;

ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
/**
* Data Objenious Controller
*
* PHP version 7.0
*/
class ControllerDataObjenious extends \Core\Controller{

  public function __construct() {

  }

  public function receiveRawDataFromObjeniousAction(){
    //Get the JSON content from the HTTP request
    $data = json_decode(file_get_contents('php://input'), true);
    //Parse the JSON content to insert into the DB
    $recordManager = new RecordManager();
    $recordManager->parseJsonDataAndInsert($data);

  }

  //Testing 
  public function testChocAction()
  {
    //Get the JSON content from the HTTP request
    $data = json_decode(file_get_contents('php://input'), true);
    //Parse the JSON content to insert into the DB
    $recordManager = new RecordManager();
    $recordManager->parseJsonDataAndInsert($data);
  }

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
