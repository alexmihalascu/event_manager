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

// Fetch current bio
$currentBio = '';
$fetchBioSql = "SELECT bio FROM profiles WHERE user_id = ?";
if ($stmt = $conn->prepare($fetchBioSql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($currentBio);
    $stmt->fetch();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $profileUpdated = false;

    // Update password
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

    // Update avatar
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

    // Update bio
    if (!empty($_POST['bio'])) {
        $bio = $_POST['bio'];
        $updateBioSql = "UPDATE profiles SET bio = ? WHERE user_id = ?";
        $stmt = $conn->prepare($updateBioSql);
        $stmt->bind_param("si", $bio, $user_id);
        $stmt->execute();
        $stmt->close();
        $profileUpdated = true;

        // Refresh the page to update the bio display
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if ($profileUpdated) {
        $_SESSION['success_msg'] = "Profile updated successfully.";
    } else if (!empty($errorMsg)) {
        $_SESSION['error_msg'] = $errorMsg;
    }
}

// Display success or error message after redirect
if (isset($_SESSION['success_msg'])) {
    echo "<div class='alert alert-success'>".$_SESSION['success_msg']."</div>";
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    echo "<div class='alert alert-danger'>".$_SESSION['error_msg']."</div>";
    unset($_SESSION['error_msg']);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title text-center">Edit Profile</h1>
            <form action="edit_profile.php" method="post" enctype="multipart/form-data">
                <div class="row">
                    <!-- Left Column for Avatar -->
                    <div class="col-md-4 text-center">
                        <img src="<?php echo "uploads/avatars/" . $_SESSION['avatar']; ?>" alt="Avatar" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Change Avatar:</label>
                            <input type="file" id="avatar" name="avatar" class="form-control">
                        </div>
                    </div>

                    <!-- Right Column for User Info -->
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" id="username" name="username" value="<?php echo $_SESSION['username']; ?>" disabled class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio:</label>
                            <textarea id="bio" name="bio" class="form-control non-resizable"><?php echo htmlspecialchars($currentBio); ?></textarea>
                        </div>
                        <div class="text-center">
                            <input type="submit" value="Update Profile" class="btn btn-primary">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
