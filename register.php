<?php
include 'navbar.php';
include 'db_config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

function sanitizeInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

$registration_error = ''; // Initialize an error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $checkUserSql = "SELECT * FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($checkUserSql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $registration_error = "An account with this username already exists. Please choose a different username.";
        } else {
            $insertSql = "INSERT INTO users (username, password) VALUES (?, ?)";
            if ($insertStmt = $conn->prepare($insertSql)) {
                $insertStmt->bind_param("ss", $username, $password);
                if ($insertStmt->execute()) {
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
                    $registration_error = "Error: " . $insertStmt->error;
                }
                $insertStmt->close();
            }
        }
        $stmt->close();
    } else {
        $registration_error = "Error preparing SQL statement.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section class="vh-100">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-dark text-white" style="border-radius: 1rem;">
                        <div class="card-body p-4 text-center">
                            <div class="mb-md-4 mt-md-4 pb-4">
                                <form method="post" action="register.php">
                                    <h2 class="fw-bold mb-3 text-uppercase">Register</h2>
                                    <p class="text-white-50 mb-4">Please enter your username and password to register!</p>

                                    <?php if ($registration_error): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo $registration_error; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label class="form-label" for="username">Username</label>
                                        <input type="text" id="username" name="username" class="form-control" required />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="password">Password</label>
                                        <input type="password" id="password" name="password" class="form-control" required />
                                    </div>

                                    <button class="btn btn-outline-light btn-lg px-5" type="submit">Register</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
