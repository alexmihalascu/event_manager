<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$sortMap = [
    'newest'     => 'events.id DESC',
    'oldest'     => 'events.id ASC',
    'name_asc'   => 'events.name ASC',
    'name_desc'  => 'events.name DESC',
    'price_asc'  => 'events.price ASC',
    'price_desc' => 'events.price DESC',
];
$sortLabels = [
    'newest'     => 'Newest first',
    'oldest'     => 'Oldest first',
    'name_asc'   => 'Name A–Z',
    'name_desc'  => 'Name Z–A',
    'price_asc'  => 'Price: low to high',
    'price_desc' => 'Price: high to low',
];

if (!isset($_SESSION['event_sort'])) {
    $_SESSION['event_sort'] = 'newest';
}
$sort = $_GET['sort'] ?? $_SESSION['event_sort'];
if (!array_key_exists($sort, $sortMap)) $sort = 'newest';
$_SESSION['event_sort'] = $sort;

$search     = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['cat'] ?? 0);
$orderBy    = $sortMap[$sort];

$events     = ($search !== '' || $categoryId > 0)
    ? searchEvents($search, $categoryId, $orderBy)
    : getEvents($orderBy);
$categories = getCategories();

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper">

    <div class="page-header">
        <h1>Events</h1>
        <p style="color:var(--text-muted);font-size:.9rem;margin-top:.25rem;">
            <?php echo count($events); ?> event<?php echo count($events) !== 1 ? 's' : ''; ?> found.
        </p>
    </div>

    <form method="get" id="filterForm">
        <div class="sort-bar">
            <div style="position:relative;flex:1;min-width:200px;max-width:360px;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem;pointer-events:none;"></i>
                <input type="text" name="q" id="searchInput"
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="form-control" placeholder="Search by name or location…"
                       style="padding-left:2.4rem;" autocomplete="off">
            </div>
            <select name="cat" class="form-select" style="max-width:180px;" id="catSelect">
                <option value="0">All categories</option>
                <?php foreach ($categories as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $id == $categoryId ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="form-select" style="max-width:200px;" id="sortSelect">
                <?php foreach ($sortLabels as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $key === $sort ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Apply
            </button>
            <?php if ($search !== '' || $categoryId > 0 || $sort !== 'newest'): ?>
                <a href="events_list.php" class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-xmark"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (!empty($events)): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($events as $row): ?>
                <div class="col">
                    <div class="event-card-grid">
                        <div class="card-img-wrapper">
                            <img src="<?php echo htmlspecialchars(eventPhotoUrl($row['photo'])); ?>"
                                 alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <span class="card-img-overlay-tag">
                                <?php echo htmlspecialchars($row['category_name']); ?>
                            </span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <div class="card-meta">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($row['location']); ?>
                            </div>
                            <div class="card-meta">
                                <i class="fa-solid fa-calendar"></i>
                                <?php echo date('d M Y', strtotime($row['event_date'])); ?>
                                &bull;
                                <i class="fa-solid fa-clock"></i>
                                <?php echo date('H:i', strtotime($row['event_time'])); ?>
                            </div>
                            <div class="card-price">
                                <?php echo $row['price'] == 0 ? 'Free' : number_format($row['price']) . ' Lei'; ?>
                            </div>
                        </div>
                        <div class="card-footer-area">
                            <a href="/event_manager/events/<?php echo (int)$row['id']; ?>"
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
            <p>No events match your search.</p>
        </div>
    <?php endif; ?>
</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>

<script>
document.getElementById('catSelect').addEventListener('change', () => document.getElementById('filterForm').submit());
document.getElementById('sortSelect').addEventListener('change', () => document.getElementById('filterForm').submit());
let t;
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(t);
    t = setTimeout(() => document.getElementById('filterForm').submit(), 500);
});
</script>
</body></html>
