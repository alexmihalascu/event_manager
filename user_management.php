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

function deleteUser($userId)
{
    global $conn;

    $conn->begin_transaction();

    try {
        $commentsSql = "DELETE FROM comments WHERE user_id = ?";
        if ($commentsStmt = $conn->prepare($commentsSql)) {
            $commentsStmt->bind_param("i", $userId);
            $commentsStmt->execute();
            $commentsStmt->close();
        }

        $profileSql = "DELETE FROM profiles WHERE user_id = ?";
        if ($profileStmt = $conn->prepare($profileSql)) {
            $profileStmt->bind_param("i", $userId);
            $profileStmt->execute();
            $profileStmt->close();
        }

        $userSql = "DELETE FROM users WHERE id = ?";
        if ($userStmt = $conn->prepare($userSql)) {
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userStmt->close();
        }

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
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="card">
        <h1>User Management</h1>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="actions">
                            <?php if ($user['is_admin']) : ?>
                                <a href="user_management.php?toggleAdmin=0&userId=<?php echo $user['id']; ?>" class="demote-button">Demote from Admin</a>
                            <?php else : ?>
                                <a href="user_management.php?toggleAdmin=1&userId=<?php echo $user['id']; ?>" class="promote-button">Promote to Admin</a>
                            <?php endif; ?>
                            <a href="user_management.php?deleteUser=1&userId=<?php echo $user['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this user?');">Delete User</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>