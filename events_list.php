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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Events List</title>
</head>
<body>

<div class="container py-5 mt-5">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='col'>";
                echo "<div class='card h-100'>";
                echo "<img src='" . htmlspecialchars($row["photo"] ? $row["photo"] : 'uploads/events/default_event.png') . "' class='card-img-top' alt='Event Photo'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($row["name"]) . "</h5>";
                echo "<p class='card-text'><strong>Category:</strong> " . htmlspecialchars($row["category_name"]) . "</p>";
                echo "<p class='card-text'><strong>Location:</strong> " . htmlspecialchars($row["location"]) . "</p>";
                echo "<p class='card-text'><strong>Date:</strong> " . htmlspecialchars(date('d.m.Y', strtotime($row["event_date"]))) . "</p>";
                echo "<p class='card-text'><strong>Time:</strong> " . htmlspecialchars(date('H:i', strtotime($row["event_time"]))) . "</p>";
                echo "<p class='card-text'><strong>Price:</strong> " . htmlspecialchars(number_format($row["price"])) . " Lei</p>";
                echo "</div>";
                echo "<div class='card-footer'>";
                echo "<a href='event_details.php?id=" . $row["id"] . "' class='btn btn-primary btn-block'>View more details</a>";
                echo "</div>";
                echo "</div>"; // card
                echo "</div>"; // col
            }
        } else {
            echo "<p class='no-events'>No events found.</p>";
        }
        ?>
    </div>
</div>

<!-- Include Bootstrap JS and Popper.js for Bootstrap functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
