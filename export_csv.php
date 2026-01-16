<?php
// export_csv.php
require_once __DIR__ . "/db.php";
session_start();

/* Must match admin.php password gate */
if (empty($_SESSION["is_admin"])) {
  http_response_code(403);
  exit("Forbidden");
}

$search = trim($_GET["search"] ?? "");
$attendance = strtoupper(trim($_GET["attendance"] ?? ""));
if (!in_array($attendance, ["YES","NO","ALL",""], true)) $attendance = "";

$where = [];
$params = [];
$types = "";

if ($search !== "") {
  $where[] = "guest_name LIKE ?";
  $params[] = "%" . $search . "%";
  $types .= "s";
}

if ($attendance === "YES" || $attendance === "NO") {
  $where[] = "attendance = ?";
  $params[] = $attendance;
  $types .= "s";
}

$where_sql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

$sql = "SELECT id, guest_name, attendance, ip_address, user_agent, created_at
        FROM wedding_rsvp
        $where_sql
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=rsvp_export_" . date("Y-m-d_H-i-s") . ".csv");

$out = fopen("php://output", "w");
fputcsv($out, ["ID", "Name", "Attendance", "IP Address", "User Agent", "Created At"]);

while ($row = $res->fetch_assoc()) {
  fputcsv($out, [
    $row["id"],
    $row["guest_name"],
    $row["attendance"],
    $row["ip_address"],
    $row["user_agent"],
    $row["created_at"],
  ]);
}

fclose($out);
$stmt->close();
exit;
