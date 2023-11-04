<?php
include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db_config.php';

    // Gather post data and sanitize it
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $price = $conn->real_escape_string($_POST['price']);
    
    // First insert the event without the photo
    $sql = "INSERT INTO events (name, location, event_date, event_time, price) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssd", $name, $location, $event_date, $event_time, $price);
    
    // Execute the prepared statement
    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        $uploadOk = 1;

        // Handle the uploaded photo if it exists
        if (isset($_FILES['event_photo']) && $_FILES['event_photo']['error'] == 0) {
            $target_dir = "uploads/events/";
            $imageFileType = strtolower(pathinfo($_FILES['event_photo']['name'], PATHINFO_EXTENSION));
            // Rename file as 'event_id_photo.extension'
            $newFileName = "event_{$last_id}_photo." . $imageFileType;
            $target_file = $target_dir . $newFileName;

            $check = getimagesize($_FILES['event_photo']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['event_photo']['tmp_name'], $target_file)) {
                    // Update the event with the new photo file name including the directory
                    $filePathToSave = $target_dir . $newFileName;
                    $sql = "UPDATE events SET photo = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $filePathToSave, $last_id);
                    $stmt->execute();
                } else {
                    echo "Sorry, there was an error uploading your file.";
                    $uploadOk = 0;
                }
            } else {
                echo "File is not an image.";
                $uploadOk = 0;
            }
        }

        if ($uploadOk) {
            echo "New event added successfully with photo.";
        } else {
            echo "New event added successfully, but the photo upload failed.";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
}
?>
<link rel="stylesheet" href="style.css">

<div class="form-container">
  <form method="post" action="add_event.php" enctype="multipart/form-data">
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
      <label for="event_photo">Event Photo:</label>
      <input type="file" id="event_photo" name="event_photo">
    </div>
    <div class="form-group">
      <input type="submit" value="Add Event">
    </div>
  </form>
</div>
