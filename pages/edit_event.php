<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /event_manager/events');
    exit;
}

$eventId    = (int)$_GET['id'];
$event      = getEvent($eventId);
$categories = getCategories();
$error      = '';
$success    = false;

if (!$event) {
    header('Location: /event_manager/events');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = sanitize($_POST['name'] ?? '');
    $location    = sanitize($_POST['location'] ?? '');
    $event_date  = sanitize($_POST['event_date'] ?? '');
    $event_time  = sanitize($_POST['event_time'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $photoPath   = $event['photo'];

    if (isset($_FILES['event_photo']) && $_FILES['event_photo']['error'] === UPLOAD_ERR_OK) {
        $ext  = strtolower(pathinfo($_FILES['event_photo']['name'], PATHINFO_EXTENSION));
        $name_ = "event_{$eventId}_photo.{$ext}";
        $dest  = __DIR__ . '/../uploads/events/' . $name_;

        if (getimagesize($_FILES['event_photo']['tmp_name']) !== false && move_uploaded_file($_FILES['event_photo']['tmp_name'], $dest)) {
            $photoPath = "uploads/events/$name_";
        } else {
            $error = 'Photo upload failed.';
        }
    }

    if (!$error) {
        $stmt = $conn->prepare(
            "UPDATE events SET name=?, location=?, event_date=?, event_time=?, price=?, photo=?, category_id=? WHERE id=?"
        );
        $stmt->bind_param('ssssdsii', $name, $location, $event_date, $event_time, $price, $photoPath, $category_id, $eventId);
        if ($stmt->execute()) {
            $success = true;
            $event   = getEvent($eventId);
        } else {
            $error = 'Update failed: ' . $conn->error;
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper" style="max-width:680px;">
    <div class="page-header">
        <a href="/event_manager/events/?id=<?php echo $eventId; ?>" class="btn btn-ghost btn-sm mb-3" style="padding-left:0;">
            <i class="fa-solid fa-arrow-left"></i> Back to event
        </a>
        <h1><i class="fa-solid fa-pen-to-square me-2" style="color:var(--primary);"></i>Edit event</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success mb-4"><i class="fa-solid fa-circle-check me-2"></i>Event updated.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card" style="border-radius:var(--r-xl);">
        <div class="card-body" style="padding:2rem;">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="name" class="form-label">Event name</label>
                    <input type="text" id="name" name="name" required class="form-control"
                           value="<?php echo htmlspecialchars($event['name']); ?>">
                </div>
                <div class="mb-4">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" required class="form-control"
                           value="<?php echo htmlspecialchars($event['location']); ?>">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="event_date" class="form-label">Date</label>
                        <input type="date" id="event_date" name="event_date" required class="form-control"
                               value="<?php echo htmlspecialchars($event['event_date']); ?>">
                    </div>
                    <div class="col-sm-6">
                        <label for="event_time" class="form-label">Time</label>
                        <input type="time" id="event_time" name="event_time" required class="form-control"
                               value="<?php echo htmlspecialchars($event['event_time']); ?>">
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="price" class="form-label">Price (Lei)</label>
                        <input type="number" id="price" step="0.01" min="0" name="price" required class="form-control"
                               value="<?php echo htmlspecialchars($event['price']); ?>">
                    </div>
                    <div class="col-sm-6">
                        <label for="category_id" class="form-label">Category</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <?php foreach ($categories as $id => $catName): ?>
                                <option value="<?php echo $id; ?>" <?php echo $id == $event['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($catName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Event photo</label>
                    <?php if (!empty($event['photo'])): ?>
                        <div style="margin-bottom:.75rem;border-radius:var(--r-md);overflow:hidden;aspect-ratio:16/9;max-width:280px;">
                            <img src="/event_manager/<?php echo htmlspecialchars($event['photo']); ?>"
                                 alt="Current photo" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="event_photo" name="event_photo" class="form-control" accept="image/*">
                    <p style="font-size:.78rem;color:var(--text-muted);margin-top:.35rem;">Leave blank to keep current photo.</p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
                    <i class="fa-solid fa-floppy-disk"></i> Save changes
                </button>
            </form>
        </div>
    </div>
</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>
</body></html>
