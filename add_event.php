<?php
include 'navbar.php';
include 'db_config.php';

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$categoriesQuery = "SELECT id, name FROM categories";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
}

$errorMsg = '';
$eventAdded = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $location = sanitizeInput($_POST['location']);
    $event_date = sanitizeInput($_POST['event_date']);
    $event_time = sanitizeInput($_POST['event_time']);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    $sql = "INSERT INTO events (name, location, event_date, event_time, price, category_id) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssdi", $name, $location, $event_date, $event_time, $price, $category_id);
        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            $eventAdded = true;

            if (isset($_FILES['event_photo']) && $_FILES['event_photo']['error'] == 0) {
                $target_dir = "uploads/events/";
                $imageFileType = strtolower(pathinfo($_FILES['event_photo']['name'], PATHINFO_EXTENSION));
                $newFileName = "event_{$last_id}_photo." . $imageFileType;
                $target_file = $target_dir . $newFileName;

                $check = getimagesize($_FILES['event_photo']['tmp_name']);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES['event_photo']['tmp_name'], $target_file)) {
                        $filePathToSave = $target_dir . $newFileName;
                        $sql = "UPDATE events SET photo = ? WHERE id = ?";
                        if ($updateStmt = $conn->prepare($sql)) {
                            $updateStmt->bind_param("si", $filePathToSave, $last_id);
                            $updateStmt->execute();
                            $updateStmt->close();
                        }
                    } else {
                        $errorMsg = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $errorMsg = "File is not an image.";
                }
            }
        } else {
            $errorMsg = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMsg = "Error preparing statement: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title text-center">Add Event</h1>
            <?php if ($eventAdded): ?>
                <div class="alert alert-success">Event added successfully.</div>
            <?php elseif (!empty($errorMsg)): ?>
                <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            <form method="post" action="add_event.php" enctype="multipart/form-data">
                <div class="form-group mb-3">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" required class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label for="event_date">Date:</label>
                    <input type="date" id="event_date" name="event_date" required class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label for="event_time">Time:</label>
                    <input type="time" id="event_time" name="event_time" required class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label for="price">Price:</label>
                    <input type="number" id="price" step="0.01" name="price" required class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <?php foreach ($categories as $id => $name) : ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="event_photo">Event Photo:</label>
                    <input type="file" id="event_photo" name="event_photo" class="form-control">
                </div>

                <div class="text-center">
                    <input type="submit" value="Add Event" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

