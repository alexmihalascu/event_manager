<?php
include 'navbar.php';
include 'db_config.php';

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Încărcăm categoriile pentru formular
$categoriesQuery = "SELECT id, name FROM categories";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $name = sanitizeInput($_POST['name']);
    $location = sanitizeInput($_POST['location']);
    $event_date = sanitizeInput($_POST['event_date']);
    $event_time = sanitizeInput($_POST['event_time']);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    // Insert the event without the photo
    $sql = "INSERT INTO events (name, location, event_date, event_time, price, category_id) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssdi", $name, $location, $event_date, $event_time, $price, $category_id);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
            exit;
        }
        $last_id = $conn->insert_id;
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }

    // Handle the uploaded photo if it exists
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
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $filePathToSave, $last_id);
                $stmt->execute();
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    $stmt->close();
    echo "New event added successfully.";
}
?>

<link rel="stylesheet" href="style.css">

<div class="form-container">
    <form method="post" action="add_event.php" enctype="multipart/form-data">
        <h1>Add Event</h1>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>
        </div>
        <div class="form-group">
            <label for="event_date">Date:</label>
            <input type="date" id="event_date" name="event_date" required>
        </div>
        <div class="form-group">
            <label for="event_time">Time:</label>
            <input type="time" id="event_time" name="event_time" required>
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" step="0.01" name="price" required>
        </div>
        <div class="form-group">
        <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $id => $name): ?>
                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="event_photo">Event Photo:</label>
            <input type="file" id="event_photo" name="event_photo">
        </div>
        <div class="form-group">
            <input type="submit" value="Add Event">
        </div>
    </form>
</div>
