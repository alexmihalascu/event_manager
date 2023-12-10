<?php
session_start();

// Check if the referer URL is set
if (isset($_SERVER['HTTP_REFERER'])) {
    // Redirect to the referer page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    // If referer is not set, redirect to a default page, e.g., home page
    header('Location: index.php');
}
exit;
