<?php
include 'navbar.php';
include 'db_config.php';

$targetDir = "uploads/events/"; // Define the target directory for event photos

// Check if an ID was provided and it's a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventId = $conn->real_escape_string($_GET['id']);

    // Fetch the event data from the database
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

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle the form data and perform the update
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $price = $conn->real_escape_string($_POST['price']);
    
    // Handle the uploaded photo if any
    $photoUpdated = false;
    $newFilePath = ""; // Initialize variable for storing the new file path
    if (isset($_FILES['event_photo']) && $_FILES['event_photo']['error'] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES['event_photo']['name'], PATHINFO_EXTENSION));
        $newFileName = "event_{$eventId}_photo." . $imageFileType; // New file name
        $targetFilePath = $targetDir . $newFileName; // Full path for file operations
        
        $check = getimagesize($_FILES['event_photo']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['event_photo']['tmp_name'], $targetFilePath)) {
                $photoUpdated = true;
                $newFilePath = $targetFilePath; // Set the new file path to include the directory
                // Delete old image file if different from new file
                if (!empty($event['photo']) && $event['photo'] != $newFileName && file_exists($targetDir . $event['photo'])) {
                    unlink($targetDir . $event['photo']);
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
        }
    }
    
    // Prepare the update query
    if ($photoUpdated) {
        $sql = "UPDATE events SET name = ?, location = ?, event_date = ?, event_time = ?, price = ?, photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsi", $name, $location, $event_date, $event_time, $price, $newFilePath, $eventId);
    } else {
        $sql = "UPDATE events SET name = ?, location = ?, event_date = ?, event_time = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdis", $name, $location, $event_date, $event_time, $price, $eventId);
    }

    // Execute the update query
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
