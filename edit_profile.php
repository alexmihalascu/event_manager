<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db_config.php';
include 'navbar.php';

$user_id = $_SESSION['user_id'];
$profileUpdated = false;
$errorMsg = '';

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
        $profileUpdated = true;
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
                $_SESSION['avatar'] = $newName;
                $profileUpdated = true;
            }
        } else {
            $errorMsg = "Failed to upload avatar.";
        }
    }

    if ($profileUpdated) {
        echo "<div class='alert alert-success'>Profile updated successfully.</div>";
    } else if (!empty($errorMsg)) {
        echo "<div class='alert alert-danger'>$errorMsg</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title text-center">Edit Profile</h1>
            <form action="edit_profile.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $_SESSION['username']; ?>" disabled class="form-control">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="avatar" class="form-label">Avatar:</label>
                    <input type="file" id="avatar" name="avatar" class="form-control">
                </div>
                <div class="text-center">
                    <input type="submit" value="Update Profile" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
