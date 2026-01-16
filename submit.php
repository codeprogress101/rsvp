<?php
include 'db.php';

$name = trim($_POST['name'] ?? '');
$attending = $_POST['attending'] ?? '';

if (!$name || !in_array($attending, ['Yes','No'])) {
    exit("Invalid input.");
}

$stmt = $conn->prepare(
    "INSERT INTO rsvps (name, attending) VALUES (?, ?)"
);
$stmt->bind_param("ss", $name, $attending);
$stmt->execute();

echo "<h3>Thank you 💐</h3><p>Your RSVP has been saved.</p>";
