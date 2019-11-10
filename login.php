<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" href="css/login.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>


  <?php
  ob_start();
  include_once("config.php");
  session_start();
  if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    echo "Welcome to the member's area, " . $_SESSION['user_name'] . "!";
    header("Location: index.php");
  }
  if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);
    $query = "SELECT * FROM user WHERE email = '" . $email. "' and password = '" . md5($password). "'";
    $result = mysqli_query($connect, $query);
    if ($row = mysqli_fetch_array($result)) {
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['user_name'] = $row['username'];
      $_SESSION['loggedin'] = true;
      header("Location: index.php");
    } else {
      $error_message = "Incorrect Email or Password!!!";
    }
  }
  ?>
  <title>Login Form</title>

  <div class="sidenav">
    <div class="login-main-text">
      <h2>Flod.ai<br> Login Page</h2>
      <p>Login or register from here to access.</p>
    </div>
  </div>
  <div class="main">
    <div class="col-md-6 col-sm-12">
      <div class="login-form">
        <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginform">
          <fieldset>
            <div class="form-group">
              <label for="name">Email</label>
              <input type="text" name="email" placeholder="Your Email" required class="form-control" />
            </div>
            <div class="form-group">
              <label for="name">Password</label>
              <input type="password" name="password" placeholder="Your Password" required class="form-control" />
            </div>
            <button type="submit" name="login" value="Login" class="btn btn-black">Login</button>
            <button onclick="location.href='register.php'"  name="register" value="register" class="btn btn-secondary">Register</button>
          </fieldset>
        </form>
        <span class="text-danger"><?php if (isset($error_message)) { echo $error_message; } ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-md-offset-4 text-center"><a href="changepwd.php">Change your password</a>
  </div>

</body></html>
