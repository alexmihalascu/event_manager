<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $eventId = $_POST['event_id'];
    $comment = $_POST['comment'];
    $userId = $_SESSION['user_id'];

    $sql = "INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iis", $eventId, $userId, $comment);
        $stmt->execute();
        $stmt->close();
    }

    // Redirecționează înapoi la pagina evenimentului (modificați după nevoie)
    header("Location: event_details.php?id=" . $eventId);
    exit();
}
?>
