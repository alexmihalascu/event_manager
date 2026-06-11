<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$userId     = currentUserId();
$success    = false;
$error      = '';

$profile = getUserProfile($userId);
$currentBio = $profile['bio'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $s = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $s->bind_param('si', $hash, $userId);
            $s->execute();
            $s->close();
            $success = true;
        }
    }

    if (!$error && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext  = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed, true)) {
            $error = 'Invalid image type. Use JPG, PNG, GIF or WebP.';
        } else {
            $name = 'u' . $userId . '_avatar.' . $ext;
            $dest = UPLOAD_AVATARS . $name;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $s = $conn->prepare("UPDATE profiles SET avatar = ? WHERE user_id = ?");
                $s->bind_param('si', $name, $userId);
                $s->execute();
                $s->close();
                $_SESSION['avatar'] = $name;
                $success = true;
            } else {
                $error = 'Avatar upload failed. Check server permissions.';
            }
        }
    }

    if (!$error && isset($_POST['bio'])) {
        $bio = trim(substr($_POST['bio'], 0, 300));
        $s = $conn->prepare("UPDATE profiles SET bio = ? WHERE user_id = ?");
        $s->bind_param('si', $bio, $userId);
        $s->execute();
        $s->close();
        $currentBio = $bio;
        $success = true;
    }
}

