<?php
session_start();
$db = new PDO('sqlite:' . __DIR__ . '/database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user']['email']) || !isset($data['exercise_id']) || !isset($data['elapsed'])) {
  http_response_code(400);
  echo "Missing data";
  exit;
}

if ($data['elapsed'] < 11) {
  http_response_code(400);
  echo "Malformed time";
  exit;
}

$stmt = $db->prepare('INSERT INTO results (email, name, exercise_id, elapsed) VALUES (?, ?, ?, ?)');
$stmt->execute([
  $_SESSION['user']['email'],
  $_SESSION['user']['name'],
  $data['exercise_id'],
  $data['elapsed']
]);
