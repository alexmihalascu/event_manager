<?php
include 'navbar.php';
include 'db_config.php';

// Check if user is not logged in
if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit;
}

// Query to get all events
$sql = "SELECT * FROM events ORDER BY id DESC";
$result = $conn->query($sql);
?>
<link rel="stylesheet" href="style.css">

<div class="events-container">
  <?php
  if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
      echo "<div class='event-card'>";
      echo "<div class='event-image'>";
      // Check if an event photo exists and display it
      if ($row["photo"] && file_exists($row["photo"])) {
        echo "<img src='" . htmlspecialchars($row["photo"]) . "' alt='Event Photo'>";
      } else {
        echo "<img src='uploads/events/defaultevent.png' alt='Default Event Image'>";
      }
      echo "</div>";
      echo "<h3>" . htmlspecialchars($row["name"]) . "</h3>";
      echo "<p>Location: " . htmlspecialchars($row["location"]) . "</p>";
          // Format the date
    $formattedDate = date('d.m.Y', strtotime($row["event_date"]));
    echo "<p>Date: " . htmlspecialchars($formattedDate) . "</p>";

    // Format the time
    $formattedTime = date('H:i', strtotime($row["event_time"]));
    echo "<p>Time: " . htmlspecialchars($formattedTime) . "</p>";
      echo "<p>Price: " . htmlspecialchars(number_format($row["price"])) . " Lei </p>";
      // Edit button only for admin users
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    echo "<a href='edit_event.php?id=" . $row["id"] . "' class='edit-button'>Edit</a>";
    // Add a Delete button for admin users
    echo "<a href='delete_event.php?id=" . $row["id"] . "' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this event?\");'>Delete</a>";
}
      echo "</div>";
    }
  } else {
    echo "<p class='no-events'>No events found.</p>";
  }
  ?>
</div>
