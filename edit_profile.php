<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db_config.php';
include 'navbar.php';

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];

    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updatePasswordSql = "UPDATE users SET password = ? WHERE username = ?";
        $stmt = $conn->prepare($updatePasswordSql);
        $stmt->bind_param("ss", $hashedPassword, $username);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['avatar']['tmp_name'];
        $originalName = basename($_FILES['avatar']['name']);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $newName = $username . "_avatar." . $ext;
        $destination = "uploads/avatars/" . $newName;

        if (move_uploaded_file($tmp_name, $destination)) {
            $updateAvatarSql = "UPDATE profiles SET avatar = ? WHERE user_id = ?";
            if ($stmt = $conn->prepare($updateAvatarSql)) {
                $stmt->bind_param("si", $newName, $user_id);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['avatar'] = $newName;
        } else {
            echo "Failed to upload avatar.";
        }
    }

    echo "Profile updated successfully.";
}
?>

<link rel="stylesheet" href="style.css">

<div class="form-container">
    <form action="edit_profile.php" method="post" enctype="multipart/form-data">
        <h1>Edit Profile</h1>
        Username: <input type="text" name="username" value="<?php echo $_SESSION['username']; ?>" disabled><br>
        Password: <input type="password" name="password"><br>
        Avatar: <input type="file" name="avatar"><br>
        <input type="submit" value="Update Profile">
    </form>
</div>