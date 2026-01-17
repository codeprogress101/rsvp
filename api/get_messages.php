<?php
header('Content-Type: application/json; charset=utf-8');

$path = __DIR__ . '/../data/messages.json';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 12;

if (!file_exists($path)) {
  echo json_encode(['ok' => true, 'notes' => [], 'total' => 0]);
  exit;
}

$raw = file_get_contents($path);
$notes = json_decode($raw, true);
if (!is_array($notes)) $notes = [];

usort($notes, function($a, $b){
  return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
});

$total = count($notes);
$offset = ($page - 1) * $limit;
$slice = array_slice($notes, $offset, $limit);

echo json_encode([
  'ok' => true,
  'notes' => $slice,
  'total' => $total,
]);
