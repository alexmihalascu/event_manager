<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db_config.php';
include 'navbar.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username']; // Retrieve the username from the session

    // Check if the password field is not empty
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        // Hash the new password here
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update the password in the database
        $updatePasswordSql = "UPDATE users SET password = ? WHERE username = ?";
        $stmt = $conn->prepare($updatePasswordSql);
        $stmt->bind_param("ss", $hashedPassword, $username);
        $stmt->execute();
        $stmt->close();
    }

    // Handle the avatar upload if a file was given
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['avatar']['tmp_name'];
        $originalName = basename($_FILES['avatar']['name']);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION); // Get the file extension
        $newName = $username . "_avatar." . $ext;
        $destination = "uploads/avatars/" . $newName;

        // Attempt to move the uploaded file to the destination
        if (move_uploaded_file($tmp_name, $destination)) {
            $_SESSION['avatar'] = $newName;
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE username = ?");
            $stmt->bind_param("ss", $newName, $username);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Failed to upload avatar.";
        }
        if (isset($_FILES['event_photo']) && $_FILES['event_photo']['error'] == 0) {
            $uploadDir = 'uploads/';
            $uploadFile = $uploadDir . basename($_FILES['event_photo']['name']);
            
            if (move_uploaded_file($_FILES['event_photo']['tmp_name'], $uploadFile)) {
                // Save $uploadFile to the database along with other event details
                $photoPath = $conn->real_escape_string($uploadFile);
                // Update your SQL insert/update statement to include the photo path
            } else {
                // Handle error
                echo "An error occurred while uploading the file.";
            }
        }
        
    }

    echo "Profile updated successfully.";
}
?>
<div class="form-container">
    <!-- HTML and form for updating profile -->
    <form action="edit_profile.php" method="post" enctype="multipart/form-data">
        Username: <input type="text" name="username" value="<?php echo $_SESSION['username']; ?>" disabled><br>
        Password: <input type="password" name="password"><br>
        Avatar: <input type="file" name="avatar"><br>
        <input type="submit" value="Update Profile">
    </form>
</div>
