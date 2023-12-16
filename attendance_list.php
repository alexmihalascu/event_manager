<?php
session_start();
include 'navbar.php'; // Include your navigation bar
include 'db_config.php'; // Include your database configuration file

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo "<p class='alert alert-danger'>Access Denied. You must be an admin to view this page.</p>";
    exit;
}

// Check if an event ID is provided
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo "<p class='alert alert-warning'>No event ID provided.</p>";
    exit;
}

$eventId = $conn->real_escape_string($_GET['event_id']);

// Fetch event name
$eventName = '';
if ($eventStmt = $conn->prepare("SELECT name FROM events WHERE id = ?")) {
    $eventStmt->bind_param("i", $eventId);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    if ($row = $eventResult->fetch_assoc()) {
        $eventName = $row['name'];
    }
    $eventStmt->close();
}

// Fetch attendees for the event
$attendees = [];
$sql = "SELECT users.username FROM event_attendance JOIN users ON event_attendance.user_id = users.id WHERE event_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $attendees[] = $row['username'];
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Attendees at <?php echo htmlspecialchars($eventName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> <!-- Include your custom CSS if needed -->
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title text-center">Event Attendees at <?php echo htmlspecialchars($eventName); ?></h1>
            <?php if (count($attendees) > 0): ?>
                <ul class="list-group">
                    <?php foreach ($attendees as $attendee): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($attendee); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-info">No attendees for this event.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
