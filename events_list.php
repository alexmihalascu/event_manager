<?php
include 'navbar.php';
include 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$sql = "SELECT events.*, categories.name AS category_name FROM events JOIN categories ON events.category_id = categories.id ORDER BY events.id DESC";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="style.css">

<div class="events-container">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='event-card'>";
            echo "<div class='event-image'>";
            if ($row["photo"] && file_exists($row["photo"])) {
                echo "<img src='" . htmlspecialchars($row["photo"]) . "' alt='Event Photo'>";
            } else {
                echo "<img src='uploads/events/default_event.png' alt='Default Event Image'>";
            }
            echo "</div>";
            echo "<h3>" . htmlspecialchars($row["name"]) . "</h3>";
            echo "<p>Category: " . htmlspecialchars($row["category_name"]) . "</p>";
            echo "<p>Location: " . htmlspecialchars($row["location"]) . "</p>";
            $formattedDate = date('d.m.Y', strtotime($row["event_date"]));
            echo "<p>Date: " . htmlspecialchars($formattedDate) . "</p>";
            $formattedTime = date('H:i', strtotime($row["event_time"]));
            echo "<p>Time: " . htmlspecialchars($formattedTime) . "</p>";
            echo "<p>Price: " . htmlspecialchars(number_format($row["price"])) . " Lei</p>";
            echo "<a href='event_details.php?id=" . $row["id"] . "' class='edit-button'>View Comments</a>";
            if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                echo "<a href='edit_event.php?id=" . $row["id"] . "' class='edit-button'>Edit</a>";
                echo "<a href='delete_event.php?id=" . $row["id"] . "' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this event?\");'>Delete</a>";
            }
            echo "</div>";
        }
    } else {
        echo "<p class='no-events'>No events found.</p>";
    }
    ?>
</div>