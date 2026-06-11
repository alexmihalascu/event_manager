<?php
/**
 * Central router — Apache rewrites clean URLs here.
 * Maps $_GET['_route'] → pages/*.php
 * Query params from the original URL are merged in via QSA flag.
 */

$map = [
    'login'          => 'pages/login.php',
    'register'       => 'pages/register.php',
    'logout'         => 'pages/logout.php',
    'events'         => 'pages/events_list.php',
    'event'          => 'pages/event_details.php',
    'my-events'      => 'pages/my_events.php',
    'profile'        => 'pages/edit_profile.php',
    'user'           => 'pages/user_profile.php',
    'add-event'      => 'pages/add_event.php',
    'edit-event'     => 'pages/edit_event.php',
    'delete-event'   => 'pages/delete_event.php',
    'users'          => 'pages/user_management.php',
    'attendance'     => 'pages/attendance_list.php',
    'delete-comment' => 'pages/delete_comment.php',
    'toggle-top'     => 'pages/toggle_top_event.php',
];

$route = $_GET['_route'] ?? '';
unset($_GET['_route']);

// Remap 'id' param for event/user detail routes
if ($route === 'event' && isset($_GET['id'])) {
    // event_details.php reads $_GET['id'] — already set via QSA
}
if ($route === 'user' && isset($_GET['id'])) {
    $_GET['user_id'] = $_GET['id'];
    unset($_GET['id']);
}

$file = $map[$route] ?? null;

if ($file && file_exists(__DIR__ . '/' . $file)) {
    require __DIR__ . '/' . $file;
} else {
    http_response_code(404);
    require __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/navbar.php';
    echo '<main id="main-content" class="page-content"><div class="page-wrapper"><div class="empty-state" style="padding:6rem 2rem;"><i class="fa-solid fa-triangle-exclamation"></i><p>Page not found.</p><a href="' . (defined('APP_URL') ? APP_URL : '/event_manager') . '/" class="btn btn-primary mt-3">Go home</a></div></div></main></body></html>';
}
