<?php
include 'navbar.php';
// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db_config.php';
    
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $defaultAvatar = "defaultavatar.png";

    // Check if username already exists
    $checkUserSql = "SELECT * FROM users WHERE username = '$username'";
    $checkUserResult = $conn->query($checkUserSql);

    if ($checkUserResult->num_rows > 0) {
        // Username already exists
        echo "An account with this username already exists. Please choose a different username.";
    } else {
        // Username does not exist, proceed with the insertion
        $sql = "INSERT INTO users (username, password, avatar) VALUES ('$username', '$password', '$defaultAvatar')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to login.php
            header('Location: login.php');
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
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
