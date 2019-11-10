<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap-theme.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<?php
ob_start();
include_once("config.php");
session_start();

$role = "ROLE_ADMIN";
$groupe = "RTE";
$valid = 1;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    	header("Location: index.php");
}

$error = false;
if (isset($_POST['signup'])) {
	$username = mysqli_real_escape_string($connect, $_POST['username']);
	$firstname = mysqli_real_escape_string($connect, $_POST['firstname']);
	$name = mysqli_real_escape_string($connect, $_POST['name']);
	$email = mysqli_real_escape_string($connect, $_POST['email']);
	$password = mysqli_real_escape_string($connect, $_POST['password']);
	$cpassword = mysqli_real_escape_string($connect, $_POST['cpassword']);
	if (!preg_match("/^[a-zA-Z ]+$/",$name)) {
		$error = true;
		$uname_error = "Name must contain only alphabets and space";
	}
	if (!preg_match("/^[a-zA-Z ]+$/",$username)) {
		$error = true;
		$uname_error = "username must contain only alphabets and space";
	}
	if (!preg_match("/^[a-zA-Z ]+$/",$firstname)) {
		$error = true;
		$ufirstname_error = "First name must contain only alphabets and space";
	}
	if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
		$error = true;
		$email_error = "Please Enter Valid Email ID";
	}
	if(strlen($password) < 6) {
		$error = true;
		$password_error = "Password must be minimum of 6 characters";
	}
	if($password != $cpassword) {
		$error = true;
		$cpassword_error = "Password and Confirm Password doesn't match";
	}
	if (!$error) {
		$query = "INSERT INTO user(username, first_name, last_name, email, password, roles, group_name, valid) VALUES('" . $username . "','" . $firstname . "','" . $name . "', '" . $email . "',
			 '" . md5($password) . "', '" . $role . "', '" . $groupe . "', '" . $valid . "')";
		if(mysqli_query($connect, $query)) {
      	header("Location: login.php");
			$success_message = "Successfully Registered! <a href='login.php'>Click here to Login</a>";
		} else {
			$error_message = "Error in registering...Please try again later!";
		}
	}
}
?>
<title>Registration form for data sensors</title>
<script type="text/javascript" src="script/ajax.js"></script>


<div class="container">
<h2>Registration form</h2>
	<div class="row">
		<div class="col-md-4 col-md-offset-4 well">
			<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="signupform">
				<fieldset>
					<legend>Sign Up</legend>

					<div class="form-group">
						<label for="name">First Name</label>
						<input type="text" name="firstname" placeholder="Enter first name" required value="<?php if($error) echo $firstname; ?>" class="form-control" />
						<span class="text-danger"><?php if (isset($ufirstname_error)) echo $ufirstname_error; ?></span>
					</div>
					<div class="form-group">
						<label for="name">Name</label>
						<input type="text" name="name" placeholder="Enter last Name" required value="<?php if($error) echo $name; ?>" class="form-control" />
						<span class="text-danger"><?php if (isset($uname_error)) echo $uname_error; ?></span>
					</div>
					<div class="form-group">
						<label for="name">Username</label>
						<input type="text" name="username" placeholder="Enter username" required value="<?php if($error) echo $username; ?>" class="form-control" />
						<span class="text-danger"><?php if (isset($uusername_error)) echo $uusername_error; ?></span>
					</div>
					<div class="form-group">
						<label for="name">Email</label>
						<input type="text" name="email" placeholder="Email" required value="<?php if($error) echo $email; ?>" class="form-control" />
						<span class="text-danger"><?php if (isset($email_error)) echo $email_error; ?></span>
					</div>

					<div class="form-group">
						<label for="name">Password</label>
						<input type="password" name="password" placeholder="Password" required class="form-control" />
						<span class="text-danger"><?php if (isset($password_error)) echo $password_error; ?></span>
					</div>

					<div class="form-group">
						<label for="name">Confirm Password</label>
						<input type="password" name="cpassword" placeholder="Confirm Password" required class="form-control" />
						<span class="text-danger"><?php if (isset($cpassword_error)) echo $cpassword_error; ?></span>
					</div>

					<div class="form-group">
						<input type="submit" name="signup" value="Sign Up" class="btn btn-primary" />
					</div>
				</fieldset>
			</form>
			<span class="text-success"><?php if (isset($success_message)) { echo $success_message; } ?></span>
			<span class="text-danger"><?php if (isset($error_message)) { echo $error_message; } ?></span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-md-offset-4 text-center">
		Already Registered? <a href="login.php">Login Here</a>
		</div>
	</div>
</div>
</div>
</body></html>
