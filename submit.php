<?php
require_once __DIR__ . "/db.php";

function redirect_rsvp($status, $msg = "") {
  $q = "rsvp=" . urlencode($status);
  if (!empty($msg)) {
    $q .= "&msg=" . urlencode($msg);
  }
  header("Location: index.php?$q#rsvp");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  redirect_rsvp("error", "Invalid request.");
}

$guest_name = trim($_POST["guest_name"] ?? "");
$attendance = strtoupper(trim($_POST["attendance"] ?? ""));

// normalize spaces
$guest_name = preg_replace('/\s+/', ' ', $guest_name);

// validations
if ($guest_name === "") redirect_rsvp("error", "Please enter your name.");
if (mb_strlen($guest_name) > 120) redirect_rsvp("error", "Name is too long.");
if (!in_array($attendance, ["YES", "NO"], true)) redirect_rsvp("error", "Please choose an option.");

$ip = $_SERVER["REMOTE_ADDR"] ?? null;
$ua = $_SERVER["HTTP_USER_AGENT"] ?? null;
if ($ua !== null) $ua = mb_substr($ua, 0, 255);

try {

  /* =============================
     DUPLICATE NAME CHECK (1 min)
  ============================== */
  $dup = $conn->prepare("
    SELECT id FROM wedding_rsvp
    WHERE guest_name = ?
      AND created_at >= (NOW() - INTERVAL 60 SECOND)
    LIMIT 1
  ");
  $dup->bind_param("s", $guest_name);
  $dup->execute();
  $dup->store_result();

  if ($dup->num_rows > 0) {
    $dup->close();
    redirect_rsvp("error", "It looks like you already submitted recently.");
  }
  $dup->close();


  /* =============================
     RATE LIMIT BY IP (20 sec)
  ============================== */
  if ($ip) {
    $stmt = $conn->prepare("
      SELECT COUNT(*) AS cnt
      FROM wedding_rsvp
      WHERE ip_address = ?
        AND created_at >= (NOW() - INTERVAL 20 SECOND)
    ");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($row && (int)$row["cnt"] > 0) {
      redirect_rsvp("error", "Please wait a moment and try again.");
    }
  }


  /* =============================
     INSERT RSVP
  ============================== */
  $stmt = $conn->prepare("
    INSERT INTO wedding_rsvp (guest_name, attendance, ip_address, user_agent)
    VALUES (?, ?, ?, ?)
  ");

  if (!$stmt) redirect_rsvp("error", "Server error. Please try again.");

  $stmt->bind_param("ssss", $guest_name, $attendance, $ip, $ua);

  if (!$stmt->execute()) {
    $stmt->close();
    redirect_rsvp("error", "Could not save RSVP. Please try again.");
  }

  $stmt->close();


  /* =============================
     OPTIONAL GOOGLE SHEETS
  ============================== */
  // $SHEETS_WEBHOOK_URL = "";
  // if (!empty($SHEETS_WEBHOOK_URL)) {
  //   $url = $SHEETS_WEBHOOK_URL . "?" . http_build_query([
  //     "guest_name" => $guest_name,
  //     "attendance" => $attendance
  //   ]);
  //   @file_get_contents($url);
  // }

  redirect_rsvp("success");

} catch (Throwable $e) {
  redirect_rsvp("error", "Something went wrong. Please try again.");
}
