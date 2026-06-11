<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$eventId = isset($_GET['event_id']) && is_numeric($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$status  = isset($_GET['top_event_status']) ? (int)(bool)$_GET['top_event_status'] : 0;

if ($eventId > 0) {
    $s = $conn->prepare("UPDATE events SET top_event = ? WHERE id = ?");
    $s->bind_param('ii', $status, $eventId);
    $s->execute();
    $s->close();
}

header("Location: /event_manager/events/?id=$eventId");
exit;
