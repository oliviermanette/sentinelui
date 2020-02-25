<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\UserManager;

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
    $is_valid = !UserManager::emailExists($_GET['email']);

    header('Content-Type: application/json');
    echo json_encode($is_valid);
  }
}
