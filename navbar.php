<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$userAvatar = $loggedIn && isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'defaultavatar.png';
?>

<link rel="stylesheet" href="style.css">

<nav>
    <a href="index.php">Events Manager</a>
    <div class="navbar-right">
        <?php if ($loggedIn): ?>
            <div class="navbar-avatar">
                <img src="uploads/avatars/<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar" class="navbar-user-avatar">
            </div>
            <a href="edit_profile.php"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
            <?php if ($isAdmin): ?>
                <a href="add_event.php">Add Event</a>
            <?php endif; ?>
            <a href="events_list.php">View Events</a>
            <a href="logout.php">Log Out</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
