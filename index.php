<?php
require_once __DIR__ . '/includes/bootstrap.php';

/* ── Stats for dashboard ─────────────────────────────────── */
if (isLoggedIn()) {
    $promotedEvents = getPromotedEvents(3);

    $r = $conn->query("SELECT COUNT(*) FROM events");
    $totalEvents = (int)$r->fetch_row()[0];

    $r = $conn->query("SELECT COUNT(*) FROM users");
    $totalUsers = (int)$r->fetch_row()[0];

    $r = $conn->query("SELECT COUNT(*) FROM event_attendance WHERE user_id = " . currentUserId());
    $myAttendCount = (int)$r->fetch_row()[0];

    $r = $conn->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()");
    $upcomingCount = (int)$r->fetch_row()[0];

    // Latest 3 events (beyond top events)
    $latestEvents = getEvents('events.id DESC');
    $latestEvents = array_slice($latestEvents, 0, 6);
}

require_once __DIR__ . '/navbar.php';
?>

<?php if (!isLoggedIn()): ?>
<!-- ══════════════════════════════════════════════════════════
     LANDING PAGE  (guest)
     ══════════════════════════════════════════════════════════ -->
<main id="main-content">

    <!-- Hero -->
    <section class="lp-hero">
        <div class="lp-hero-bg">
            <div class="lp-orb lp-orb-1"></div>
            <div class="lp-orb lp-orb-2"></div>
            <div class="lp-orb lp-orb-3"></div>
            <div class="lp-grid-lines"></div>
        </div>
        <div class="page-wrapper lp-hero-inner">
            <div class="lp-hero-content">
                <div class="lp-hero-pill">
                    <span class="lp-hero-pill-dot"></span>
                    Event discovery platform
                </div>
                <h1 class="lp-hero-title">
                    Find events<br><span class="lp-gradient-text">you'll love</span>
                </h1>
                <p class="lp-hero-sub">
                    Discover, join, and organize events that bring people together.
                    Browse what's happening, register in one click, and never miss a moment.
                </p>
                <div class="lp-hero-cta">
                    <a href="<?php echo APP_URL; ?>/register" class="btn btn-primary btn-lg lp-btn-glow">
                        <i class="fa-solid fa-rocket"></i> Get started free
                    </a>
                    <a href="<?php echo APP_URL; ?>/login" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-right-to-bracket"></i> Sign in
                    </a>
                </div>
            </div>

            <!-- Floating event card mockup -->
            <div class="lp-hero-visual" aria-hidden="true">
                <div class="lp-mockup-card">
                    <div class="lp-mockup-img"></div>
                    <div class="lp-mockup-body">
                        <div class="lp-mockup-tag">Music · Live</div>
                        <div class="lp-mockup-title">Summer Night Fest</div>
                        <div class="lp-mockup-meta"><i class="fa-solid fa-location-dot"></i> Bucharest Arena</div>
                        <div class="lp-mockup-meta"><i class="fa-solid fa-calendar"></i> 18 Jul 2026</div>
                        <div class="lp-mockup-footer">
                            <span class="lp-mockup-price">Free</span>
                            <span class="lp-mockup-btn">View →</span>
                        </div>
                    </div>
                    <div class="lp-mockup-badge"><i class="fa-solid fa-star"></i> Top Event</div>
                </div>
                <div class="lp-mockup-pill lp-pill-1"><i class="fa-solid fa-users"></i> 248 attending</div>
                <div class="lp-mockup-pill lp-pill-2"><i class="fa-solid fa-check-circle"></i> Registered!</div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="lp-features">
        <div class="page-wrapper">
            <div class="section-header">
                <span class="label">Why Events Manager</span>
                <h2>Everything you need,<br>nothing you don't</h2>
            </div>
            <div class="lp-features-grid">
                <?php foreach ([
                    ['fa-calendar-plus',   '#6382ff', 'Create in seconds',   'Add photos, location, date and price. Your event is live instantly.'],
                    ['fa-users',           '#a78bfa', 'Community driven',    'Comment, attend and connect with other participants.'],
                    ['fa-star',            '#f59e0b', 'Curated top picks',   'Admins surface the best events straight to the homepage.'],
                    ['fa-magnifying-glass','#22c55e', 'Powerful search',     'Filter by category, name or location and sort any way you like.'],
                ] as [$icon, $color, $title, $text]): ?>
                    <div class="lp-feature-card">
                        <div class="lp-feature-icon" style="background:<?php echo $color; ?>1a;color:<?php echo $color; ?>;">
                            <i class="fa-solid <?php echo $icon; ?>"></i>
                        </div>
                        <h3><?php echo $title; ?></h3>
                        <p><?php echo $text; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA strip -->
    <section class="lp-cta-strip">
        <div class="page-wrapper lp-cta-inner">
            <div>
                <h2>Ready to explore?</h2>
                <p>Join the community and start discovering events today.</p>
            </div>
            <a href="<?php echo APP_URL; ?>/register" class="btn btn-primary btn-lg lp-btn-glow">
                <i class="fa-solid fa-user-plus"></i> Create free account
            </a>
        </div>
    </section>

</main>

<footer class="site-footer">
    <div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div>
</footer>

<?php else: ?>
<!-- ══════════════════════════════════════════════════════════
     DASHBOARD  (logged-in)
     ══════════════════════════════════════════════════════════ -->
