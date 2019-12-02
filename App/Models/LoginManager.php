<?php

namespace App\Models;
use PDO;

class LoginManager extends \Core\Model
{

  public function validateLogin($email, $password){
    $db = static::getDB();

    //$db = $this->dbConnect();
    $query = "SELECT * FROM user WHERE email = :email and password = :password";
    $stmt = $db->prepare($query);

    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);

    if ($stmt->execute()) {
      $info_user = $stmt->fetchAll();
      if ($info_user){
        return true;
      }
      else {
        return false;
      }
    }

  }

  public function checkLoginStatus(){
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
      return true;
    }else{
      return false;
    }
  }

  public function logout(){
    if(isset($_SESSION['user_id'])) {
      session_destroy();
      // Unset all of the session variables
      $_SESSION = array();
      //header("Location: login.php");
      exit;
    } else {
      //header("Location: index.php");
    }
  }

}
