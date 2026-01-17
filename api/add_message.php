<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$path = __DIR__ . '/../data/messages.json';

$now = time();
if (!empty($_SESSION['last_post']) && ($now - $_SESSION['last_post']) < 5) {
  echo json_encode(['ok' => false, 'error' => 'Please wait a few seconds and try again.']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
  echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
  exit;
}

$honeypot = trim((string)($input['website'] ?? ''));
if ($honeypot !== '') {
  echo json_encode(['ok' => true]);
  exit;
}

$name = trim((string)($input['name'] ?? ''));
$message = trim((string)($input['message'] ?? ''));
$anonymous = (bool)($input['anonymous'] ?? false);

if ($message === '' || mb_strlen($message) > 220) {
  echo json_encode(['ok' => false, 'error' => 'Message is required (max 220 chars).']);
  exit;
}
if (mb_strlen($name) > 40) $name = mb_substr($name, 0, 40);

$rot = ((random_int(-35, 35)) / 10) . "deg";

$note = [
  'id' => bin2hex(random_bytes(8)),
  'name' => $name,
  'anonymous' => $anonymous,
  'message' => $message,
  'time' => (int)(microtime(true) * 1000),
  'rot' => $rot
];

if (!file_exists($path)) {
  @file_put_contents($path, "[]");
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

array_unshift($data, $note);

if (count($data) > 200) $data = array_slice($data, 0, 200);

rewind($fp);
ftruncate($fp, 0);
fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
fflush($fp);

flock($fp, LOCK_UN);
fclose($fp);

$_SESSION['last_post'] = $now;

echo json_encode(['ok' => true, 'note' => $note]);
