<?php
session_start();
include 'db_config.php';

function sanitizeInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    // Modify the SQL query to include a JOIN with the profiles table
    $sql = "SELECT users.*, profiles.avatar FROM users LEFT JOIN profiles ON users.id = profiles.user_id WHERE users.username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // Fetch the avatar from the profiles table
                $_SESSION['avatar'] = $user['avatar'] ?? 'defaultavatar.png';

                header('Location: index.php');
                exit;
            } else {
                $login_error = "Invalid password.";
            }
        } else {
            $login_error = "Username does not exist.";
        }
        $stmt->close();
    } else {
        echo "Error preparing SQL statement.";
    }
}

include 'navbar.php';
?>

<head>
    <meta rel="icon" href="uploads/icons/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>



<div class="login-page">
    <?php if (isset($login_error)) : ?>
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