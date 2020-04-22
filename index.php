<?php

/**
 * Composer
 */
require dirname(__FILE__) . '/vendor/autoload.php';

/*
 * Error and Exception handling
*/
error_reporting(E_ALL);
ini_set('display_errors', 'On');
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');




/**
 * Session
 */

session_start();

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
/**
 * PAGES
 */

/**
 * ACTION PAGES

 */
//Homepage
$router->add('', ['controller' => 'ControllerAccueil', 'action' => 'index']);
//New login
$router->add('login', ['controller' => 'ControllerLogin', 'action' => 'new']);
//New registration
$router->add('register', ['controller' => 'ControllerRegistration', 'action' => 'new']);
//Logout
$router->add('logout', ['controller' => 'ControllerLogin', 'action' => 'destroy']);
//Reset password
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
//Parse data from Objenious server
$router->add('data', ['controller' => 'ControllerDataObjenious', 'action' => 'receiveRawDataFromObjenious']);
//Setting page
$router->add('settings', ['controller' => 'ControllerSetting', 'action' => 'index']);
//Profile page
$router->add('profile', ['controller' => 'ControllerProfile', 'action' => 'index']);
//Support page
$router->add('support', ['controller' => 'ControllerProfile', 'action' => 'support']);
//Search data form
$router->add('search-spectre', ['controller' => 'ControllerData', 'action' => 'searchSpectre']);
//Choc data display
$router->add('search-choc', ['controller' => 'ControllerData', 'action' => 'searchChoc']);
//alerts data visualization
$router->add('alerts', ['controller' => 'ControllerAlert', 'action' => 'index']);
//sensors visualization
$router->add('sensors', ['controller' => 'ControllerSensors', 'action' => 'index']);
//device info display
$router->add('device/{deviceid:[\da-f]+}/info', ['controller' => 'ControllerSensors', 'action' => 'info']);
//device settings display
$router->add('device/{deviceid:[\da-f]+}/settings', ['controller' => 'ControllerSensors', 'action' => 'settingsView']);



//Match route controller/action
$router->add('{controller}/{action}');
//In case there is something between controller and action
$router->add('{controller}/{id:\d+}/{action}');

//TESTING
$router->add('data-test', ['controller' => 'ControllerDataObjenious', 'action' => 'receiveRawDataFromObjenious']);
$router->add('alerts-test', ['controller' => 'ControllerAlert', 'action' => 'getAlertsFromAPI']);
$router->add('test-sql', ['controller' => 'ControllerDataObjenious', 'action' => 'testSQL']);
$router->add('go', ['controller' => 'ControllerInit', 'action' => 'goTimeSeries']);
$router->add('go2', ['controller' => 'ControllerInit', 'action' => 'goTestTimeSeries']);
$router->add('api-test', ['controller' => 'ControllerInit', 'action' => 'testApi']);
$router->add('fill-weather', ['controller' => 'ControllerInit', 'action' => 'fillTemperatureDataForSite']);


$router->dispatch($_SERVER['QUERY_STRING']);
