<link rel="stylesheet" href="style.css">

<?php
function displayComments($conn) {
    $sql = "SELECT comments.id, comments.comment, users.username FROM comments JOIN users ON comments.user_id = users.id";
    $result = $conn->query($sql);

    while ($comment = $result->fetch_assoc()) {
        echo "<div class='comment'>";
        echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . "</p>";

        // Show delete button if the user is an admin
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            echo "<a href='delete_comment.php?comment_id=" . $comment['id'] . "' onclick='return confirm(\"Are you sure you want to delete this comment?\");'>Delete</a>";
        }

        echo "</div>";
    }

        $stmt->close();
    } else {
        echo "Error preparing SQL statement.";
    }
?>
