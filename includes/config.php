<?php
/**
 * App configuration — edit credentials here only.
 */

define('APP_ROOT', dirname(__DIR__));
define('APP_URL',  '/event_manager');
define('UPLOAD_EVENTS',  APP_ROOT . '/uploads/events/');
define('UPLOAD_AVATARS', APP_ROOT . '/uploads/avatars/');

/* Clean URL route helper */
function route(string $name, array $params = []): string {
    $base = APP_URL . '/' . $name;
    return $params ? $base . '?' . http_build_query($params) : $base;
}

$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'event_manager';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
