<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verifică dacă utilizatorul este admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Access denied");
}

// Verifică dacă ID-ul evenimentului este setat
if (isset($_GET['event_id'])) {
    $eventId = intval($_GET['event_id']);
    $newTopEventStatus = isset($_GET['top_event_status']) ? intval($_GET['top_event_status']) : 0;

    // Actualizează statutul de Top Event
    $updateSql = "UPDATE events SET top_event = ? WHERE id = ?";
    if ($updateStmt = $conn->prepare($updateSql)) {
        $updateStmt->bind_param("ii", $newTopEventStatus, $eventId);
        $updateStmt->execute();
        $updateStmt->close();
    }
}

// Redirecționează înapoi la pagina de detalii a evenimentului
header("Location: event_details.php?id=" . $eventId);
exit;
?>
