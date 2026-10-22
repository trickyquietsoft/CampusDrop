<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */

/**
 * CAMPUSDROP DATABASE CONNECTION
 * This file is the central gateway for all database interactions.
 * It is required by every API script to establish a connection.
 */

// --- Configuration Constants ---
// Use environment variables for Docker compatibility, fallback to localhost for XAMPP
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'campusdrop_schema');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

try {
    /**
     * PDO (PHP Data Objects) Connection
     * We use PDO because it is more secure and flexible than mysqli.
     * Charset utf8mb4 is used to support all characters, including emojis.
     */
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            // Throw exceptions on SQL errors for better debugging
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Return results as associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Disable emulation to use real prepared statements for security
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    /**
     * Error Handling
     * If the connection fails, we send a 500 server error and a JSON message.
     */
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>
