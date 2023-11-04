<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Assuming $loggedIn and $isAdmin are determined by checking session variables
$loggedIn = isset($_SESSION['user_id']); // true if user is logged in
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin']; // true if user is an admin
$userAvatar = $loggedIn ? $_SESSION['avatar'] : 'defaultavatar.png'; // Set a default avatar if not logged in or no avatar
?>
<link rel="stylesheet" href="style.css">
<nav>
    <!-- your logo here -->
    <a href="index.php">Events Manager</a>

    <div class="navbar-right">
        <?php if ($loggedIn): ?>
            <!-- User Avatar -->
            <div class="navbar-avatar">
                <img src="uploads/avatars/<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar" class="navbar-user-avatar">
            </div>
            <a href="edit_profile.php"><?php echo $_SESSION['username']; ?></a>
            <?php if ($isAdmin): ?>
                <!-- Only show Add Event if the user is an admin -->
                <a href="add_event.php">Add Event</a>
            <?php endif; ?>
            <a href="events_list.php">View Events</a>
            <!-- Log out link -->
            <a href="logout.php">Log Out</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
