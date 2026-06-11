<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    header('Location: /event_manager/events');
    exit;
}

$eventId = (int)$_GET['event_id'];
$event   = getEvent($eventId);

if (!$event) {
    header('Location: /event_manager/events');
    exit;
}

$stmt = $conn->prepare(
    "SELECT users.id, users.username, profiles.avatar, profiles.bio
     FROM event_attendance
     JOIN users ON event_attendance.user_id = users.id
     LEFT JOIN profiles ON users.id = profiles.user_id
     WHERE event_attendance.event_id = ?
     ORDER BY users.username ASC"
);
$stmt->bind_param('i', $eventId);
$stmt->execute();
$result    = $stmt->get_result();
$attendees = [];
while ($row = $result->fetch_assoc()) {
    $attendees[] = $row;
}
$stmt->close();

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper" style="max-width:760px;">
    <div class="page-header">
        <a href="/event_manager/events/?id=<?php echo $eventId; ?>" class="btn btn-ghost btn-sm mb-3" style="padding-left:0;">
            <i class="fa-solid fa-arrow-left"></i> Back to event
        </a>
        <h1><i class="fa-solid fa-list-check me-2" style="color:var(--primary);"></i>Attendees</h1>
        <p style="color:var(--text-muted);font-size:.875rem;margin-top:.25rem;">
            <?php echo htmlspecialchars($event['name']); ?> &mdash; <?php echo count($attendees); ?> registered
        </p>
    </div>

    <div class="card" style="border-radius:var(--r-xl);">
        <?php if (!empty($attendees)): ?>
            <?php foreach ($attendees as $i => $a): ?>
                <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;<?php echo $i > 0 ? 'border-top:1px solid var(--border);' : ''; ?>">
                    <img src="<?php echo htmlspecialchars(avatarUrl($a['avatar'] ?? '')); ?>"
                         alt="" style="width:40px;height:40px;border-radius:var(--r-full);object-fit:cover;border:2px solid var(--border);flex-shrink:0;">
                    <div style="flex:1;min-width:0;">
                        <a href="/event_manager/profile/<?php echo (int)$a['id']; ?>"
                           style="font-weight:600;color:var(--text-primary);font-size:.9rem;">
                            <?php echo htmlspecialchars($a['username']); ?>
                        </a>
                        <?php if (!empty($a['bio'])): ?>
                            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:400px;">
                                <?php echo htmlspecialchars($a['bio']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="/event_manager/profile/<?php echo (int)$a['id']; ?>"
                       class="btn btn-ghost btn-sm">
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-regular fa-user-xmark"></i>
                <p>No one has registered yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>
</body></html>
