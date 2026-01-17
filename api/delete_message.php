<?php
header('Content-Type: application/json; charset=utf-8');

$ADMIN_KEY = 'superadmin123';

$headerKey = $_SERVER['HTTP_X_ADMIN_KEY'] ?? '';
if (!is_string($headerKey) || $headerKey !== $ADMIN_KEY) {
  echo json_encode(['ok' => false, 'error' => 'Unauthorized.']);
  exit;
}

$path = __DIR__ . '/../data/messages.json';
if (!file_exists($path)) {
  echo json_encode(['ok' => false, 'error' => 'Storage not found.']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = is_array($input) ? (string)($input['id'] ?? '') : '';
$id = trim($id);

if ($id === '') {
  echo json_encode(['ok' => false, 'error' => 'Missing id.']);
  exit;
}

$fp = fopen($path, 'c+');
if (!$fp) {
  echo json_encode(['ok' => false, 'error' => 'Cannot open storage file.']);
  exit;
}

flock($fp, LOCK_EX);
rewind($fp);
$raw = stream_get_contents($fp);
$data = json_decode($raw ?: "[]", true);
if (!is_array($data)) $data = [];

$before = count($data);
$data = array_values(array_filter($data, function($n) use ($id){
  return (string)($n['id'] ?? '') !== $id;
}));
$after = count($data);

rewind($fp);
ftruncate($fp, 0);
fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['ok' => true, 'deleted' => ($before - $after)]);
