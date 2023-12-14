<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$userAvatar = $loggedIn && isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'defaultavatar.png';
?>

    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Events Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav ms-auto">
                    <?php if ($loggedIn) : ?>
                        <a class="nav-link active" aria-current="page" href="edit_profile.php">
                            <img src="uploads/avatars/<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar" class="rounded-circle" style="width: 30px; height: 30px;">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <?php if ($isAdmin) : ?>
                            <a class="nav-link" href="user_management.php">User Management</a>
                            <a class="nav-link" href="add_event.php">Add Event</a>
                        <?php endif; ?>
                        <a class="nav-link" href="events_list.php">View Events</a>
                        <a class="nav-link" href="logout.php">Log Out</a>
                    <?php else : ?>
                        <a class="nav-link" href="login.php">Login</a>
                        <a class="nav-link" href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>