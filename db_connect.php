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
// The hostname of the MySQL server (localhost for XAMPP)
define('DB_HOST', 'localhost');
// Updated to match the user's local database title
define('DB_NAME', 'campusdrop_schema');
// Default XAMPP MySQL username
define('DB_USER', 'root');
// Default XAMPP MySQL password (empty string)
define('DB_PASS', '');

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
