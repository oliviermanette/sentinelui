<?php
// Initialize the session
ob_start();
session_start();

if(isset($_SESSION['user_id'])) {
	session_destroy();
  // Unset all of the session variables
  $_SESSION = array();
	header("Location: login.php");
  exit;
} else {
	header("Location: index.php");
}
?>
