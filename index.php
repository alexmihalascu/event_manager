<?php
session_start(); // Start the session

include 'navbar.php'; 

// Check if the user is logged in
if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
    // If logged in, redirect to events_list.php
    header('Location: events_list.php');
    exit;
} else {
    // If not logged in, redirect to login.php
    header('Location: login.php');
    exit;
}
?>
