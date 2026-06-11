<?php
/**
 * Shared navbar + document opener.
 * Always included AFTER bootstrap.php (session + auth already loaded).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/includes/auth.php';
}
if (!defined('APP_URL')) {
    require_once __DIR__ . '/includes/config.php';
}
if (!function_exists('avatarUrl')) {
    require_once __DIR__ . '/includes/db_helpers.php';
}

$_nav = [
    'loggedIn' => isLoggedIn(),
    'isAdmin'  => isAdmin(),
    'avatar'   => isLoggedIn() ? avatarUrl(currentAvatar()) : '',
    'username' => currentUsername(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Manager</title>
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>/uploads/icons/icon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to content</a>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo APP_URL; ?>/index.php">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="flex-shrink:0;">
                <defs>
                    <linearGradient id="logo-grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#6382ff"/>
                        <stop offset="1" stop-color="#a78bfa"/>
                    </linearGradient>
                    <filter id="logo-glow">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="1.5" result="blur"/>
                        <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                    </filter>
                </defs>
                <!-- Card body -->
                <rect x="3" y="6" width="26" height="22" rx="4" fill="url(#logo-grad)"/>
                <!-- Header strip -->
                <rect x="3" y="6" width="26" height="8" rx="4" fill="rgba(0,0,0,0.18)"/>
                <rect x="3" y="10" width="26" height="4" fill="rgba(0,0,0,0.18)"/>
                <!-- Binding loops -->
                <rect x="10" y="3" width="3" height="7" rx="1.5" fill="white" opacity=".9"/>
                <rect x="19" y="3" width="3" height="7" rx="1.5" fill="white" opacity=".9"/>
                <!-- Calendar lines -->
                <rect x="8" y="18" width="5" height="1.8" rx=".9" fill="white" opacity=".85"/>
                <rect x="8" y="22" width="5" height="1.8" rx=".9" fill="white" opacity=".85"/>
                <rect x="15.5" y="18" width="5" height="1.8" rx=".9" fill="white" opacity=".85"/>
                <!-- Checkmark on last slot -->
                <path d="M15.5 22.5l1.4 1.4 2.8-2.8" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" opacity=".95"/>
            </svg>
            <span>Events Manager</span>
        </a>

        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation"
                style="color:var(--text-secondary);">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <div class="navbar-nav ms-auto align-items-center gap-1">
                <?php if ($_nav['loggedIn']): ?>
                    <a class="nav-link" href="<?php echo APP_URL; ?>/events">
                        <i class="fa-solid fa-ticket me-1"></i>Events
                    </a>
                    <a class="nav-link" href="<?php echo APP_URL; ?>/my-events">
                        <i class="fa-solid fa-calendar-check me-1"></i>My Events
                    </a>
                    <?php if ($_nav['isAdmin']): ?>
                        <a class="nav-link" href="<?php echo APP_URL; ?>/users">
                            <i class="fa-solid fa-users-gear me-1"></i>Users
                        </a>
                        <a class="nav-link" href="<?php echo APP_URL; ?>/add-event">
                            <i class="fa-solid fa-circle-plus me-1"></i>Add Event
                        </a>
                    <?php endif; ?>
                    <a class="nav-link d-flex align-items-center gap-2" href="<?php echo APP_URL; ?>/profile">
                        <img src="<?php echo htmlspecialchars($_nav['avatar']); ?>"
                             alt="Avatar <?php echo $_nav['username']; ?>"
                             class="navbar-avatar-img">
                        <span><?php echo $_nav['username']; ?></span>
                    </a>
                    <a class="nav-link" href="<?php echo APP_URL; ?>/logout"
                       style="color:var(--danger)!important;">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Log out
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo APP_URL; ?>/login">Log in</a>
                    <a class="btn btn-primary btn-sm ms-1" href="<?php echo APP_URL; ?>/register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
