<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */

session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister($pdo);
        break;
    case 'login':
        handleLogin($pdo);
        break;
    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleRegister($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['email'], $data['password'], $data['first_name'], $data['last_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO admins (first_name, last_name, email, password_hash, access_level) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $passwordHash, $data['access_level'] ?? 'moderator']);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleLogin($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing credentials']);
        return;
    }
    $stmt = $pdo->prepare("SELECT admin_id, password_hash FROM admins WHERE email = ?");
    $stmt->execute([$data['email']]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($data['password'], $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid admin credentials']);
    }
}
?>
