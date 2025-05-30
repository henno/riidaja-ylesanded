<?php
session_start();
header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/config.php';

// Check if user is authenticated and is admin
if (!isset($_SESSION['user']['email']) || $_SESSION['user']['email'] !== ADMIN_EMAIL) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Juurdepääs keelatud']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Ainult POST päringud on lubatud']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['email']) || !is_string($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email on kohustuslik']);
    exit;
}

$email = trim($input['email']);
$grade = isset($input['grade']) ? trim($input['grade']) : null;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vigane email formaat']);
    exit;
}

// Validate grade (if provided)
$allowedGrades = ['5r', '7r', '8r'];
if ($grade !== null && $grade !== '' && !in_array($grade, $allowedGrades)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vigane klass. Lubatud: ' . implode(', ', $allowedGrades)]);
    exit;
}

// Convert empty string to null
if ($grade === '') {
    $grade = null;
}

try {
    // Include required files
    require_once __DIR__ . '/models/StudentsModel.php';

    // Create model instance
    $studentsModel = new StudentsModel();

    // Update the grade
    $success = $studentsModel->updateStudentGrade($email, $grade);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Klass edukalt salvestatud',
            'email' => $email,
            'grade' => $grade
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Andmebaasi viga']);
    }

} catch (Exception $e) {
    error_log("Error in save_grade.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Serveri viga']);
}
