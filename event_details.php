<?php
include 'navbar.php';
include 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventId = $conn->real_escape_string($_GET['id']);

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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $comment = $_POST['comment'];
    $userId = $_SESSION['user_id'];

    $insertSql = "INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)";
    if ($insertStmt = $conn->prepare($insertSql)) {
        $insertStmt->bind_param("iis", $eventId, $userId, $comment);
        $insertStmt->execute();
        $insertStmt->close();
    }
}
?>

<div class="event-container">
    <div class="event-card">
        <h1><?php echo htmlspecialchars($event['name']); ?></h1>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category_name']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
        <p><strong>Date:</strong> <?php echo date('d.m.Y', strtotime($event['event_date'])); ?></p>
        <p><strong>Time:</strong> <?php echo date('H:i', strtotime($event['event_time'])); ?></p>
        <p><strong>Price:</strong> <?php echo htmlspecialchars(number_format($event['price'])) . " Lei"; ?></p>
        <?php if ($event['photo']) : ?>
            <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="Event Photo" style="max-width: 400px;">
        <?php endif; ?>
    </div>

    <div class="comments-card">
        <?php if (isset($_SESSION['user_id'])) : ?>
            <form method="post" action="event_details.php?id=<?php echo $eventId; ?>" class="comment-form">
                <textarea style="resize: none;" name="comment" required></textarea>
                <button type="submit">Add Comment</button>
            </form>
        <?php endif; ?>

        <div class="comments-section">
            <h2>Comments</h2>
            <?php
            $commentsSql = "SELECT comments.*, users.username, profiles.avatar
                    FROM comments
                    JOIN users ON comments.user_id = users.id
                    LEFT JOIN profiles ON users.id = profiles.user_id
                    WHERE event_id = ?
                    ORDER BY comments.comment_date DESC";

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
        </div>



    </div>