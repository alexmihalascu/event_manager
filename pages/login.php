<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) {
    header('Location: /event_manager/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT users.*, profiles.avatar
         FROM users
         LEFT JOIN profiles ON users.id = profiles.user_id
         WHERE users.username = ?"
    );
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        $_SESSION['avatar']   = $user['avatar'] ?? 'default_avatar.png';
        header('Location: /event_manager/index.php');
        exit;
    }

    $error = 'Invalid username or password.';
}

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="auth-wrap">
    <div class="auth-card">
        <div style="text-align:center;margin-bottom:2rem;">
            <div class="auth-icon-wrap">
                <i class="fa-solid fa-calendar-star"></i>
            </div>
            <h2>Welcome back</h2>
            <p class="auth-subtitle">Sign in to your Events Manager account.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       required autocomplete="username" placeholder="your_username">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       required autocomplete="current-password" placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg mb-4">
                <i class="fa-solid fa-right-to-bracket"></i> Sign in
            </button>
        </form>

        <p style="text-align:center;font-size:.875rem;color:var(--text-muted);">
            No account? <a href="/event_manager/register">Create one</a>
        </p>
    </div>
</main>
</body></html>
