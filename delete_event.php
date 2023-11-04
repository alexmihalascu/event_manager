<?php
include 'db_config.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect to the login page or home page
    header('Location: index.php');
    exit;
}

// Check if the id GET variable is set and if it is a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = $_GET['id'];

    // Prepare a delete statement
    $sql = "DELETE FROM events WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);

        // Set parameters
        $param_id = $event_id;

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Redirect to landing page
            header("Location: events_list.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
} else {
    // If the id is not set or is not valid, do not do anything
    echo "Invalid request.";
}

// Close connection
$conn->close();
?>
