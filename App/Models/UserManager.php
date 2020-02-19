<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;


class UserManager extends \Core\Model
{

  protected $role = '';
  protected $groupe_name = '';
  protected $valid = '';

  public $errors = [];
  public $success = '';

  public function __construct($data = [])
  {
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
    $this->role = "ROLE_ADMIN";
  }

  public function save()
  {

    $this->validate();

    if (empty($this->errors)) {
      $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

      $sql = 'INSERT INTO user (username, first_name, last_name, email, password, roles)
      VALUES (:username, :first_name, :last_name, :email, :password, :roles)';

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
      $stmt->bindValue(':first_name', $this->firstname, PDO::PARAM_STR);
      $stmt->bindValue(':last_name', $this->name, PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
      $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);
      $stmt->bindValue(':roles', $this->role, PDO::PARAM_STR);

      $success = "Vous êtes bien inscrit. Vous pouvez vous connecter";

      return $stmt->execute();
    }

    return false;
  }


  public function validate()
  {
    if (!preg_match("/^[a-zA-Z ]+$/", $this->name)) {
      $this->errors["uname_error"] = "Name must contain only alphabets and space";
    }
    if (!preg_match("/^[a-zA-Z ]+$/", $this->username)) {
      $this->errors["uusername_error"] = "username must contain only alphabets and space";
    }
    if (!preg_match("/^[a-zA-Z ]+$/", $this->firstname)) {
      $this->errors["ufirstname_error"] = "First name must contain only alphabets and space";
    }
    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
      $this->errors["email_error"] = "Please Enter Valid Email";
    }
    if (static::emailExists($this->email, $this->id ?? null)) {
      $this->errors[] = 'email already taken';
    }
    if (strlen($this->password) < 6) {
      $this->errors["password_error"] = "Password must be minimum of 6 characters";
    }
    if ($this->password != $this->cpassword) {
      $this->errors["cpassword_error"] = "Password and Confirm Password doesn't match";
    }
  }

  /**
   * See if a user record already exists with the specified email
   *
   * @param string $email email address to search for
   * @param string $ignore_id Return false anyway if the record found has this ID
   *
   * @return boolean  True if a record already exists with the specified email, false otherwise
   */
  public static function emailExists($email, $ignore_id = null)
  {
    $user = static::findByEmail($email);

    if ($user) {
      if ($user->id != $ignore_id) {
        return true;
      }
    }

    return false;
  }

  /**
   * Find a user model by email address
   *
   * @param string $email email address to search for
   *
   * @return mixed User object if found, false otherwise
   */
  public static function findByEmail($email)
  {

    $sql = 'SELECT * FROM user WHERE email = :email';

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);

    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();

    $res = $stmt->fetch();
    return $res;
  }

  /**
   * Find a user model by ID
   *
   * @param string $id The user ID
   *
   * @return mixed User object if found, false otherwise
   */
  public static function findByID($id)
  {
    $sql = 'SELECT * FROM user WHERE id = :id';

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();

    $user = $stmt->fetch();
    $groupe_name = UserManager::findGroupById($id);
    $user->setGroupName($groupe_name);
    return $user;
  }

  /**
   * Find group that belong to a specific user by ID
   *
   * @param string $id The user ID
   *
   * @return array array which contains all the groups
   */
  public static function findGroupById($user_id)
  {

    $sql = "SELECT gn.name
    FROM user AS u
    LEFT JOIN group_users as gu ON (gu.user_id = u.id)
    LEFT JOIN group_name as gn ON (gn.group_id = gu.group_id)
    WHERE u.id = :id";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $group= $stmt->fetch(PDO::FETCH_COLUMN);

      return $group;
    }
  }

  /**
   * Find the first name of an user using his id
   *
   * @param string $id The user ID
   *
   * @return array 
   */
  public static function findNameUserById($user_id)
  {
    $sql = "SELECT u.first_name
    FROM user AS u
    LEFT JOIN group_users as gu ON (gu.user_id = u.id)
    LEFT JOIN group_name as gn ON (gn.group_id = gu.group_id)
    WHERE u.id = :id";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

    $group_array = array();
    if ($stmt->execute()) {
      $group_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $group_array;
    }
  }




  /**
   * Authenticate a user by email and password.
   *
   * @param string $email email address
   * @param string $password password
   *
   * @return mixed  The user object or false if authentication fails
   */
  public static function authenticate($email, $password)
  {
    $user = static::findByEmail($email);

    if ($user) {
      if (password_verify($password, $user->password)) {
        return $user;
      }
    }

    return false;
  }

  /**
   * Remember the login by inserting a new unique token into the remembered_logins table
   * for this user record
   *
   * @return boolean  True if the login was remembered successfully, false otherwise
   */
  public function rememberLogin()
  {
    $token = new Token();
    $hashed_token = $token->getHash();
    $this->remember_token = $token->getValue();

    $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

    $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
    VALUES (:token_hash, :user_id, :expires_at)';

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
    $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

    return $stmt->execute();
  }


  /**
   * Send password reset instructions to the user specified
   *
   * @param string $email The email address
   *
   * @return void
   */
  public static function sendPasswordReset($email)
  {
    $user = static::findByEmail($email);

    if ($user) {

      if ($user->startPasswordReset()) {
        $user->sendPasswordResetEmail();
      }
    }
  }


  /**
   * Start the password reset process by generating a new token and expiry
   *
   * @return void
   */
  protected function startPasswordReset()
  {
    $token = new Token();
    $hashed_token = $token->getHash();

    $this->remember_token = $token->getValue();

    $expiry_timestamp = time() + 60 * 60 * 2;  // 2 hours from now

    $sql = 'UPDATE user
    SET password_reset_hash = :token_hash,
    password_reset_expires_at = :expires_at
    WHERE id = :id';

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
    $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

    return $stmt->execute();
  }

  /**
   * Send password reset instructions in an email to the user
   *
   * @return void
   */
  protected function sendPasswordResetEmail()
  {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

    $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
    $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

    Mail::send($this->email, 'Password reset', $text, $html);
  }

  public function setGroupName($groupName)
  {
    $this->groupe_name = $groupName;
  }

  public function getGroupName(){
    return $this->groupe_name;
  }

  /**
   * Find a user model by password reset token and expiry
   *
   * @param string $token Password reset token sent to user
   *
   * @return mixed User object if found and the token hasn't expired, null otherwise
   */
  public static function findByPasswordReset($token)
  {
    $token = new Token($token);
    $hashed_token = $token->getHash();

    $sql = 'SELECT * FROM user
    WHERE password_reset_hash = :token_hash';

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();

    $user = $stmt->fetch();

    if ($user) {

      // Check password reset token hasn't expired
      if (strtotime($user->password_reset_expires_at) > time()) {

        return $user;
      }
    }
  }

  /**
   * Reset the password
   *
   * @param string $password The new password
   *
   * @return boolean  True if the password was updated successfully, false otherwise
   */
  public function resetPassword($password)
  {
    $this->password = $password;

    $this->validate();

    if (empty($this->errors)) {

      $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

      $sql = 'UPDATE user
                    SET password = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id';

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
      $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

      return $stmt->execute();
    }

    return false;
  }

  /**
   * Update the profile account in the DB
   * 
   * @param int $user_id The id of the user
   * @param array $data array that contain the profile information for updating
   *
   * @return boolean  True if the profil was updated successfully, false otherwise
   */
  public static function editAccount($user_id, $data)
  {
    $db = static::getDB();

    $company = $data["company"];
    $username = $data["username"];
    $email = $data["email"];
    $first_name = $data["first_name"];
    $last_name = $data["last_name"];
    $adress = $data["adress"];
    $phone_number = $data["phone_number"];
    $city = $data["city"];
    $zip_code = $data["zip_code"];
    $country = $data["country"];
    ;
    $sql = "UPDATE user
        SET user.country = :country, user.zip_code = :zip_code, user.phone_number = :phone_number,
        user.adress = :adress, user.city = :city, user.company = :company, user.username = :username,
        user.email = :email, user.first_name = :first_name, user.last_name = :last_name
        WHERE user.id = :user_id
            ";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':country', $country, PDO::PARAM_STR);
    $stmt->bindValue(':zip_code', $zip_code, PDO::PARAM_STR);
    $stmt->bindValue(':phone_number', $phone_number, PDO::PARAM_STR);
    $stmt->bindValue(':adress', $adress, PDO::PARAM_STR);
    $stmt->bindValue(':city', $city, PDO::PARAM_STR);
    $stmt->bindValue(':company', $company, PDO::PARAM_STR);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
    $stmt->bindValue(':last_name', $last_name, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
}
