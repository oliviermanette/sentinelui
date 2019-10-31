<?php
$host = "92.243.19.37";
$userName = "admin";
$password = "eoL4p0w3r";
$dbName = "sentinel_test";
// Create database connection
/*
$conn = new mysqli($host, $userName, $password, $dbName);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}*/
if( ! $connect = mysqli_connect($host,$userName,$password, $dbName) ) {
  die('Connection failed: ' . mysqli_connect_error());
}
