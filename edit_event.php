<?php
include 'navbar.php';
include 'db_config.php';

$targetDir = "uploads/events/";

// Încărcăm categoriile pentru dropdown
$categoriesQuery = "SELECT id, name FROM categories";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventId = $conn->real_escape_string($_GET['id']);

    $sql = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
    } else {
        echo "Event not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "No event ID provided.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $price = $conn->real_escape_string($_POST['price']);
    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : null;

    $photoUpdated = false;
    $newFilePath = $event['photo']; // Presupunem că fotografia rămâne neschimbată inițial

    if (isset($_FILES['event_photo']) && $_FILES['event_photo']['error'] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES['event_photo']['name'], PATHINFO_EXTENSION));
        $newFileName = "event_{$eventId}_photo." . $imageFileType;
        $targetFilePath = $targetDir . $newFileName;

        $check = getimagesize($_FILES['event_photo']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['event_photo']['tmp_name'], $targetFilePath)) {
                $photoUpdated = true;
                $newFilePath = $targetFilePath;

                if (!empty($event['photo']) && $event['photo'] != $newFileName) {
                    unlink($targetDir . $event['photo']);
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    $sql = "UPDATE events SET name = ?, location = ?, event_date = ?, event_time = ?, price = ?, photo = ?, category_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdsii", $name, $location, $event_date, $event_time, $price, $newFilePath, $category_id, $eventId);

    if ($stmt->execute()) {
        echo "Event updated successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}
?>

<link rel="stylesheet" href="style.css">

<div class="event-container">
    <form method="post" action="edit_event.php?id=<?php echo $eventId; ?>" enctype="multipart/form-data">
        <h1>Edit Event</h1>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($event['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
        </div>

        <div class="form-group">
            <label for="event_date">Date:</label>
            <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
        </div>

        <div class="form-group">
            <label for="event_time">Time:</label>
            <input type="time" id="event_time" name="event_time" value="<?php echo htmlspecialchars($event['event_time']); ?>" required>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" step="0.01" name="price" value="<?php echo htmlspecialchars($event['price']); ?>" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id">
                <?php foreach ($categories as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $id == $event['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="event_photo">Event Photo:</label>
            <?php if (!empty($event['photo'])): ?>
                <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="Event Photo" style="max-width: 200px; display: block; margin-bottom: 10px;">
            <?php endif; ?>
            <input type="file" id="event_photo" name="event_photo">
        </div>

        <div class="form-group">
            <input type="submit" value="Update Event">
        </div>
    </form>
</div>
