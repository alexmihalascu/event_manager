<?php
// Start the session
session_start();
include 'db_config.php';

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL to prevent SQL injection
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['avatar'] = $user['avatar'];

            // Redirect to index.php
            header('Location: index.php');
            exit;
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "Username does not exist.";
    }
}
include 'navbar.php';
?>
<link rel="stylesheet" href="style.css">

<div class="login-page">
    <?php if (isset($login_error)): ?>
        <div class="error-card" id="error-message">
            <?php echo $login_error; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="post" action="login.php">
            <h1>Login</h1>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Login">
            </div>
        </form>
    </div>
</div>

<script>
// If there's an error message, we want to clear it after 5 seconds
const errorMessage = document.getElementById('error-message');
if (errorMessage) {
    setTimeout(() => {
        errorMessage.style.display = 'none';
    }, 5000); // 5000 milliseconds = 5 seconds
}
</script>
