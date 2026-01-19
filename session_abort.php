<?php
/**
 * Fallback endpoint for session abort via sendBeacon
 * Used when WebSocket connection might not deliver the abort message
 */

$db = new PDO('sqlite:' . __DIR__ . '/database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['sessionId'])) {
    http_response_code(400);
    exit;
}

$sessionId = $data['sessionId'];

// Check if session exists and is still active
$stmt = $db->prepare('SELECT id, started_at FROM exercise_sessions WHERE session_id = ? AND status = ?');
$stmt->execute([$sessionId, 'active']);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if ($session) {
    // Calculate duration
    $startedAt = new DateTime($session['started_at']);
    $now = new DateTime();
    $durationSeconds = $now->getTimestamp() - $startedAt->getTimestamp();

    // Update session
    $stmt = $db->prepare('
        UPDATE exercise_sessions
        SET ended_at = ?, duration_seconds = ?, status = ?
        WHERE session_id = ?
    ');
    $stmt->execute([
        $now->format('Y-m-d H:i:s'),
        $durationSeconds,
        'abandoned',
        $sessionId
    ]);
}

http_response_code(200);
echo 'OK';
