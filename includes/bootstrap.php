<?php
/**
 * Application bootstrap — single require at top of every page.
 * Starts session, loads config + helpers + auth.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_helpers.php';
require_once __DIR__ . '/auth.php';
