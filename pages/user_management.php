<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$flash = '';

if (isset($_GET['toggleAdmin'], $_GET['userId']) && is_numeric($_GET['userId'])) {
    $makeAdmin = (int)(bool)$_GET['toggleAdmin'];
    $uid       = (int)$_GET['userId'];
    if ($uid !== currentUserId()) {
        $s = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $s->bind_param('ii', $makeAdmin, $uid);
        $s->execute();
        $s->close();
    }
    header('Location: ' . APP_URL . '/users?flash=' . ($makeAdmin ? 'promoted' : 'demoted'));
    exit;
}

if (isset($_GET['deleteUser'], $_GET['userId']) && is_numeric($_GET['userId'])) {
    $uid = (int)$_GET['userId'];
    if ($uid !== currentUserId()) {
        $conn->begin_transaction();
        try {
            foreach ([
                "DELETE FROM comments WHERE user_id = ?",
                "DELETE FROM event_attendance WHERE user_id = ?",
                "DELETE FROM profiles WHERE user_id = ?",
                "DELETE FROM users WHERE id = ?",
            ] as $sql) {
                $s = $conn->prepare($sql);
                $s->bind_param('i', $uid);
                $s->execute();
                $s->close();
            }
            $conn->commit();
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
        }
    }
    header('Location: ' . APP_URL . '/users?flash=deleted');
    exit;
}

$flashMsg = match ($_GET['flash'] ?? '') {
    'promoted' => ['type' => 'success', 'msg' => 'User promoted to admin.'],
    'demoted'  => ['type' => 'warning', 'msg' => 'User demoted to member.'],
    'deleted'  => ['type' => 'danger',  'msg' => 'User deleted.'],
    default    => null,
};

$search    = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? 'all';

$where  = [];
$sql_where = '';

if ($search !== '') {
    $where[] = 'u.username LIKE ?';
}
if ($roleFilter === 'admin') {
    $where[] = 'u.is_admin = 1';
} elseif ($roleFilter === 'member') {
    $where[] = 'u.is_admin = 0';
}

