<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$userId         = currentUserId();
$attendedEvents = getUserAttendedEvents($userId);
$recentComments = getUserComments($userId, 10);

$now      = new DateTime('today');
$upcoming = array_values(array_filter($attendedEvents, fn($e) => new DateTime($e['event_date']) >= $now));
$past     = array_values(array_filter($attendedEvents, fn($e) => new DateTime($e['event_date']) < $now));

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper">
    <div class="page-header">
        <h1><i class="fa-solid fa-calendar-check me-2" style="color:var(--primary);"></i>My events</h1>
        <p style="color:var(--text-muted);font-size:.875rem;margin-top:.25rem;">Your attendance history and comment activity.</p>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:2.5rem;">
        <?php foreach ([
            ['value' => count($attendedEvents), 'label' => 'Total registered', 'color' => 'var(--primary)'],
            ['value' => count($upcoming),        'label' => 'Upcoming',         'color' => 'var(--success)'],
            ['value' => count($past),            'label' => 'Attended',         'color' => 'var(--text-secondary)'],
            ['value' => count($recentComments),  'label' => 'Comments',         'color' => 'var(--accent)'],
        ] as $stat): ?>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--r-lg);padding:1.25rem;text-align:center;">
                <div style="font-size:2rem;font-weight:800;color:<?php echo $stat['color']; ?>;font-variant-numeric:tabular-nums;"><?php echo $stat['value']; ?></div>
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;letter-spacing:.05em;text-transform:uppercase;margin-top:.2rem;"><?php echo $stat['label']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">

            <!-- Upcoming -->
            <div class="card" style="border-radius:var(--r-xl);margin-bottom:1.5rem;">
                <div class="card-body" style="padding:1.75rem;">
                    <h2 style="font-size:1.1rem;margin-bottom:1.25rem;">
                        <i class="fa-solid fa-calendar-day me-2" style="color:var(--success);"></i>Upcoming
                    </h2>
                    <?php if (!empty($upcoming)): ?>
                        <div style="display:flex;flex-direction:column;gap:.75rem;">
                            <?php foreach ($upcoming as $e):
                                $diff     = (new DateTime('today'))->diff(new DateTime($e['event_date']));
                                $daysLeft = (int)$diff->days;
                            ?>
                                <div style="display:flex;align-items:center;gap:1rem;padding:.875rem;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);">
                                    <div style="width:48px;height:48px;border-radius:var(--r-md);overflow:hidden;flex-shrink:0;">
                                        <img src="<?php echo htmlspecialchars(eventPhotoUrl($e['photo'])); ?>"
                                             alt="" style="width:100%;height:100%;object-fit:cover;">
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-weight:600;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?php echo htmlspecialchars($e['name']); ?>
                                        </div>
                                        <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;">
                                            <?php echo date('d M Y', strtotime($e['event_date'])); ?>
                                            &bull; <?php echo htmlspecialchars($e['location']); ?>
                                        </div>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-size:.72rem;font-weight:700;color:var(--success);">
                                            <?php echo $daysLeft === 0 ? 'Today' : "in $daysLeft day" . ($daysLeft !== 1 ? 's' : ''); ?>
                                        </div>
                                        <a href="/event_manager/events/<?php echo (int)$e['id']; ?>"
                                           style="font-size:.75rem;font-weight:500;">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:1.5rem;">
                            <i class="fa-regular fa-calendar-xmark" style="font-size:1.5rem;margin-bottom:.5rem;"></i>
                            <p style="font-size:.85rem;">No upcoming events. <a href="/event_manager/events">Browse events</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past -->
            <?php if (!empty($past)): ?>
            <div class="card" style="border-radius:var(--r-xl);">
                <div class="card-body" style="padding:1.75rem;">
                    <h2 style="font-size:1.1rem;margin-bottom:1.25rem;">
                        <i class="fa-solid fa-clock-rotate-left me-2" style="color:var(--text-muted);"></i>Past events
                    </h2>
                    <div style="display:flex;flex-direction:column;gap:.75rem;">
                        <?php foreach ($past as $e): ?>
                            <div style="display:flex;align-items:center;gap:1rem;padding:.875rem;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);opacity:.65;">
                                <div style="width:48px;height:48px;border-radius:var(--r-md);overflow:hidden;flex-shrink:0;filter:grayscale(60%);">
                                    <img src="<?php echo htmlspecialchars(eventPhotoUrl($e['photo'])); ?>"
                                         alt="" style="width:100%;height:100%;object-fit:cover;">
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:600;font-size:.9rem;color:var(--text-secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        <?php echo htmlspecialchars($e['name']); ?>
                                    </div>
                                    <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;">
                                        <?php echo date('d M Y', strtotime($e['event_date'])); ?>
                                        &bull; <?php echo htmlspecialchars($e['category_name']); ?>
                                    </div>
                                </div>
                                <span class="badge-tag" style="background:var(--bg-elevated);color:var(--text-muted);border:1px solid var(--border);">Done</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Comment history -->
        <div class="col-lg-5">
            <div class="card" style="border-radius:var(--r-xl);position:sticky;top:5.5rem;">
                <div class="card-body" style="padding:1.75rem;">
                    <h2 style="font-size:1.1rem;margin-bottom:1.25rem;">
                        <i class="fa-regular fa-comments me-2" style="color:var(--primary);"></i>Comment history
                    </h2>
                    <?php if (!empty($recentComments)): ?>
                        <div style="display:flex;flex-direction:column;gap:.75rem;max-height:500px;overflow-y:auto;">
                            <?php foreach ($recentComments as $c): ?>
                                <div style="padding:.875rem;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);">
                                    <div style="display:flex;justify-content:space-between;gap:.5rem;margin-bottom:.4rem;">
                                        <a href="/event_manager/events/<?php echo (int)$c['event_id']; ?>"
                                           style="font-size:.78rem;font-weight:600;color:var(--primary);">
                                            <i class="fa-solid fa-ticket fa-xs me-1"></i><?php echo htmlspecialchars($c['event_name']); ?>
                                        </a>
                                        <span style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;">
                                            <?php echo date('d M', strtotime($c['comment_date'])); ?>
                                        </span>
                                    </div>
                                    <p style="font-size:.85rem;margin:0;color:var(--text-primary);line-height:1.45;">
                                        <?php echo htmlspecialchars($c['comment']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:1.5rem;">
                            <i class="fa-regular fa-comment-dots" style="font-size:1.5rem;margin-bottom:.5rem;"></i>
                            <p style="font-size:.85rem;">No comments yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>
</body></html>
