<?php

namespace App\Controllers;

use \Core\View;

require_once('App/Models/UserManager.php');
require_once('Core/Controller.php');
require_once('Core/View.php');

/**
 * Account controller
 *
 * PHP version 7.0
 */
class ControllerAccount extends \Core\Controller
{

  /**
   * Validate if email is available (AJAX) for a new signup.
   *
   * @return void
   */
  public function validateEmailAction()
  {
    $is_valid = ! User::emailExists($_GET['email']);

    header('Content-Type: application/json');
    echo json_encode($is_valid);
  }
}
