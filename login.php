<?php
session_start();
include 'db_config.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id']) || isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}


function sanitizeInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    // SQL query with JOIN for avatar
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
                $_SESSION['avatar'] = $user['avatar'] ?? 'defaultavatar.png';

                header('Location: index.php');
                exit;
            } else {
                $login_error = "Invalid username or password.";
            }
        } else {
            $login_error = "Invalid username or password.";
        }
        $stmt->close();
    } else {
        echo "Error preparing SQL statement.";
    }
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <section class="vh-100">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-dark text-white">
                        <div class="card-body p-4 text-center">
                            <form method="post" action="login.php">
                                <h2 class="fw-bold mb-3 text-uppercase">Login</h2>
                                <?php if (!empty($login_error)) : ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo $login_error; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" required />
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control" required />
                                </div>
                                <button class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>