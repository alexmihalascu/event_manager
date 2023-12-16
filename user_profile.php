<?php
include 'navbar.php';
include 'db_config.php';

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "<p class='alert alert-warning'>No user ID provided.</p>";
    exit;
}

$userId = $conn->real_escape_string($_GET['user_id']);

// Fetch user profile
$sql = "SELECT users.username, profiles.bio, profiles.avatar FROM users LEFT JOIN profiles ON users.id = profiles.user_id WHERE users.id = ?";
$profile = null;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $profile = $row;
    }
    $stmt->close();
}

if (!$profile) {
    echo "<p class='alert alert-info'>User profile not found.</p>";
    exit;
}

// Fetch avatar
$avatarPath = "uploads/avatars/default_avatar.png";
if (!empty($profile['avatar'])) {
    $avatarPath = "uploads/avatars/" . $profile['avatar'];
}


// Fetch latest comments with event names
$commentsSql = "SELECT comments.comment, comments.comment_date, events.name AS event_name, events.photo 
                FROM comments 
                JOIN events ON comments.event_id = events.id 
                WHERE comments.user_id = ? 
                ORDER BY comments.comment_date DESC 
                LIMIT 5";
$comments = [];
if ($stmt = $conn->prepare($commentsSql)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($profile['username']); ?>'s Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center"><?php echo htmlspecialchars($profile['username']); ?>'s Profile</h1>
                <div class="row">
                    <!-- Left Column for Avatar -->
                    <div class="col-md-4 text-center">
                        <img src="<?php echo $avatarPath; ?>" alt="Avatar" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                    </div>

                    <!-- Right Column for Bio -->
                    <div class="col-md-8">
                        <h3 class="mt-3">Bio</h3>
                        <p class="mt-3"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                    </div>
                </div>

                <!-- Latest comments -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h3 class="text-center mb-4">Latest Comments</h3>
                        <div class="list-group">
                            <?php if (!empty($comments)) : ?>
                                <?php foreach ($comments as $comment) : ?>
                                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">Event: <?php echo htmlspecialchars($comment['event_name']); ?></h5>
                                            <small class="text-muted">Posted on: <?php echo htmlspecialchars($comment['comment_date']); ?></small>
                                        </div>
                                        <p class="mb-1">Comment: <?php echo htmlspecialchars($comment['comment']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p class="text-center">No comments yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>