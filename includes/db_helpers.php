<?php
/**
 * DB helper functions — all return associative arrays or null.
 * Require db_config.php before including this file.
 */

function getEvent(int $id): ?array
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT events.*, categories.name AS category_name
         FROM events
         JOIN categories ON events.category_id = categories.id
         WHERE events.id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getEvents(string $orderBy = 'events.id DESC'): array
{
    global $conn;
    $allowed = [
        'events.id DESC', 'events.id ASC',
        'events.name ASC', 'events.name DESC',
        'events.price ASC', 'events.price DESC',
    ];
    if (!in_array($orderBy, $allowed, true)) {
        $orderBy = 'events.id DESC';
    }
    $result = $conn->query(
        "SELECT events.*, categories.name AS category_name
         FROM events
         JOIN categories ON events.category_id = categories.id
         ORDER BY $orderBy"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function getPromotedEvents(int $limit = 3): array
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT * FROM events WHERE top_event = 1 ORDER BY event_date DESC LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $rows = [];
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function getUserProfile(int $userId): ?array
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT users.id, users.username, users.is_admin, profiles.bio, profiles.avatar
         FROM users
         LEFT JOIN profiles ON users.id = profiles.user_id
         WHERE users.id = ?"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getEventComments(int $eventId): array
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT comments.*, users.username, profiles.avatar
         FROM comments
         JOIN users ON comments.user_id = users.id
         LEFT JOIN profiles ON users.id = profiles.user_id
         WHERE comments.event_id = ?
         ORDER BY comments.comment_date ASC"
    );
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $rows = [];
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function getUserComments(int $userId, int $limit = 5): array
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT comments.comment, comments.comment_date, events.name AS event_name, events.id AS event_id
         FROM comments
         JOIN events ON comments.event_id = events.id
         WHERE comments.user_id = ?
         ORDER BY comments.comment_date DESC
         LIMIT ?"
    );
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $rows = [];
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function getUserAttendedEvents(int $userId): array
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT events.*, categories.name AS category_name,
                event_attendance.attendance_date AS registered_at
         FROM event_attendance
         JOIN events ON event_attendance.event_id = events.id
         JOIN categories ON events.category_id = categories.id
         WHERE event_attendance.user_id = ?
         ORDER BY events.event_date DESC"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $rows = [];
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function getAttendeeCount(int $eventId): int
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM event_attendance WHERE event_id = ?"
    );
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $count;
}

function isUserAttending(int $eventId, int $userId): bool
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT 1 FROM event_attendance WHERE event_id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $eventId, $userId);
    $stmt->execute();
    $attending = ($stmt->get_result()->num_rows > 0);
    $stmt->close();
    return $attending;
}

function getCategories(): array
{
    global $conn;
    $result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[$row['id']] = $row['name'];
    }
    return $rows;
}

function searchEvents(string $query, int $categoryId = 0, string $orderBy = 'events.id DESC'): array
{
    global $conn;
    $allowed = [
        'events.id DESC', 'events.id ASC',
        'events.name ASC', 'events.name DESC',
        'events.price ASC', 'events.price DESC',
    ];
    if (!in_array($orderBy, $allowed, true)) {
        $orderBy = 'events.id DESC';
    }

    $search = '%' . $query . '%';

    if ($categoryId > 0) {
        $stmt = $conn->prepare(
            "SELECT events.*, categories.name AS category_name
             FROM events
             JOIN categories ON events.category_id = categories.id
             WHERE (events.name LIKE ? OR events.location LIKE ?)
               AND events.category_id = ?
             ORDER BY $orderBy"
        );
        $stmt->bind_param("ssi", $search, $search, $categoryId);
    } else {
        $stmt = $conn->prepare(
            "SELECT events.*, categories.name AS category_name
             FROM events
             JOIN categories ON events.category_id = categories.id
             WHERE events.name LIKE ? OR events.location LIKE ?
             ORDER BY $orderBy"
        );
        $stmt->bind_param("ss", $search, $search);
    }

    $stmt->execute();
    $rows = [];
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/**
 * Returns a web-relative URL for an avatar (relative to APP_URL).
 * Uses absolute filesystem check so it works from any subfolder.
 */
function avatarUrl(string $avatar): string
{
    if (!empty($avatar) && file_exists(UPLOAD_AVATARS . $avatar)) {
        return APP_URL . '/uploads/avatars/' . $avatar;
    }
    return APP_URL . '/uploads/avatars/default_avatar.png';
}

/**
 * Returns a web-relative URL for an event photo.
 * $photo is stored as "uploads/events/filename.ext" in the DB.
 */
function eventPhotoUrl(string $photo): string
{
    if (!empty($photo)) {
        $abs = APP_ROOT . '/' . $photo;
        if (file_exists($abs)) {
            return APP_URL . '/' . $photo;
        }
    }
    return APP_URL . '/uploads/events/default_event.png';
}
