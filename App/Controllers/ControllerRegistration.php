<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\UserManager;
use \App\Flash;


/**
 * ControllerRegistration
 * Registration of a new user
 *
 * PHP version 7.0
 */
class ControllerRegistration extends \Core\Controller
{
  /**
   * Show the signup page
   *
   * @return void
   */
  public function registerViewAction()
  {
    View::renderTemplate('Signup/inscription.html');
  }

  public function createAction()
  {
    $user = new UserManager($_POST);

    $view = new View();
    if ($user->save()) {
      Flash::addMessage('Inscription réussie ! Vous pouvez vous connecter !');
      $this->redirect("/ControllerLogin/new");
    } else {
      View::renderTemplate('Signup/inscription.html', [
        'error_message' => $user->errors
      ]);
    }
  }

  /**
   * Show the signup success page
   *
   * @return void
   */
  public function successAction()
  {
  }

  /**
   * See if a user record already exists with the specified email
   *
   * @param string $email email address to search for
   *
   * @return boolean  True if a record already exists with the specified email, false otherwise
   */


  protected function before()
  {
  }

  /**
   * After filter
   *
   * @return void
   */
  protected function after()
  {
  }
}
