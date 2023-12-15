<?php
session_start();
include 'navbar.php';
include 'db_config.php';

$error = ''; // Initialize an error message variable

// Ensure the user is logged in before proceeding
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if an event ID was provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventId = $conn->real_escape_string($_GET['id']);
    // Fetch event details
    $sql = "SELECT events.*, categories.name AS category_name FROM events JOIN categories ON events.category_id = categories.id WHERE events.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
    } else {
        echo "Event not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "No event ID provided.";
    exit;
}

// Handle POST request for attending/unattending and adding a comment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];

    // Handle attendance/unattendance
    if (isset($_POST['attend']) || isset($_POST['unattend'])) {
        $attendanceSql = isset($_POST['attend']) ?
            "INSERT INTO event_attendance (event_id, user_id) VALUES (?, ?)" :
            "DELETE FROM event_attendance WHERE event_id = ? AND user_id = ?";
        $attendanceStmt = $conn->prepare($attendanceSql);
        $attendanceStmt->bind_param("ii", $eventId, $userId);
        $attendanceStmt->execute();
        $attendanceStmt->close();
    }

    // Handle comment submission
    if (!empty($_POST['comment'])) {
        $comment = $_POST['comment'];
        $insertSql = "INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iis", $eventId, $userId, $comment);
        $insertStmt->execute();
        $insertStmt->close();
    }

    // Redirect to avoid form resubmission
    header("Location: event_details.php?id=$eventId");
    exit;
}

// Fetch attendance count for admin users
$attendeeCount = 0;
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    $attendeesSql = "SELECT COUNT(*) as attendee_count FROM event_attendance WHERE event_id = ?";
    $attendeesStmt = $conn->prepare($attendeesSql);
    $attendeesStmt->bind_param("i", $eventId);
    $attendeesStmt->execute();
    $attendeesResult = $attendeesStmt->get_result();
    $attendeeRow = $attendeesResult->fetch_assoc();
    $attendeeCount = $attendeeRow['attendee_count'];
    $attendeesStmt->close();
}

// Check if the current user is attending the event
$userHasAttended = false;
$checkUserAttendance = $conn->prepare("SELECT * FROM event_attendance WHERE event_id = ? AND user_id = ?");
$checkUserAttendance->bind_param("ii", $eventId, $_SESSION['user_id']);
$checkUserAttendance->execute();
$userHasAttended = ($checkUserAttendance->get_result()->num_rows > 0);
$checkUserAttendance->close();

// Check if an admin is updating the top event status
if (isset($_POST['toggle_top_event']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    $newTopEventStatus = $event['top_event'] ? 0 : 1;
    $updateSql = "UPDATE events SET top_event = ? WHERE id = ?";
    if ($updateStmt = $conn->prepare($updateSql)) {
        $updateStmt->bind_param("ii", $newTopEventStatus, $eventId);
        $updateStmt->execute();
        $updateStmt->close();

        // Re-fetch the event details after update
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <!-- Include Bootstrap CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container py-5 mt-5">
        <div class="row">
            <!-- Event Card with Details and Comments -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="row g-0">
                        <!-- Event Details Section -->
                        <div class="col-md-8">
                            <div class="card-body text-center">
                                <h1 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h1>
                                <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="Event Photo" class="card-img-top">
                                <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($event['category_name']); ?></p>
                                <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                <p class="card-text"><strong>Date:</strong> <?php echo date('d.m.Y', strtotime($event['event_date'])); ?></p>
                                <p class="card-text"><strong>Time:</strong> <?php echo date('H:i', strtotime($event['event_time'])); ?></p>
                                <p class="card-text"><strong>Price:</strong> <?php echo htmlspecialchars(number_format($event['price'])) . " Lei"; ?></p>
                                <!-- Attendance Button -->
                                <?php if (isset($_SESSION['user_id'])) : ?>
                                    <?php if (!$userHasAttended) : ?>
                                        <form method="post" action="event_details.php?id=<?php echo $eventId; ?>" class="mb-2">
                                            <button type="submit" name="attend" class="btn btn-primary">Attend Event</button>
                                        </form>
                                    <?php else : ?>
                                        <form method="post" action="event_details.php?id=<?php echo $eventId; ?>" class="mb-2">
                                            <button type="submit" name="unattend" class="btn btn-warning">Unattend Event</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <!-- Admin Actions -->
                                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) : ?>
                                    <div class="admin-actions mb-2">
                                        <p class="card-text text-center"><strong>Admin:</strong></p>
                                        <p class="card-text"></p><strong>Number of Attendees:</strong> <?php echo $attendeeCount; ?></p>
                                        <a href='toggle_top_event.php?event_id=<?php echo $eventId; ?>&top_event_status=<?php echo $event['top_event'] ? 0 : 1; ?>' class='btn btn-<?php echo $event['top_event'] ? 'warning' : 'success'; ?>'>
                                            <?php echo $event['top_event'] ? 'Unmark as Top Event' : 'Mark as Top Event'; ?>
                                        </a>
                                        <a href='edit_event.php?id=<?php echo $eventId; ?>' class='btn btn-secondary'>Edit Event</a>
                                        <a href='delete_event.php?id=<?php echo $eventId; ?>' class='btn btn-danger' onclick='return confirm("Are you sure you want to delete this event?");'>Delete Event</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Comments section -->
                        <div class="col-md-4 border-start">
                            <!-- Comments List -->
                            <div class="card-body">
                                <h2 class="card-title">Comments</h2>
                                <?php
                                $commentsSql = "SELECT comments.*, users.username, profiles.avatar
                        FROM comments
                        JOIN users ON comments.user_id = users.id
                        LEFT JOIN profiles ON users.id = profiles.user_id
                        WHERE event_id = ?
                        ORDER BY comments.comment_date ASC";

                                if ($commentsStmt = $conn->prepare($commentsSql)) {
                                    $commentsStmt->bind_param("i", $eventId);
                                    $commentsStmt->execute();
                                    $commentsResult = $commentsStmt->get_result();

                                    while ($comment = $commentsResult->fetch_assoc()) {
                                        $avatarPath = !empty($comment['avatar']) ? "uploads/avatars/" . $comment['avatar'] : "uploads/avatars/default_avatar.png";
                                        echo "<div class='comment'>";
                                        echo "<div class='comment-avatar'><img src='" . htmlspecialchars($avatarPath) . "' alt='User Avatar'></div>";
                                        echo "<div class='comment-content'>";
                                        echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . "</p>";
                                        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                                            echo "<a href='delete_comment.php?comment_id=" . $comment['id'] . "' class='delete-comment' onclick='return confirm(\"Are you sure you want to delete this comment?\");'>Delete</a>";
                                        }
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    $commentsStmt->close();
                                }
                                ?>
                                <div class="card-body">
                                    <h2 class="card-title">Add comment</h2>
                                    <!-- Comment Form -->
                                    <?php if (isset($_SESSION['user_id'])) : ?>
                                        <form method="post" action="event_details.php?id=<?php echo $eventId; ?>" class="mb-3">
                                            <textarea style="resize: none;" name="comment" required class="form-control mb-2"></textarea>
                                            <button type="submit" class="btn btn-primary">Add Comment</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Include Bootstrap JS -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>