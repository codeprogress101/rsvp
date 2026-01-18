<?php
$host = "fdb1033.awardspace.net";
$user = "4727051_rsvp";
$pass = "logic101";
$db   = "4727051_rsvp";

// $host = "localhost";
// $user = "root";
// $pass = "";
// $db   = "wedding_rsvp";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    exit("Database connection failed");
}
?>
