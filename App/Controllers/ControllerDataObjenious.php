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

  
}
