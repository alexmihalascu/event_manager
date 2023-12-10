<?php
include 'navbar.php';
include 'db_config.php';

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $checkUserSql = "SELECT * FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($checkUserSql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "An account with this username already exists. Please choose a different username.";
        } else {
            // Username does not exist, proceed with the insertion
            $insertSql = "INSERT INTO users (username, password) VALUES (?, ?)";
            if ($insertStmt = $conn->prepare($insertSql)) {
                $insertStmt->bind_param("ss", $username, $password);
                if ($insertStmt->execute()) {
                    // Create a default profile for the new user
                    $newUserId = $conn->insert_id;
                    $defaultProfileSql = "INSERT INTO profiles (user_id, bio, avatar) VALUES (?, '', 'default_avatar.png')";
                    if ($profileStmt = $conn->prepare($defaultProfileSql)) {
                        $profileStmt->bind_param("i", $newUserId);
                        $profileStmt->execute();
                        $profileStmt->close();
                    }
                    header('Location: login.php');
                    exit();
                } else {
                    echo "Error: " . $insertStmt->error;
                }
                $insertStmt->close();
            }
        }
        $stmt->close();
    } else {
        echo "Error preparing SQL statement.";
    }
}
?>

<link rel="stylesheet" href="style.css">
<div class="form-container">
    <form method="post" action="register.php">
        <h1 class="card-header">Registration</h1>
        Username: <input type="text" name="username" required>
        Password: <input type="password" name="password" required>
        <input type="submit" value="Register">
    </form>
</div>
