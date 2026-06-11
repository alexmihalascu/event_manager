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

    if (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'Username already taken. Choose a different one.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param('ss', $username, $hash);
            $stmt->execute();
            $newId = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO profiles (user_id, bio, avatar) VALUES (?, '', 'default_avatar.png')");
            $stmt->bind_param('i', $newId);
            $stmt->execute();
            $stmt->close();

            header('Location: /event_manager/login');
            exit;
        }
    }
}

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="auth-wrap">
    <div class="auth-card">
        <div style="text-align:center;margin-bottom:2rem;">
            <div class="auth-icon-wrap">
                <i class="fa-solid fa-calendar-star"></i>
            </div>
            <h2>Create account</h2>
            <p class="auth-subtitle">Join Events Manager and discover what's happening.</p>
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
                       required autocomplete="username" placeholder="choose_a_username" minlength="3">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       required autocomplete="new-password" placeholder="min. 6 characters" minlength="6">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg mb-4">
                <i class="fa-solid fa-user-plus"></i> Create account
            </button>
        </form>

        <p style="text-align:center;font-size:.875rem;color:var(--text-muted);">
            Already have an account? <a href="/event_manager/login">Sign in</a>
        </p>
    </div>
</main>
</body></html>
