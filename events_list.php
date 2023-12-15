<?php
include 'navbar.php';
include 'db_config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Opțiuni de sortare
$sortOptions = [
    'newest' => 'Newest',
    'oldest' => 'Oldest',
    'name_asc' => 'Name Ascending',
    'name_desc' => 'Name Descending',
    'price_asc' => 'Price Ascending',
    'price_desc' => 'Price Descending'
];

// Setează criteriul și direcția de sortare implicită la 'newest' dacă nu sunt setate
if (!isset($_SESSION['event_sort'])) {
    $_SESSION['event_sort'] = 'newest';
}

// Preia criteriul și direcția de sortare din sesiune sau GET
$sort = isset($_GET['sort']) ? $_GET['sort'] : $_SESSION['event_sort'];

// Actualizează sesiunea cu noul criteriu de sortare
$_SESSION['event_sort'] = $sort;

// Determină coloana și ordinea pentru sortare
switch ($sort) {
    case 'newest':
        $orderBy = 'events.id DESC';
        break;
    case 'oldest':
        $orderBy = 'events.id ASC';
        break;
    case 'name_asc':
        $orderBy = 'events.name ASC';
        break;
    case 'name_desc':
        $orderBy = 'events.name DESC';
        break;
    case 'price_asc':
        $orderBy = 'events.price ASC';
        break;
    case 'price_desc':
        $orderBy = 'events.price DESC';
        break;
    default:
        $orderBy = 'events.id DESC';
}

$sql = "SELECT events.*, categories.name AS category_name FROM events JOIN categories ON events.category_id = categories.id ORDER BY $orderBy";
$result = $conn->query($sql);

if (!$result) {
    die("Error in SQL query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Events List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container py-5 mt-5">
    <h1 class="mb-4 text-center">Events List</h1>

    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <form method="get" action="events_list.php" class="d-flex">
                <select name="sort" class="form-select me-2">
                    <?php foreach ($sortOptions as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $key === $sort ? 'selected' : ''; ?>><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Sort</button>
            </form>
        </div>
    </div>

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
