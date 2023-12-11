<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = $_GET['id'];

    $conn->begin_transaction();

    try {
        $sqlComments = "DELETE FROM comments WHERE event_id = ?";
        if ($stmtComments = $conn->prepare($sqlComments)) {
            $stmtComments->bind_param("i", $event_id);
            $stmtComments->execute();
            $stmtComments->close();
        }

        $sqlEvent = "DELETE FROM events WHERE id = ?";
        if ($stmtEvent = $conn->prepare($sqlEvent)) {
            $stmtEvent->bind_param("i", $event_id);
            $stmtEvent->execute();
            $stmtEvent->close();
        }

        $conn->commit();
        header("Location: events_list.php");
        exit();
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        echo "Oops! Something went wrong. Please try again later.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
