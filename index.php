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
 * ACTION PAGES
 */
// ### VIEWS ###

//Homepage
$router->add('', ['controller' => 'ControllerAccueil', 'action' => 'indexView']);
//New login
$router->add('login', ['controller' => 'ControllerLogin', 'action' => 'loginView']);
//New registration
$router->add('register', ['controller' => 'ControllerRegistration', 'action' => 'registerView']);
//Logout
$router->add('logout', ['controller' => 'ControllerLogin', 'action' => 'destroy']);
//Setting page
$router->add('settings', ['controller' => 'ControllerSetting', 'action' => 'settingsView']);
//Profile page
$router->add('profile', ['controller' => 'ControllerProfile', 'action' => 'profileView']);
//Support page
$router->add('support', ['controller' => 'ControllerProfile', 'action' => 'supportView']);
//Search data form
$router->add('search-spectre', ['controller' => 'ControllerSpectreData', 'action' => 'searchSpectreView']);
//$router->add('search-spectre?page={page:[\da-f]+}', ['controller' => 'ControllerSpectreData', 'action' => 'searchSpectreView']);
//Choc data display
$router->add('search-choc', ['controller' => 'ControllerChocData', 'action' => 'searchChocView']);
//alerts data visualization
$router->add('alerts', ['controller' => 'ControllerAlert', 'action' => 'indexView']);
//sensors visualization
$router->add('sensors', ['controller' => 'ControllerSensors', 'action' => 'indexView']);
//device info display
$router->add('device/{deviceid:[\da-f]+}/info', ['controller' => 'ControllerSensors', 'action' => 'infoView']);
//device settings display
$router->add('device/{deviceid:[\da-f]+}/settings', ['controller' => 'ControllerSensors', 'action' => 'settingsView']);
//sentive AI
$router->add('sentive', ['controller' => 'ControllerSentiveAI', 'action' => 'indexView']);
// ### Actions ###
//Reset password
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
//Parse data from Objenious server
$router->add('data', ['controller' => 'ControllerDataObjenious', 'action' => 'receiveRawDataFromObjenious']);




//Match route controller/action
$router->add('{controller}/{action}');
//In case there is something between controller and action
$router->add('{controller}/{id:\d+}/{action}');

//TESTING
$router->add('data-test', ['controller' => 'ControllerDataObjenious', 'action' => 'receiveRawDataFromObjenious']);
$router->add('alerts-test', ['controller' => 'ControllerAlert', 'action' => 'getAlertsFromAPI']);
$router->add('test-sql', ['controller' => 'ControllerTest', 'action' => 'testSQL']);
$router->add('go', ['controller' => 'ControllerTest', 'action' => 'goTimeSeries']);
$router->add('go2', ['controller' => 'ControllerTest', 'action' => 'goTestTimeSeries']);
$router->add('api-test', ['controller' => 'ControllerTest', 'action' => 'testApi']);
$router->add('fill-weather', ['controller' => 'ControllerTest', 'action' => 'fillTemperatureDataForSite']);
$router->add('debug', ['controller' => 'ControllerTest', 'action' => 'debug']);
$router->add('sentiveai', ['controller' => 'ControllerTest', 'action' => 'testSentiveAI']);


$router->dispatch($_SERVER['QUERY_STRING']);
