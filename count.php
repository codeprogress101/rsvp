<?php
include 'db.php';

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM rsvps WHERE attending = 'Yes'"
);

$row = $result->fetch_assoc();
echo $row['total'];