$sql = "SELECT u.id, u.username, u.is_admin, p.avatar,
               (SELECT COUNT(*) FROM event_attendance ea WHERE ea.user_id = u.id) AS event_count,
               (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comment_count
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id";

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY u.username ASC';

$stmt = $conn->prepare($sql);
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt->bind_param('s', $like);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalUsers  = count($users);
$adminCount  = count(array_filter($users, fn($u) => $u['is_admin']));
$memberCount = $totalUsers - $adminCount;

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper">

    <!-- Page header -->
    <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <div class="label mb-1"><i class="fa-solid fa-shield-halved me-1"></i>Admin</div>
            <h1>User Management</h1>
            <p style="color:var(--text-muted);font-size:.875rem;margin-top:.4rem;">
                Manage roles and accounts across the platform.
            </p>
        </div>
        <div class="um-stats-row">
            <div class="um-stat-pill">
                <i class="fa-solid fa-users" style="color:var(--primary);"></i>
                <span class="um-stat-num"><?php echo $totalUsers; ?></span>
                <span class="um-stat-label">Total</span>
            </div>
            <div class="um-stat-pill">
                <i class="fa-solid fa-shield-halved" style="color:var(--accent);"></i>
                <span class="um-stat-num"><?php echo $adminCount; ?></span>
                <span class="um-stat-label">Admins</span>
            </div>
            <div class="um-stat-pill">
                <i class="fa-solid fa-user" style="color:var(--success);"></i>
                <span class="um-stat-num"><?php echo $memberCount; ?></span>
                <span class="um-stat-label">Members</span>
            </div>
        </div>
    </div>

    <?php if ($flashMsg): ?>
        <div class="alert alert-<?php echo $flashMsg['type']; ?> mb-4">
            <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMsg['msg']; ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="get" class="um-filters">
        <div class="um-search-wrap">
            <i class="fa-solid fa-magnifying-glass um-search-icon"></i>
            <input type="text" name="search" class="form-control um-search-input"
                   placeholder="Search by username…"
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="um-role-tabs">
            <?php foreach ([
                ['all',    'All',     ''],
                ['admin',  'Admins',  'fa-shield-halved'],
                ['member', 'Members', 'fa-user'],
            ] as [$val, $label, $icon]): ?>
                <button type="submit" name="role" value="<?php echo $val; ?>"
                        class="um-role-tab <?php echo $roleFilter === $val ? 'active' : ''; ?>">
                    <?php if ($icon): ?><i class="fa-solid <?php echo $icon; ?>"></i><?php endif; ?>
                    <?php echo $label; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </form>

    <!-- User grid -->
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-user-slash"></i>
            <p>No users found<?php echo $search ? ' matching "' . htmlspecialchars($search) . '"' : ''; ?>.</p>
        </div>
    <?php else: ?>
        <div class="um-user-grid">
            <?php foreach ($users as $user):
                $isSelf  = ($user['id'] === currentUserId());
                $userAvatar = avatarUrl($user['avatar'] ?? '');
            ?>
                <div class="um-user-card <?php echo $isSelf ? 'um-user-card--self' : ''; ?>">
                    <div class="um-user-card-inner">

                        <!-- Avatar + identity -->
                        <div class="um-user-identity">
                            <a href="<?php echo APP_URL; ?>/profile/<?php echo (int)$user['id']; ?>"
                               class="um-avatar-link">
                                <img src="<?php echo htmlspecialchars($userAvatar); ?>"
                                     alt="<?php echo htmlspecialchars($user['username']); ?>"
                                     class="um-avatar">
                                <?php if ($user['is_admin']): ?>
                                    <span class="um-avatar-badge" title="Admin">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <div class="um-user-info">
                                <a href="<?php echo APP_URL; ?>/profile/<?php echo (int)$user['id']; ?>"
                                   class="um-username">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                    <?php if ($isSelf): ?>
                                        <span class="um-self-tag">you</span>
                                    <?php endif; ?>
                                </a>
                                <div class="um-role-badge <?php echo $user['is_admin'] ? 'um-role-admin' : 'um-role-member'; ?>">
                                    <i class="fa-solid <?php echo $user['is_admin'] ? 'fa-shield-halved' : 'fa-user'; ?>"></i>
                                    <?php echo $user['is_admin'] ? 'Admin' : 'Member'; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Activity numbers -->
                        <div class="um-user-activity">
                            <div class="um-activity-item">
                                <span class="um-activity-num"><?php echo (int)$user['event_count']; ?></span>
                                <span class="um-activity-label">events</span>
                            </div>
                            <div class="um-activity-divider"></div>
                            <div class="um-activity-item">
                                <span class="um-activity-num"><?php echo (int)$user['comment_count']; ?></span>
                                <span class="um-activity-label">comments</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <?php if (!$isSelf): ?>
                        <div class="um-user-actions">
                            <?php if ($user['is_admin']): ?>
                                <a href="?toggleAdmin=0&userId=<?php echo (int)$user['id']; ?>"
                                   class="btn btn-sm btn-warning um-action-btn"
                                   onclick="return confirm('Demote <?php echo htmlspecialchars($user['username']); ?> to Member?');"
                                   title="Demote to Member">
                                    <i class="fa-solid fa-arrow-down"></i> Demote
                                </a>
                            <?php else: ?>
                                <a href="?toggleAdmin=1&userId=<?php echo (int)$user['id']; ?>"
                                   class="btn btn-sm btn-success um-action-btn"
                                   onclick="return confirm('Promote <?php echo htmlspecialchars($user['username']); ?> to Admin?');"
                                   title="Promote to Admin">
                                    <i class="fa-solid fa-arrow-up"></i> Promote
                                </a>
                            <?php endif; ?>
                            <a href="?deleteUser=1&userId=<?php echo (int)$user['id']; ?>"
                               class="btn btn-sm btn-danger um-action-btn"
                               onclick="return confirm('Permanently delete <?php echo htmlspecialchars($user['username']); ?>? This cannot be undone.');"
                               title="Delete user">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="um-user-actions">
                            <a href="<?php echo APP_URL; ?>/profile" class="btn btn-sm btn-secondary um-action-btn">
                                <i class="fa-solid fa-pen"></i> Edit profile
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>
</body></html>