<main id="main-content">

    <!-- Dashboard hero -->
    <section class="dash-hero">
        <div class="dash-hero-bg">
            <div class="lp-orb lp-orb-1" style="opacity:.35;"></div>
            <div class="lp-orb lp-orb-2" style="opacity:.2;"></div>
        </div>
        <div class="page-wrapper dash-hero-inner">
            <div class="dash-hero-text">
                <div class="label mb-2">
                    <?php
                    $hour = (int)date('G');
                    echo $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                    ?>, <?php echo currentUsername(); ?>
                </div>
                <h1>Your dashboard</h1>
                <p>Here's what's happening on the platform.</p>
            </div>
            <div class="dash-hero-actions">
                <a href="<?php echo APP_URL; ?>/events" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-ticket"></i> Browse events
                </a>
                <a href="<?php echo APP_URL; ?>/my-events" class="btn btn-secondary btn-lg">
                    <i class="fa-solid fa-calendar-check"></i> My events
                </a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo APP_URL; ?>/add-event" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-circle-plus"></i> Add event
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Stat cards -->
    <div class="page-wrapper" style="margin-top:2rem;position:relative;z-index:2;">
        <div class="dash-stats-grid">
            <div class="dash-stat-card">
                <div class="dash-stat-icon" style="background:var(--primary-dim);color:var(--primary);">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="dash-stat-body">
                    <div class="dash-stat-num"><?php echo $totalEvents; ?></div>
                    <div class="dash-stat-label">Total events</div>
                </div>
            </div>
            <div class="dash-stat-card">
                <div class="dash-stat-icon" style="background:rgba(167,139,250,.12);color:var(--accent);">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div class="dash-stat-body">
                    <div class="dash-stat-num"><?php echo $upcomingCount; ?></div>
                    <div class="dash-stat-label">Upcoming</div>
                </div>
            </div>
            <div class="dash-stat-card">
                <div class="dash-stat-icon" style="background:var(--success-dim);color:var(--success);">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="dash-stat-body">
                    <div class="dash-stat-num"><?php echo $myAttendCount; ?></div>
                    <div class="dash-stat-label">You're attending</div>
                </div>
            </div>
            <div class="dash-stat-card">
                <div class="dash-stat-icon" style="background:var(--warning-dim);color:var(--warning);">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="dash-stat-body">
                    <div class="dash-stat-num"><?php echo $totalUsers; ?></div>
                    <div class="dash-stat-label">Members</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Events -->
    <?php if (!empty($promotedEvents)): ?>
    <section style="padding:3rem 0 0;">
        <div class="page-wrapper">
            <div class="dash-section-header">
                <div>
                    <span class="label"><i class="fa-solid fa-star me-1" style="color:var(--warning);"></i>Promoted</span>
                    <h2 style="font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin-top:.25rem;">Top events</h2>
                </div>
                <a href="<?php echo APP_URL; ?>/events" class="btn btn-secondary btn-sm">
                    View all <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($promotedEvents as $event): ?>
                    <div class="col">
                        <div class="event-card-grid">
                            <div class="card-img-wrapper">
                                <img src="<?php echo htmlspecialchars(eventPhotoUrl($event['photo'] ?? '')); ?>"
                                     alt="<?php echo htmlspecialchars($event['name']); ?>"
                                     loading="lazy">
                                <span class="card-img-overlay-tag">
                                    <i class="fa-solid fa-star" style="color:var(--warning);"></i> Top Event
                                </span>
                            </div>
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                                <div class="card-meta">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <div class="card-meta">
                                    <i class="fa-solid fa-calendar"></i>
                                    <?php echo date('d M Y', strtotime($event['event_date'])); ?>
                                </div>
                                <div class="card-price">
                                    <?php echo $event['price'] == 0 ? 'Free' : number_format($event['price']) . ' Lei'; ?>
                                </div>
                            </div>
                            <div class="card-footer-area">
                                <a href="<?php echo APP_URL; ?>/events/<?php echo (int)$event['id']; ?>"
                                   class="btn btn-primary btn-block">
                                    View details <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Latest Events -->
    <section style="padding:3rem 0 5rem;">
        <div class="page-wrapper">
            <div class="dash-section-header">
                <div>
                    <span class="label"><i class="fa-solid fa-clock-rotate-left me-1"></i>Recent</span>
                    <h2 style="font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin-top:.25rem;">Latest events</h2>
                </div>
                <a href="<?php echo APP_URL; ?>/events" class="btn btn-secondary btn-sm">
                    Browse all <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <?php if (!empty($latestEvents)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($latestEvents as $event): ?>
                    <div class="col">
                        <div class="event-card-grid">
                            <div class="card-img-wrapper">
                                <img src="<?php echo htmlspecialchars(eventPhotoUrl($event['photo'] ?? '')); ?>"
                                     alt="<?php echo htmlspecialchars($event['name']); ?>"
                                     loading="lazy">
                                <?php if (!empty($event['category_name'])): ?>
                                    <span class="card-img-overlay-tag"><?php echo htmlspecialchars($event['category_name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                                <div class="card-meta">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <div class="card-meta">
                                    <i class="fa-solid fa-calendar"></i>
                                    <?php echo date('d M Y', strtotime($event['event_date'])); ?>
                                </div>
                                <div class="card-price">
                                    <?php echo $event['price'] == 0 ? 'Free' : number_format($event['price']) . ' Lei'; ?>
                                </div>
                            </div>
                            <div class="card-footer-area">
                                <a href="<?php echo APP_URL; ?>/events/<?php echo (int)$event['id']; ?>"
                                   class="btn btn-primary btn-block">
                                    View details <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <p>No events yet. <a href="<?php echo APP_URL; ?>/add-event">Add the first one.</a></p>
                </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<footer class="site-footer">
    <div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div>
</footer>

<?php endif; ?>
</body></html>
