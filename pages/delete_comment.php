<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$commentId = isset($_GET['comment_id']) && is_numeric($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;
$eventId   = isset($_GET['event_id'])   && is_numeric($_GET['event_id'])   ? (int)$_GET['event_id']   : 0;

if ($commentId > 0) {
    $s = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $s->bind_param('i', $commentId);
    $s->execute();
    $s->close();
}

$redirect = $eventId > 0
    ? "/event_manager/events/$eventId"
    : "/event_manager/events";

header("Location: $redirect");
exit;
