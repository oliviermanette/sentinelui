<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>


  <?php
  ob_start();
  include_once("config.php");
  session_start();
  if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {

  }

  if (isset($_POST['changepwd'])) {
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $currentpassword = mysqli_real_escape_string($connect, $_POST['currentpassword']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);
    $cpassword = mysqli_real_escape_string($connect, $_POST['cpassword']);
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
      $error = true;
      $email_error = "Please Enter Valid Email ID";
    }
    if(strlen($password) < 6) {
      $error = true;
      $password_error = "New password must be minimum of 6 characters";
    }
    if($password != $cpassword) {
      $error = true;
      $cpassword_error = "Password and Confirm Password doesn't match";
    }
    if (!$error) {
      $query = "SELECT * FROM user WHERE email = '" . $email. "' and password = '" . md5($currentpassword) . "'";
      $result = mysqli_query($connect, $query);
      if ($row = mysqli_fetch_array($result)) {
        $query_changepwd = "UPDATE user SET password='" . md5($password) . "' where email='$email'";
        if(mysqli_query($connect, $query_changepwd)) {
          //header("Location: login.php");
          $success_message = "Password Successfully changed! <a href='login.php'>Click here to Login</a>";
        } else {
          $error_message = "Error in changing password...Please try again later!";
        }
      }else {
        $currentpassword_error = "Password or current password incorrect. Please try again."
      }
    }
}
?>
<title>Login Form</title>

<div class="container">
  <div class="row">
    <div class="col-md-4 col-md-offset-4 well">
      <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="changepwdform">
        <fieldset>
          <legend>Change password</legend>
          <div class="form-group">
            <label for="name">Email</label>
            <input type="text" name="email" placeholder="Your Email" required class="form-control" />
            <span class="text-danger"><?php if (isset($email_error)) echo $email_error; ?></span>
          </div>
          <div class="form-group">
            <label for="name">Current Password</label>
            <input type="password" name="currentpassword" placeholder="Your current Password" required class="form-control" />
            <span class="text-danger"><?php if (isset($currentpassword_error)) echo $currentpassword_error; ?></span>
          </div>
          <div class="form-group">
            <label for="name">new Password</label>
            <input type="password" name="password" placeholder="Your new Password" required class="form-control" />
            <span class="text-danger"><?php if (isset($password_error)) echo $password_error; ?></span>
          </div>

          <div class="form-group">
            <label for="name">Confirm new Password</label>
            <input type="password" name="cpassword" placeholder="Confirm new Password" required class="form-control" />
            <span class="text-danger"><?php if (isset($cpassword_error)) echo $cpassword_error; ?></span>
          </div>
          <div class="form-group">
            <input type="submit" name="changepwd" value="Change Password" class="btn btn-primary" />
          </div>
        </fieldset>
      </form>
      <span class="text-success"><?php if (isset($success_message)) { echo $success_message; } ?></span>
      <span class="text-danger"><?php if (isset($error_message)) { echo $error_message; } ?></span>
    </div>
  </div>


</div>
</div>
</body></html>
