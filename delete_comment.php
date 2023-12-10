<?php
session_start();
include 'db_config.php';

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("You are not authorized to perform this action.");
}

if (isset($_GET['comment_id'])) {
    $comment_id = $_GET['comment_id'];

    $sql = "DELETE FROM comments WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $comment_id);
        if ($stmt->execute()) {
            echo "Comment deleted successfully.";
            // Redirect to the previous page or a specific page
            header("Location: previous_page.php");
            exit;
        } else {
            echo "Error deleting comment.";
        }
    } else {
        echo "Error preparing SQL statement.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
