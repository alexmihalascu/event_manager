<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "event_manager";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
