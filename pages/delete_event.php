<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /event_manager/events');
    exit;
}

$eventId = (int)$_GET['id'];

$conn->begin_transaction();
try {
    $s = $conn->prepare("DELETE FROM event_attendance WHERE event_id = ?");
    $s->bind_param('i', $eventId); $s->execute(); $s->close();

    $s = $conn->prepare("DELETE FROM comments WHERE event_id = ?");
    $s->bind_param('i', $eventId); $s->execute(); $s->close();

    $s = $conn->prepare("DELETE FROM events WHERE id = ?");
    $s->bind_param('i', $eventId); $s->execute(); $s->close();

    $conn->commit();
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
}

header('Location: /event_manager/events');
exit;
