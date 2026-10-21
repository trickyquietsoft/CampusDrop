<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */

/**
 * CAMPUSDROP LISTING API
 * Handles the lifecycle of a textbook listing: Creation, Retrieval, and Deletion.
 */

// 1. Safe Session Start: Prevents notices if included inside another file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';
require_once 'upload_manager.php';

/**
 * EXECUTION GUARD & ROUTER
 * Only execute headers and JSON routing if this file is accessed directly.
 */
if (basename($_SERVER['PHP_SELF']) === 'listing_api.php') {
    
    // Output as JSON for direct API calls
    header('Content-Type: application/json');

    // Security Gatekeeper for modifying data
    $method = $_SERVER['REQUEST_METHOD'];
    if (!isset($_SESSION['user_id']) && ($method === 'POST' || $method === 'DELETE')) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required. Please log in to manage listings.']);
        exit;
    }

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            handleCreate($pdo);
            break;
        case 'get':
            echo json_encode(handleGet($pdo));
            break;
        case 'get_mine':
            echo json_encode(handleGet($pdo, 'me'));
            break;
        case 'delete':
            handleDelete($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action. Use create, get, or delete.']);
            break;
    }
    
    // Halt execution to prevent any accidental output
    exit;
}

/**
 * FUNCTION: handleCreate
 * Processes the submission of a new textbook listing.
 */
function handleCreate($pdo) {
    // Failsafe for internal function calls
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'User session not found.']);
        return;
    }

    $userId = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $course_code = trim($_POST['course_code'] ?? '');
    $book_condition = $_POST['condition'] ?? 'good';
    $listing_type = $_POST['listing_type'] ?? 'both';
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : null;
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($author) || empty($isbn)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields (Title, Author, or ISBN).']);
        return;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO listings (seller_id, title, author, isbn, course_code, book_condition, listing_type, price, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $author, $isbn, $course_code, $book_condition, $listing_type, $price, $description]);
        $listingId = $pdo->lastInsertId();

        // Safely check if a file was actually uploaded
        if (!empty($_FILES['images']['name'][0])) {
            uploadListingImages($pdo, $listingId, $_FILES['images']);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'listing_id' => $listingId,
            'redirect' => 'index.php'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Listing creation failed: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An internal server error occurred while creating the listing.']);
    }
}

/**
 * FUNCTION: handleGet
 * Retrieves listings for the homepage, specific product, or user's account page.
 */
function handleGet($pdo, $forcedSellerId = null) {
    $id = $_GET['id'] ?? null;
    $search = $_GET['search'] ?? '';
    $sellerId = $forcedSellerId ?? $_GET['seller_id'] ?? null;

    // MODE A: Fetch ONE specific book (for product.php)
    if ($id) {
        $stmt = $pdo->prepare("
            SELECT l.*, u.first_name, u.last_name, u.university, u.email, img.image_path
            FROM listings l
            JOIN users u ON l.seller_id = u.user_id
            LEFT JOIN listing_images img ON l.listing_id = img.listing_id AND img.is_primary = 1
            WHERE l.listing_id = ? AND l.status = 'active'
        ");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        // Handle direct API 404s gracefully without breaking array returns
        if (!$book && basename($_SERVER['PHP_SELF']) === 'listing_api.php') {
            http_response_code(404);
            return ['error' => 'Book not found.'];
        }
        return $book ?: [];
    }

    // MODE B: Fetch the catalogue
    $uniFilter = $_SESSION['university'] ?? null;
    
    // Session Repair
    if (!$uniFilter && isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT university FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $uniFilter = $res['university'] ?? null;
        $_SESSION['university'] = $uniFilter;
    }

    // Handle fetching "My Posts" correctly
    if ($sellerId === 'me' && isset($_SESSION['user_id'])) {
        $sellerId = $_SESSION['user_id'];
    }

    $query = "SELECT l.*, u.first_name, u.last_name, u.university, u.email,
              (SELECT image_path FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as image_path
              FROM listings l
              JOIN users u ON l.seller_id = u.user_id
              WHERE l.status = 'active'";
    $params = [];

    // Apply filters
    if ($sellerId) {
        $query .= " AND l.seller_id = ?";
        $params[] = $sellerId;
    }

    if ($uniFilter && !$sellerId) { 
        // Only apply university filter if we aren't looking for a specific user's posts
        $query .= " AND TRIM(u.university) = TRIM(?)";
        $params[] = $uniFilter;
    }

    if ($search) {
        $query .= " AND (l.title LIKE ? OR l.author LIKE ? OR l.isbn = ? OR l.course_code LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", $search, "%$search%"]);
    }

    $query .= " ORDER BY l.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * FUNCTION: handleDelete
 * Removes a listing and cleans up physical files and db constraints from the server.
 */
function handleDelete($pdo) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized.']);
        return;
    }

    $listingId = $_GET['id'] ?? 0;
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT seller_id FROM listings WHERE listing_id = ?");
    $stmt->execute([$listingId]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$listing || $listing['seller_id'] != $userId) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. You do not own this listing.']);
        return;
    }

    try {
        $pdo->beginTransaction();

        // 1. Remove associated trades FIRST to satisfy Foreign Key constraints
        $stmtTrades = $pdo->prepare("DELETE FROM trades WHERE listing_id = ?");
        $stmtTrades->execute([$listingId]);

        // 2. Clean up physical files via the upload_manager.php function
        if (function_exists('cleanupListingImages')) {
            cleanupListingImages($listingId);
        }

        // 3. Remove associated image records FIRST to satisfy Foreign Key constraints
        $stmtImages = $pdo->prepare("DELETE FROM listing_images WHERE listing_id = ?");
        $stmtImages->execute([$listingId]);

        // 4. Finally, safe to delete the main listing
        $stmt = $pdo->prepare("DELETE FROM listings WHERE listing_id = ?");
        $stmt->execute([$listingId]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Listing removed successfully.']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Delete failed: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An internal server error occurred while deleting.']);
    }
}
?>