<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$categories = getCategories();
$error      = '';
$success    = false;
$newId      = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = sanitize($_POST['name'] ?? '');
    $location    = sanitize($_POST['location'] ?? '');
    $event_date  = sanitize($_POST['event_date'] ?? '');
    $event_time  = sanitize($_POST['event_time'] ?? '');
    $price       = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $location === '' || $event_date === '') {
        $error = 'Name, location and date are required.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO events (name, location, event_date, event_time, price, category_id) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssdi', $name, $location, $event_date, $event_time, $price, $category_id);

        if ($stmt->execute()) {
            $newId   = $conn->insert_id;
            $success = true;
            $stmt->close();

            if (isset($_FILES['event_photo']) && $_FILES['event_photo']['error'] === UPLOAD_ERR_OK) {
                $ext  = strtolower(pathinfo($_FILES['event_photo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed, true) && getimagesize($_FILES['event_photo']['tmp_name']) !== false) {
                    $newName = "event_{$newId}_photo.{$ext}";
                    $dest    = UPLOAD_EVENTS . $newName;
                    if (move_uploaded_file($_FILES['event_photo']['tmp_name'], $dest)) {
                        $path = "uploads/events/$newName";
                        $s    = $conn->prepare("UPDATE events SET photo = ? WHERE id = ?");
                        $s->bind_param('si', $path, $newId);
                        $s->execute();
                        $s->close();
                    } else {
                        $error = 'Event saved, but photo upload failed (check permissions).';
                    }
                }
            }
        } else {
            $error = 'Failed to save event.';
            $stmt->close();
        }
    }
}

require_once __DIR__ . '/../navbar.php';
?>

<main id="main-content" class="page-content">
<div class="page-wrapper ae-layout">

    <!-- Left: form -->
    <div class="ae-form-col">
        <div class="ae-page-header">
            <a href="<?php echo APP_URL; ?>/events" class="btn btn-ghost btn-sm ae-back-btn">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <div>
                <div class="label mb-1"><i class="fa-solid fa-circle-plus me-1"></i>Admin</div>
                <h1>Create event</h1>
                <p style="color:var(--text-muted);font-size:.875rem;margin-top:.3rem;">
                    Fill in the details below. Photo is optional.
                </p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success mb-4">
                <i class="fa-solid fa-circle-check me-2"></i>
                Event created!
                <a href="<?php echo APP_URL; ?>/events/<?php echo $newId; ?>" style="color:inherit;font-weight:700;text-decoration:underline;margin-left:.4rem;">View it →</a>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" id="ae-form" novalidate>

            <!-- Photo drop zone -->
            <div class="ae-photo-drop" id="photo-drop" onclick="document.getElementById('event_photo').click()">
                <img id="photo-preview" src="" alt="" style="display:none;">
                <div class="ae-photo-drop-placeholder" id="photo-placeholder">
                    <div class="ae-photo-drop-icon">
                        <i class="fa-solid fa-image-portrait"></i>
                    </div>
                    <div class="ae-photo-drop-label">Click or drag to upload cover photo</div>
                    <div class="ae-photo-drop-hint">JPG, PNG, WebP · Recommended 16:9</div>
                </div>
                <input type="file" id="event_photo" name="event_photo" accept="image/*" style="display:none;">
            </div>

            <!-- Sections -->
            <div class="ae-sections">

                <!-- Basic info -->
                <div class="ae-section">
                    <div class="ae-section-label">
                        <i class="fa-solid fa-pen-to-square"></i> Basic info
                    </div>
                    <div class="ae-section-body">
                        <div class="ae-field">
                            <label for="name" class="form-label">Event name <span class="ae-required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control ae-input-lg"
                                   placeholder="e.g. Tech Meetup Bucharest" required
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                        <div class="ae-field">
                            <label for="location" class="form-label">Location <span class="ae-required">*</span></label>
                            <div class="ae-input-icon-wrap">
                                <i class="fa-solid fa-location-dot ae-input-icon"></i>
                                <input type="text" id="location" name="location" class="form-control ae-input-icon-pad"
                                       placeholder="e.g. Cluj-Napoca, Arena Hall" required
                                       value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="ae-section">
                    <div class="ae-section-label">
                        <i class="fa-solid fa-calendar-days"></i> Date &amp; time
                    </div>
                    <div class="ae-section-body">
                        <div class="ae-two-col">
                            <div class="ae-field">
                                <label for="event_date" class="form-label">Date <span class="ae-required">*</span></label>
                                <div class="ae-input-icon-wrap">
                                    <i class="fa-solid fa-calendar ae-input-icon"></i>
                                    <input type="date" id="event_date" name="event_date" class="form-control ae-input-icon-pad" required
                                           value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="ae-field">
                                <label for="event_time" class="form-label">Time</label>
                                <div class="ae-input-icon-wrap">
                                    <i class="fa-solid fa-clock ae-input-icon"></i>
                                    <input type="time" id="event_time" name="event_time" class="form-control ae-input-icon-pad"
                                           value="<?php echo htmlspecialchars($_POST['event_time'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category & Price -->
                <div class="ae-section">
                    <div class="ae-section-label">
                        <i class="fa-solid fa-tags"></i> Category &amp; pricing
                    </div>
                    <div class="ae-section-body">
                        <div class="ae-two-col">
                            <div class="ae-field">
                                <label for="category_id" class="form-label">Category</label>
                                <select id="category_id" name="category_id" class="form-select">
                                    <?php foreach ($categories as $cid => $cname): ?>
                                        <option value="<?php echo $cid; ?>"
                                            <?php echo ((int)($_POST['category_id'] ?? 0) === $cid) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cname); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="ae-field">
                                <label for="price" class="form-label">Price (Lei)</label>
                                <div class="ae-input-icon-wrap">
                                    <i class="fa-solid fa-tag ae-input-icon"></i>
                                    <input type="number" id="price" name="price" step="0.01" min="0"
                                           class="form-control ae-input-icon-pad"
                                           placeholder="0 = Free"
                                           value="<?php echo htmlspecialchars($_POST['price'] ?? '0'); ?>">
                                </div>
                                <div id="price-hint" class="form-hint ae-price-hint">Free event</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /ae-sections -->

            <button type="submit" class="btn btn-primary ae-submit-btn">
                <i class="fa-solid fa-rocket"></i> Publish event
            </button>

        </form>
    </div>

    <!-- Right: live preview -->
    <div class="ae-preview-col">
        <div class="ae-preview-sticky">
            <div class="ae-preview-label">
                <i class="fa-solid fa-eye"></i> Live preview
            </div>
            <div class="event-card-grid ae-preview-card">
                <div class="card-img-wrapper">
                    <img id="prev-photo" src="<?php echo APP_URL; ?>/uploads/events/default_event.png" alt="preview">
                    <span class="card-img-overlay-tag" id="prev-category">
                        <?php echo htmlspecialchars(reset($categories) ?: 'Category'); ?>
                    </span>
                </div>
                <div class="card-content">
                    <h3 class="card-title" id="prev-name">Event name</h3>
                    <div class="card-meta">
                        <i class="fa-solid fa-location-dot"></i>
                        <span id="prev-location">Location</span>
                    </div>
                    <div class="card-meta">
                        <i class="fa-solid fa-calendar"></i>
                        <span id="prev-date">Date</span>
                    </div>
                    <div class="card-price" id="prev-price">Free</div>
                </div>
                <div class="card-footer-area">
                    <div class="btn btn-primary btn-block" style="pointer-events:none;opacity:.7;">
                        View details <i class="fa-solid fa-arrow-right ms-1"></i>
                    </div>
                </div>
            </div>
            <p class="ae-preview-note">Preview updates as you type.</p>
        </div>
    </div>

