<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /event_manager/events');
    exit;
}

$eventId = (int)$_GET['id'];
$event   = getEvent($eventId);

if (!$event) {
    header('Location: /event_manager/events');
    exit;
}

// Handle POST — attend/unattend/comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = currentUserId();

    if (isset($_POST['attend'])) {
        $s = $conn->prepare("INSERT IGNORE INTO event_attendance (event_id, user_id) VALUES (?, ?)");
        $s->bind_param('ii', $eventId, $uid);
        $s->execute();
        $s->close();
    } elseif (isset($_POST['unattend'])) {
        $s = $conn->prepare("DELETE FROM event_attendance WHERE event_id = ? AND user_id = ?");
        $s->bind_param('ii', $eventId, $uid);
        $s->execute();
        $s->close();
    } elseif (!empty($_POST['comment'])) {
        $comment = trim($_POST['comment']);
        $s = $conn->prepare("INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)");
        $s->bind_param('iis', $eventId, $uid, $comment);
        $s->execute();
        $s->close();
    }

    header("Location: /event_manager/events/?id=$eventId");
    exit;
}

$attendeeCount   = isAdmin() ? getAttendeeCount($eventId) : 0;
$userHasAttended = isUserAttending($eventId, currentUserId());
$comments        = getEventComments($eventId);

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper">

    <a href="/event_manager/events" class="btn btn-ghost btn-sm mb-4" style="padding-left:0;">
        <i class="fa-solid fa-arrow-left"></i> Back to events
    </a>

    <div class="row g-4">
        <!-- Event details -->
        <div class="col-lg-7">
            <div class="card" style="border-radius:var(--r-xl);">
                <div style="aspect-ratio:16/9;overflow:hidden;border-radius:var(--r-xl) var(--r-xl) 0 0;">
                    <img src="<?php echo htmlspecialchars(eventPhotoUrl($event['photo'])); ?>"
                         alt="<?php echo htmlspecialchars($event['name']); ?>"
                         style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="card-body" style="padding:2rem;">
                    <span class="badge-tag badge-primary mb-3">
                        <i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($event['category_name']); ?>
                    </span>
                    <h1 style="font-size:clamp(1.4rem,3vw,2rem);margin-bottom:1.5rem;">
                        <?php echo htmlspecialchars($event['name']); ?>
                    </h1>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.75rem;">
                        <div class="info-tile">
                            <div class="info-tile-label">Location</div>
                            <div class="info-tile-value">
                                <i class="fa-solid fa-location-dot" style="color:var(--primary);"></i>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                        </div>
                        <div class="info-tile">
                            <div class="info-tile-label">Date &amp; time</div>
                            <div class="info-tile-value">
                                <i class="fa-solid fa-calendar" style="color:var(--primary);"></i>
                                <?php echo date('d M Y', strtotime($event['event_date'])); ?> at <?php echo date('H:i', strtotime($event['event_time'])); ?>
                            </div>
                        </div>
                        <div class="info-tile">
                            <div class="info-tile-label">Price</div>
                            <div class="info-tile-value" style="color:var(--primary);font-weight:700;font-size:1.1rem;">
                                <?php echo $event['price'] == 0 ? 'Free' : number_format($event['price']) . ' Lei'; ?>
                            </div>
                        </div>
                        <?php if (isAdmin()): ?>
                        <div class="info-tile">
                            <div class="info-tile-label">Attendees</div>
                            <div class="info-tile-value" style="font-weight:700;font-size:1.1rem;">
                                <i class="fa-solid fa-users" style="color:var(--primary);"></i>
                                <?php echo $attendeeCount; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Attend button -->
                    <form method="post" class="mb-3">
                        <?php if (!$userHasAttended): ?>
                            <button type="submit" name="attend" class="btn btn-primary btn-lg" style="width:100%;">
                                <i class="fa-solid fa-circle-check"></i> Attend this event
                            </button>
                        <?php else: ?>
                            <button type="submit" name="unattend" class="btn btn-secondary btn-lg" style="width:100%;">
                                <i class="fa-solid fa-circle-minus"></i> Cancel attendance
                            </button>
                        <?php endif; ?>
                    </form>

                    <!-- Admin actions -->
                    <?php if (isAdmin()): ?>
                        <div style="border-top:1px solid var(--border);padding-top:1.25rem;">
                            <p class="label mb-3">Admin actions</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="/event_manager/toggle-top?event_id=<?php echo $eventId; ?>&top_event_status=<?php echo $event['top_event'] ? 0 : 1; ?>"
                                   class="btn <?php echo $event['top_event'] ? 'btn-warning' : 'btn-success'; ?> btn-sm">
                                    <i class="fa-solid <?php echo $event['top_event'] ? 'fa-star-half-stroke' : 'fa-star'; ?>"></i>
                                    <?php echo $event['top_event'] ? 'Unmark top event' : 'Mark as top event'; ?>
                                </a>
                                <a href="/event_manager/attendance?event_id=<?php echo $eventId; ?>"
                                   class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-list-check"></i> Attendees
                                </a>
                                <a href="/event_manager/edit-event?id=<?php echo $eventId; ?>"
                                   class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <a href="/event_manager/delete-event?id=<?php echo $eventId; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this event permanently?');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="col-lg-5">
            <div class="card" style="border-radius:var(--r-xl);position:sticky;top:5.5rem;">
                <div class="card-body" style="padding:1.5rem;">
                    <h2 style="font-size:1.15rem;margin-bottom:1.25rem;">
                        <i class="fa-regular fa-comments me-2" style="color:var(--primary);"></i>
                        Comments <span style="color:var(--text-muted);font-weight:400;font-size:.9rem;">(<?php echo count($comments); ?>)</span>
                    </h2>

                    <div style="max-height:380px;overflow-y:auto;display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.25rem;">
                        <?php if (empty($comments)): ?>
                            <div class="empty-state" style="padding:2rem 1rem;">
                                <i class="fa-regular fa-comment-dots" style="font-size:1.75rem;display:block;margin-bottom:.75rem;"></i>
                                <p style="font-size:.85rem;">No comments yet. Be the first.</p>
                            </div>
                        <?php endif; ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="comment">
                                <div class="comment-avatar">
                                    <img src="<?php echo htmlspecialchars(avatarUrl($c['avatar'] ?? '')); ?>"
                                         alt="Avatar <?php echo htmlspecialchars($c['username']); ?>">
                                </div>
                                <div class="comment-content">
                                    <p>
                                        <strong>
                                            <a href="/event_manager/profile/<?php echo (int)$c['user_id']; ?>">
                                                <?php echo htmlspecialchars($c['username']); ?>
                                            </a>
                                        </strong>
                                        <?php echo htmlspecialchars($c['comment']); ?>
                                    </p>
                                    <span style="font-size:.72rem;color:var(--text-muted);">
                                        <?php echo date('d M Y, H:i', strtotime($c['comment_date'])); ?>
                                    </span>
                                    <?php if (isAdmin()): ?>
                                        <a href="/event_manager/delete-comment?comment_id=<?php echo (int)$c['id']; ?>&event_id=<?php echo $eventId; ?>"
                                           class="delete-comment"
                                           onclick="return confirm('Delete this comment?');">
                                            <i class="fa-solid fa-trash fa-xs"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--border);padding-top:1.25rem;">
                        <h3 style="font-size:.95rem;margin-bottom:.75rem;color:var(--text-secondary);">Leave a comment</h3>
                        <form method="post">
                            <textarea name="comment" required class="form-control non-resizable mb-3"
                                      placeholder="Share your thoughts…" rows="3"></textarea>
                            <button type="submit" class="btn btn-primary" style="width:100%;">
                                <i class="fa-solid fa-paper-plane"></i> Post comment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>
</body></html>