$myComments     = getUserComments($userId, 10);
$myEvents       = getUserAttendedEvents($userId);
$now            = new DateTimeImmutable();
$upcomingEvents = array_filter($myEvents, fn($e) => new DateTimeImmutable($e['event_date']) > $now);
$pastEvents     = array_filter($myEvents, fn($e) => new DateTimeImmutable($e['event_date']) <= $now);
$avatarSrc      = avatarUrl(currentAvatar());

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper" style="max-width:1100px;">

    <!-- Profile Hero Card -->
    <div class="profile-hero-card">
        <div class="profile-cover profile-cover--own">
            <!-- Animated mesh background -->
            <div class="profile-cover-mesh"></div>
            <div class="profile-cover-actions">
                <label for="avatar-upload" class="btn btn-sm profile-avatar-change-btn" title="Change avatar">
                    <i class="fa-solid fa-camera"></i> Change photo
                </label>
            </div>
        </div>
        <div class="profile-hero-body">
            <div class="profile-avatar-wrap profile-avatar-wrap--own">
                <img src="<?php echo htmlspecialchars($avatarSrc); ?>"
                     alt="Your avatar"
                     id="avatar-preview">
                <div class="profile-avatar-ring"></div>
            </div>

            <div class="profile-hero-info">
                <div class="profile-hero-name-row">
                    <h1 class="profile-username"><?php echo currentUsername(); ?></h1>
                    <?php if (isAdmin()): ?>
                        <span class="badge-tag badge-primary">
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($currentBio)): ?>
                    <p class="profile-bio-preview"><?php echo nl2br(htmlspecialchars($currentBio)); ?></p>
                <?php else: ?>
                    <p class="profile-bio-preview profile-bio-placeholder">Add a bio to let people know who you are…</p>
                <?php endif; ?>

                <div class="profile-stats-row">
                    <div class="profile-stat">
                        <span class="profile-stat-num"><?php echo count($myEvents); ?></span>
                        <span class="profile-stat-label">Events</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-num"><?php echo count($upcomingEvents); ?></span>
                        <span class="profile-stat-label">Upcoming</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-num"><?php echo count($myComments); ?></span>
                        <span class="profile-stat-label">Comments</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success mb-4"><i class="fa-solid fa-circle-check me-2"></i>Profile updated successfully.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="profile-two-col">

        <!-- LEFT: Edit form -->
        <div class="profile-col-edit">
            <form method="post" enctype="multipart/form-data" id="profile-form">
                <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display:none;">

                <!-- Bio -->
                <div class="settings-section">
                    <div class="settings-section-header">
                        <i class="fa-regular fa-id-card"></i>
                        <div>
                            <div class="settings-section-title">About you</div>
                            <div class="settings-section-sub">Shown on your public profile.</div>
                        </div>
                    </div>
                    <div class="settings-section-body">
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea id="bio" name="bio" class="form-control" rows="4"
                                      maxlength="300"
                                      placeholder="Write a short bio…"><?php echo htmlspecialchars($currentBio); ?></textarea>
                            <div class="form-hint">
                                <span id="bio-count"><?php echo strlen($currentBio); ?></span>/300 characters
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Username</label>
                            <input type="text" value="<?php echo currentUsername(); ?>" disabled class="form-control">
                            <div class="form-hint">Username cannot be changed.</div>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="settings-section">
                    <div class="settings-section-header">
                        <i class="fa-solid fa-lock"></i>
                        <div>
                            <div class="settings-section-title">Security</div>
                            <div class="settings-section-sub">Change your password.</div>
                        </div>
                    </div>
                    <div class="settings-section-body">
                        <div class="mb-0">
                            <label for="password" class="form-label">New password</label>
                            <input type="password" id="password" name="password" class="form-control"
                                   autocomplete="new-password" placeholder="Leave blank to keep current">
                            <div class="form-hint">Must be at least 6 characters.</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem;padding:.8rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save changes
                </button>
            </form>
        </div>

        <!-- RIGHT: Activity -->
        <div class="profile-col-activity">

            <!-- Comment history -->
            <div class="card profile-activity-card">
                <div class="profile-activity-header">
                    <i class="fa-regular fa-comments" style="color:var(--primary);"></i>
                    <span>Comment history</span>
                    <span class="profile-activity-count"><?php echo count($myComments); ?></span>
                </div>
                <div class="profile-activity-body">
                    <?php if (!empty($myComments)): ?>
                        <?php foreach ($myComments as $c): ?>
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

            <!-- Upcoming events -->
            <?php if (!empty($upcomingEvents)): ?>
            <div class="card profile-activity-card">
                <div class="profile-activity-header">
                    <i class="fa-regular fa-calendar" style="color:var(--success);"></i>
                    <span>Upcoming events</span>
                    <span class="profile-activity-count" style="background:var(--success-dim);color:var(--success);"><?php echo count($upcomingEvents); ?></span>
                </div>
                <div class="profile-activity-body">
                    <?php foreach ($upcomingEvents as $e):
                        $days = (int)$now->diff(new DateTimeImmutable($e['event_date']))->days;
                    ?>
                        <a href="<?php echo APP_URL; ?>/events/<?php echo (int)$e['id']; ?>"
                           class="event-history-item">
                            <div class="ehi-countdown"><?php echo $days; ?><span>days</span></div>
                            <div class="ehi-info">
                                <div class="ehi-name"><?php echo htmlspecialchars($e['name']); ?></div>
                                <div class="ehi-date">
                                    <i class="fa-solid fa-calendar-day fa-xs"></i>
                                    <?php echo date('d M Y', strtotime($e['event_date'])); ?>
                                    <i class="fa-solid fa-location-dot fa-xs ms-2"></i>
                                    <?php echo htmlspecialchars($e['location']); ?>
                                </div>
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
                    <?php foreach (array_slice($pastEvents, 0, 5) as $e): ?>
                        <a href="<?php echo APP_URL; ?>/events/<?php echo (int)$e['id']; ?>"
                           class="event-history-item event-history-item--past">
                            <div class="ehi-info">
                                <div class="ehi-name" style="opacity:.65;"><?php echo htmlspecialchars($e['name']); ?></div>
                                <div class="ehi-date">
                                    <i class="fa-solid fa-calendar-day fa-xs"></i>
                                    <?php echo date('d M Y', strtotime($e['event_date'])); ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>

<script>
// Live avatar preview
document.getElementById('avatar-upload').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatar-preview').src = e.target.result;
    };
    reader.readAsDataURL(file);
    document.getElementById('profile-form').submit();
});

// Bio character counter
const bioArea  = document.getElementById('bio');
const bioCount = document.getElementById('bio-count');
if (bioArea && bioCount) {
    bioArea.addEventListener('input', () => {
        bioCount.textContent = bioArea.value.length;
    });
}
</script>
</body></html>
