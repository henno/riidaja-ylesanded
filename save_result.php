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

// Get exercise configuration from database
$stmt = $db->prepare('SELECT result_type, min_value FROM exercises WHERE id = ?');
$stmt->execute([$data['exercise_id']]);
$exercise = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exercise) {
  http_response_code(400);
  echo "Invalid exercise ID";
  exit;
}

// Validate result based on exercise type
if ($exercise['result_type'] === 'wpm') {
  // WPM exercises: allow any non-zero value (negative for failed, positive for passed)
  if ($data['elapsed'] == 0) {
    http_response_code(400);
    echo "Malformed value";
    exit;
  }
} else {
  // Time-based exercises: validate minimum time
  if ($data['elapsed'] < $exercise['min_value']) {
    http_response_code(400);
    echo "Malformed value";
    exit;
  }
}

$stmt = $db->prepare('INSERT INTO results (email, name, exercise_id, elapsed, timestamp) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([
  $_SESSION['user']['email'],
  $_SESSION['user']['name'],
  $data['exercise_id'],
  $data['elapsed'],
  date('Y-m-d H:i:s')
]);
