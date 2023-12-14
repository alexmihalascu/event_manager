<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo "<p>You are not an admin.</p>";
    echo "<p><a href='index.php'>Go back to the homepage</a></p>";
    exit;
}

// Function to toggle admin status
function toggleAdminStatus($userId, $makeAdmin)
{
    global $conn;
    $sql = "UPDATE users SET is_admin = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $makeAdmin, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

// Function to delete a user
function deleteUser($userId)
{
    global $conn;

    $conn->begin_transaction();

    try {
        // Delete comments made by the user
        $commentsSql = "DELETE FROM comments WHERE user_id = ?";
        if ($commentsStmt = $conn->prepare($commentsSql)) {
            $commentsStmt->bind_param("i", $userId);
            $commentsStmt->execute();
            $commentsStmt->close();
        }

        // Delete the user's profile
        $profileSql = "DELETE FROM profiles WHERE user_id = ?";
        if ($profileStmt = $conn->prepare($profileSql)) {
            $profileStmt->bind_param("i", $userId);
            $profileStmt->execute();
            $profileStmt->close();
        }

        // Delete the user
        $userSql = "DELETE FROM users WHERE id = ?";
        if ($userStmt = $conn->prepare($userSql)) {
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userStmt->close();
        }

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

if (isset($_GET['toggleAdmin']) && isset($_GET['userId'])) {
    $makeAdmin = ($_GET['toggleAdmin'] == '1') ? 1 : 0;
    toggleAdminStatus($_GET['userId'], $makeAdmin);
    header('Location: user_management.php');
    exit;
}

if (isset($_GET['deleteUser']) && isset($_GET['userId'])) {
    deleteUser($_GET['userId']);
    header('Location: user_management.php');
    exit;
}

$sql = "SELECT id, username, is_admin FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body">
            <h1 class="text-center">User Management</h1>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Username</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="text-end">
                                    <?php if ($user['is_admin']) : ?>
                                        <a href="user_management.php?toggleAdmin=0&userId=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm me-2">Demote to User</a>
                                    <?php else : ?>
                                        <a href="user_management.php?toggleAdmin=1&userId=<?php echo $user['id']; ?>" class="btn btn-success btn-sm me-2">Promote to Admin</a>
                                    <?php endif; ?>
                                    <a href="user_management.php?deleteUser=1&userId=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete User</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
