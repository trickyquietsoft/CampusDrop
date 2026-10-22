<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */

/**
 * CAMPUSDROP AUTHENTICATION API
 * Handles User Registration, Login, and Session Management.
 * All responses are returned as JSON.
 */

// Start the session to track logged-in users across different pages
session_start();
// Import the database connection
require_once 'db_connect.php';

// Set response header to JSON for all API calls
header('Content-Type: application/json');

// Determine which authentication action to perform via the 'action' URL parameter
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        // Trigger registration logic
        handleRegister($pdo);
        break;
    case 'login':
        // Trigger login logic
        handleLogin($pdo);
        break;
    case 'logout':
        // Trigger logout logic
        handleLogout();
        break;
    default:
        // Handle requests that don't specify a valid action
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action specified.']);
        break;
}

/**
 * FUNCTION: handleRegister
 * Processes new user account creation.
 */
function handleRegister($pdo) {
    // Get JSON payload from the request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Validation: Check if all required fields are present
    if (!isset($data['email'], $data['password'], $data['first_name'], $data['last_name'], $data['university'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields.']);
        return;
    }

    /**
     * Password Security
     * We NEVER store passwords in plain text. 
     * password_hash uses bcrypt by default, providing strong protection.
     */
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    try {
        // Use a prepared statement to prevent SQL Injection
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, university) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $passwordHash, $data['university']]);
        
        echo json_encode(['success' => true, 'message' => 'User registered successfully.']);
    } catch (PDOException $e) {
        // Check if the error is a Duplicate Entry (Email already exists)
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}

/**
 * FUNCTION: handleLogin
 * Verifies credentials and initializes the user session.
 */
function handleLogin($pdo) {
    // Get JSON payload from request body
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing email or password.']);
        return;
    }

    // Fetch the user record based on email
    $stmt = $pdo->prepare("SELECT user_id, password_hash, first_name, university FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();

    /**
     * Password Verification
     * password_verify checks the plain text input against the stored hash.
     */
    if ($user && password_verify($data['password'], $user['password_hash'])) {
        // Store user identity in the session to keep them logged in
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['university'] = $user['university'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'user' => [
                'id' => $user['user_id'],
                'name' => $user['first_name']
            ]
        ]);
    } else {
        // Return 401 Unauthorized for wrong credentials
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
    }
}

/**
 * FUNCTION: handleLogout
 * Clears the user session and redirects to the home page.
 */
function handleLogout() {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