</div>
</main>

<footer class="site-footer"><div class="page-wrapper">&copy; <?php echo date('Y'); ?> Events Manager.</div></footer>

<script>
(function () {
    /* ── Photo drop zone ────────────────────────── */
    const input       = document.getElementById('event_photo');
    const preview     = document.getElementById('photo-preview');
    const placeholder = document.getElementById('photo-placeholder');
    const drop        = document.getElementById('photo-drop');
    const prevPhoto   = document.getElementById('prev-photo');

    function applyPhoto(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        prevPhoto.src = url;
    }

    input.addEventListener('change', () => applyPhoto(input.files[0]));

    drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('ae-drop-active'); });
    drop.addEventListener('dragleave', () => drop.classList.remove('ae-drop-active'));
    drop.addEventListener('drop', e => {
        e.preventDefault();
        drop.classList.remove('ae-drop-active');
        const file = e.dataTransfer.files[0];
        if (file) { input.files = e.dataTransfer.files; applyPhoto(file); }
    });

    /* ── Live preview sync ──────────────────────── */
    const nameInput     = document.getElementById('name');
    const locInput      = document.getElementById('location');
    const dateInput     = document.getElementById('event_date');
    const priceInput    = document.getElementById('price');
    const catSelect     = document.getElementById('category_id');
    const priceHint     = document.getElementById('price-hint');

    const prevName      = document.getElementById('prev-name');
    const prevLoc       = document.getElementById('prev-location');
    const prevDate      = document.getElementById('prev-date');
    const prevPrice     = document.getElementById('prev-price');
    const prevCategory  = document.getElementById('prev-category');

    function fmtDate(val) {
        if (!val) return 'Date';
        const d = new Date(val + 'T00:00:00');
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function sync() {
        prevName.textContent     = nameInput.value.trim() || 'Event name';
        prevLoc.textContent      = locInput.value.trim()  || 'Location';
        prevDate.textContent     = fmtDate(dateInput.value);
        const p = parseFloat(priceInput.value);
        if (!priceInput.value || p === 0) {
            prevPrice.textContent = 'Free';
            priceHint.textContent = 'Free event';
        } else {
            prevPrice.textContent = p.toLocaleString('ro-RO') + ' Lei';
            priceHint.textContent = p.toLocaleString('ro-RO') + ' Lei';
        }
        prevCategory.textContent = catSelect.options[catSelect.selectedIndex]?.text || 'Category';
    }

    [nameInput, locInput, dateInput, priceInput].forEach(el => el.addEventListener('input', sync));
    catSelect.addEventListener('change', sync);
    sync();
})();
</script>
</body></html>
