<?php
/**
 * Auth guard helpers — call these at the top of any page that needs protection.
 * All functions redirect + exit on failure, so calling code can just call and continue.
 */

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /event_manager/login.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (empty($_SESSION['is_admin'])) {
        header('Location: /event_manager/index.php');
        exit;
    }
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return !empty($_SESSION['is_admin']);
}

function currentUserId(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function currentUsername(): string
{
    return htmlspecialchars($_SESSION['username'] ?? '');
}

function currentAvatar(): string
{
    return $_SESSION['avatar'] ?? 'default_avatar.png';
}

function sanitize(string $data): string
{
    return htmlspecialchars(stripslashes(trim($data)));
}
