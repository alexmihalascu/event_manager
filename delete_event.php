<?php
include 'db_config.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Check if the id GET variable is set and if it is a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = $_GET['id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // First delete comments associated with the event
        $sqlComments = "DELETE FROM comments WHERE event_id = ?";
        if ($stmtComments = $conn->prepare($sqlComments)) {
            $stmtComments->bind_param("i", $event_id);
            $stmtComments->execute();
            $stmtComments->close();
        }

        // Then delete the event itself
        $sqlEvent = "DELETE FROM events WHERE id = ?";
        if ($stmtEvent = $conn->prepare($sqlEvent)) {
            $stmtEvent->bind_param("i", $event_id);
            $stmtEvent->execute();
            $stmtEvent->close();
        }

        // Commit the transaction
        $conn->commit();
        header("Location: events_list.php");
        exit();
    } catch (mysqli_sql_exception $exception) {
        // An error occurred, rollback the transaction
        $conn->rollback();
        echo "Oops! Something went wrong. Please try again later.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
