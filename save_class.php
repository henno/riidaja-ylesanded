<?php
session_start();
require_once __DIR__ . '/bootstrap.php';
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
if (!isset($input['action']) || !in_array($input['action'], ['add', 'delete'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vigane tegevus']);
    exit;
}

if (!isset($input['name']) || !is_string($input['name']) || trim($input['name']) === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Klassi nimi on kohustuslik']);
    exit;
}

$action = $input['action'];
$name = trim($input['name']);

// Validate class name (alphanumeric + common chars, max 20 chars)
if (!preg_match('/^[a-zA-Z0-9äöüõÄÖÜÕ]{1,20}$/', $name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Klassi nimi võib sisaldada ainult tähti ja numbreid (max 20 tähemärki)']);
    exit;
}

try {
    require_once __DIR__ . '/models/StudentsModel.php';
    $studentsModel = new StudentsModel();

    if ($action === 'add') {
        $success = $studentsModel->addClass($name);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Klass lisatud']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Klassi lisamine ebaõnnestus (võib-olla juba olemas)']);
        }
    } else {
        $success = $studentsModel->deleteClass($name);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Klass kustutatud']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Klassi kustutamine ebaõnnestus']);
        }
    }
} catch (Exception $e) {
    error_log("Error in save_class.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Serveri viga']);
}
