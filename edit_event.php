<?php
include 'navbar.php';
include 'db_config.php';

$targetDir = "uploads/events/";
$categoriesQuery = "SELECT id, name FROM categories";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
}

$eventUpdated = false;
$errorMsg = '';

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
    $newFilePath = $event['photo'];

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
                $errorMsg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $errorMsg = "File is not an image.";
        }
    }

    if (empty($errorMsg)) {
        $sql = "UPDATE events SET name = ?, location = ?, event_date = ?, event_time = ?, price = ?, photo = ?, category_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsii", $name, $location, $event_date, $event_time, $price, $newFilePath, $category_id, $eventId);

        if ($stmt->execute()) {
            $eventUpdated = true;
        } else {
            $errorMsg = "Error updating record: " . $conn->error;
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="event-container container mt-5">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title">Edit Event</h1>
            <?php if ($eventUpdated): ?>
                <div class="alert alert-success">Event updated successfully.</div>
            <?php elseif (!empty($errorMsg)): ?>
                <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            <form method="post" action="edit_event.php?id=<?php echo $eventId; ?>" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($event['name']); ?>" required class="form-control">
            </div>
        
            <div class="form-group mb-3">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required class="form-control">
            </div>
        
            <div class="form-group mb-3">
                <label for="event_date">Date:</label>
                <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required class="form-control">
            </div>
        
            <div class="form-group mb-3">
                <label for="event_time">Time:</label>
                <input type="time" id="event_time" name="event_time" value="<?php echo htmlspecialchars($event['event_time']); ?>" required class="form-control">
            </div>
        
            <div class="form-group mb-3">
                <label for="price">Price:</label>
                <input type="number" id="price" step="0.01" name="price" value="<?php echo htmlspecialchars($event['price']); ?>" required class="form-control">
            </div>
        
            <div class="form-group mb-3">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" class="form-control">
                    <?php foreach ($categories as $id => $name) : ?>
                        <option value="<?php echo $id; ?>" <?php echo $id == $event['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        
            <div class="form-group mb-3">
                <label for="event_photo">Event Photo:</label>
                <div class="mb-2">
                    <?php if (!empty($event['photo'])) : ?>
                            <img src="<?php echo htmlspecialchars($event['photo']); ?>" alt="Event Photo" style="max-width: 200px; display: block; margin-bottom: 10px;">
                    <?php endif?>
                </div>
                <input type="file" id="event_photo" name="event_photo" class="form-control">
            </div>
        
            <div class="form-group">
                <div class="text-center">
                    <input type="submit" value="Update Event" class="btn btn-primary center">
                </div>
            </div>
        </form>            
        </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
