<?php

/**
 * Composer
 */
require dirname(__FILE__) . '/vendor/autoload.php';

/*
 * Error and Exception handling
*/
error_reporting(E_ALL);
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
//Search data form
$router->add('search-data', ['controller' => 'ControllerData', 'action' => 'index']);
//Choc data display
$router->add('search-choc', ['controller' => 'ControllerData', 'action' => 'searchChoc']);
//alerts data visualization
$router->add('alerts', ['controller' => 'ControllerAlert', 'action' => 'index']);



//Match route controller/action
$router->add('{controller}/{action}');
//In case there is something between controller and action
$router->add('{controller}/{id:\d+}/{action}');

//TESTING
$router->add('data-test', ['controller' => 'ControllerDataObjenious', 'action' => 'testChoc']);
$router->add('alerts-test', ['controller' => 'ControllerAlert', 'action' => 'getAlertsFromAPI']);
$router->add('go', ['controller' => 'ControllerTimeSeries', 'action' => 'goTimeSeries']);


$router->dispatch($_SERVER['QUERY_STRING']);
