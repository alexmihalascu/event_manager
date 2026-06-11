<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$userId = (int)$_GET['user_id'];

// Redirect to own profile editor if viewing yourself
if ($userId === currentUserId()) {
    header('Location: ' . APP_URL . '/profile');
    exit;
}

$profile = getUserProfile($userId);
if (!$profile) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$avatarSrc      = avatarUrl($profile['avatar'] ?? '');
$comments       = getUserComments($userId, 10);
$attendedEvents = getUserAttendedEvents($userId);
$now            = new DateTimeImmutable();
$upcomingEvents = array_filter($attendedEvents, fn($e) => new DateTimeImmutable($e['event_date']) > $now);
$pastEvents     = array_filter($attendedEvents, fn($e) => new DateTimeImmutable($e['event_date']) <= $now);
$memberSince    = null; // users table has no created_at column

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper" style="max-width:1100px;">

    <a href="javascript:history.back()" class="btn btn-ghost btn-sm mb-4" style="padding-left:0;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>

    <!-- Profile Hero Card -->
    <div class="profile-hero-card">
        <div class="profile-cover">
            <div class="profile-cover-mesh"></div>
        </div>
        <div class="profile-hero-body">
            <div class="profile-avatar-wrap">
                <img src="<?php echo htmlspecialchars($avatarSrc); ?>"
                     alt="Avatar of <?php echo htmlspecialchars($profile['username']); ?>">
                <div class="profile-avatar-ring"></div>
            </div>

            <div class="profile-hero-info">
                <div class="profile-hero-name-row">
                    <h1 class="profile-username"><?php echo htmlspecialchars($profile['username']); ?></h1>
                    <?php if ($profile['is_admin']): ?>
                        <span class="badge-tag badge-primary">
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($profile['bio'])): ?>
                    <p class="profile-bio-preview"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                <?php else: ?>
                    <p class="profile-bio-preview profile-bio-placeholder">This user hasn't written a bio yet.</p>
                <?php endif; ?>

                <?php if ($memberSince): ?>
                    <div class="profile-member-since">
                        <i class="fa-regular fa-calendar-plus fa-xs"></i>
                        Member since <?php echo date('F Y', strtotime($memberSince)); ?>
                    </div>
                <?php endif; ?>

                <div class="profile-stats-row">
                    <div class="profile-stat">
                        <span class="profile-stat-num"><?php echo count($attendedEvents); ?></span>
                        <span class="profile-stat-label">Events</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-num"><?php echo count($upcomingEvents); ?></span>
                        <span class="profile-stat-label">Upcoming</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-num"><?php echo count($comments); ?></span>
                        <span class="profile-stat-label">Comments</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-two-col">

        <!-- Activity Feed -->
        <div class="profile-col-edit" style="flex:2;">

            <!-- Comments -->
            <div class="card profile-activity-card">
                <div class="profile-activity-header">
                    <i class="fa-regular fa-comments" style="color:var(--primary);"></i>
                    <span>Recent comments</span>
                    <span class="profile-activity-count"><?php echo count($comments); ?></span>
                </div>
                <div class="profile-activity-body">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $c): ?>
                            <a href="<?php echo APP_URL; ?>/events/<?php echo (int)$c['event_id']; ?>"
                               class="comment-history-item">
                                <div class="chi-event">
                                    <i class="fa-solid fa-ticket fa-xs"></i>
                                    <?php echo htmlspecialchars($c['event_name']); ?>
                                </div>
                                <div class="chi-text"><?php echo htmlspecialchars($c['comment']); ?></div>
                                <div class="chi-date"><?php echo date('d M Y · H:i', strtotime($c['comment_date'])); ?></div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state" style="padding:2rem;">
                            <i class="fa-regular fa-comment-dots"></i>
                            <p>No comments yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Sidebar: Events -->
        <div class="profile-col-activity">

            <!-- Upcoming events -->
            <?php if (!empty($upcomingEvents)): ?>
            <div class="card profile-activity-card">
                <div class="profile-activity-header">
                    <i class="fa-regular fa-calendar" style="color:var(--success);"></i>
                    <span>Upcoming</span>
                    <span class="profile-activity-count" style="background:var(--success-dim);color:var(--success);"><?php echo count($upcomingEvents); ?></span>
                </div>
                <div class="profile-activity-body">
                    <?php foreach ($upcomingEvents as $e):
                        $days = (int)$now->diff(new DateTimeImmutable($e['event_date']))->days;
                    ?>
                        <a href="<?php echo APP_URL; ?>/events/<?php echo (int)$e['id']; ?>"
                           class="event-history-item">
                            <div class="ehi-countdown"><?php echo $days; ?><span>d</span></div>
                            <div class="ehi-info">
                                <div class="ehi-name"><?php echo htmlspecialchars($e['name']); ?></div>
                                <div class="ehi-date"><?php echo date('d M Y', strtotime($e['event_date'])); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Past events -->
            <?php if (!empty($pastEvents)): ?>
            <div class="card profile-activity-card">
                <div class="profile-activity-header">
                    <i class="fa-solid fa-clock-rotate-left" style="color:var(--text-muted);"></i>
                    <span style="color:var(--text-secondary);">Past events</span>
                    <span class="profile-activity-count" style="background:var(--bg-elevated);color:var(--text-muted);"><?php echo count($pastEvents); ?></span>
                </div>
                <div class="profile-activity-body">
                    <?php foreach (array_slice($pastEvents, 0, 6) as $e): ?>
                        <a href="<?php echo APP_URL; ?>/events/<?php echo (int)$e['id']; ?>"
                           class="event-history-item event-history-item--past">
                            <div class="ehi-info">
                                <div class="ehi-name" style="opacity:.65;"><?php echo htmlspecialchars($e['name']); ?></div>
                                <div class="ehi-date"><?php echo date('d M Y', strtotime($e['event_date'])); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($attendedEvents) && empty($comments)): ?>
            <div class="card">
                <div class="empty-state">
                    <i class="fa-solid fa-ghost"></i>
                    <p>No activity yet.</p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>
</body></html>
