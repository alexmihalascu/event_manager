<?php
session_start();
include 'db_config.php';
include 'navbar.php';
// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


// ...
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <meta rel="icon" href="uploads/icons/icon.ico">
    <style>
        .jumbotron {
            padding: 5rem 2rem;
            margin-top: 20vh; /* Center vertically */
        }
        .footer {
            position: relative;
            bottom: 0;
            width: 100%;
            background-color: #f8f9fa;
        }
        body, html {
            height: 100%;
        }
        .container-fluid {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Hero Section -->
        <div class="jumbotron bg-light text-center">
            <h1 class="display-2">Welcome to Events Manager</h1>
            <p class="lead">Discover, Participate, and Manage Events</p>
            <hr class="my-5">
            <p>Events Manager is a web application that allows users to discover, participate, and manage events.</p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 text-center">
        <small>&copy; <?php echo date("Y"); ?> - Events Manager. All rights reserved.</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o
