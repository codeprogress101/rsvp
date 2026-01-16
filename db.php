<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "wedding_rsvp";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    exit("Database connection failed");
}
?>
