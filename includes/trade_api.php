<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'initiate':
        handleInitiateTrade($pdo, $userId);
        break;
    case 'send_message':
        handleSendMessage($pdo, $userId);
        break;
    case 'get_trades':
        handleGetTrades($pdo, $userId);
        break;
    case 'get_messages':
        handleGetMessages($pdo, $userId);
        break;
    case 'update_status':
        handleUpdateStatus($pdo, $userId);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action.']);
        break;
}

/**
 * Starts a new trade request from a buyer to a seller.
 */
function handleInitiateTrade($pdo, $userId) {
    $listingId = $_POST['listing_id'] ?? 0;
    $offer = trim($_POST['offer'] ?? 'Interested in this book!');

    if (!$listingId) {
        http_response_code(400);
        echo json_encode(['error' => 'Listing ID is required.']);
        return;
    }

    try {
        // Find the seller to validate and prevent self-trading
        $stmt = $pdo->prepare("SELECT seller_id FROM listings WHERE listing_id = ?");
        $stmt->execute([$listingId]);
        $listing = $stmt->fetch();
        
        if (!$listing) {
            http_response_code(404);
            echo json_encode(['error' => 'Listing not found.']);
            return;
        }

        $sellerId = $listing['seller_id'];
        
        if ($sellerId == $userId) {
            http_response_code(400);
            echo json_encode(['error' => 'You cannot initiate a trade on your own listing.']);
            return;
        }

        // TRANSACTION: Ensure trade and first message are created together
        $pdo->beginTransaction();

        // 1. Create the trade record
        $stmt = $pdo->prepare("INSERT INTO trades (listing_id, buyer_id, offered_item_details, trade_status) VALUES (?, ?, ?, 'requested')");
        $stmt->execute([$listingId, $userId, $offer]);
        $tradeId = $pdo->lastInsertId();

        // 2. Create the initial message
        $msgStmt = $pdo->prepare("INSERT INTO messages (trade_id, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)");
        $msgStmt->execute([$tradeId, $userId, $sellerId, $offer]);

        $pdo->commit();
        echo json_encode(['success' => true, 'trade_id' => $tradeId]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Failed to initiate trade: ' . $e->getMessage()]);
    }
}

/**
 * Sends a message within an existing trade.
 */
function handleSendMessage($pdo, $userId) {
    $tradeId = $_POST['trade_id'] ?? 0;
    $text = trim($_POST['message'] ?? '');

    if (!$tradeId || empty($text)) {
        http_response_code(400);
        echo json_encode(['error' => 'Trade ID and message are required.']);
        return;
    }

    try {
        // Determine the receiver using a JOIN instead of nested subqueries
        $stmt = $pdo->prepare("
            SELECT CASE 
                WHEN t.buyer_id = ? THEN l.seller_id 
                ELSE t.buyer_id 
            END as receiver_id 
            FROM trades t
            JOIN listings l ON t.listing_id = l.listing_id
            WHERE t.trade_id = ?
        ");
        $stmt->execute([$userId, $tradeId]);
        $res = $stmt->fetch();
        
        if (!$res) {
            http_response_code(404);
            echo json_encode(['error' => 'Trade not found.']);
            return;
        }
        
        $receiverId = $res['receiver_id'];

        $stmt = $pdo->prepare("INSERT INTO messages (trade_id, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tradeId, $userId, $receiverId, $text]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Message failed: ' . $e->getMessage()]);
    }
}

/**
 * Fetches all trades a user is involved in (as buyer or seller).
 */
function handleGetTrades($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT t.*, l.title as book_title, u.first_name as partner_name 
        FROM trades t 
        JOIN listings l ON t.listing_id = l.listing_id 
        JOIN users u ON (u.user_id = CASE WHEN t.buyer_id = ? THEN l.seller_id ELSE t.buyer_id END)
        WHERE t.buyer_id = ? OR t.listing_id IN (SELECT listing_id FROM listings WHERE seller_id = ?)
    ");
    $stmt->execute([$userId, $userId, $userId]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Fetches the conversation history for a trade.
 */
function handleGetMessages($pdo, $userId) {
    $tradeId = $_GET['trade_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE trade_id = ? ORDER BY sent_at ASC");
    $stmt->execute([$tradeId]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Updates trade status (e.g., mark as completed).
 */
function handleUpdateStatus($pdo, $userId) {
    $tradeId = $_POST['trade_id'] ?? 0;
    $status = $_POST['status'] ?? '';

    if (!$tradeId || !$status) {
        http_response_code(400);
        echo json_encode(['error' => 'Trade ID and status are required.']);
        return;
    }

    try {
        // Authorization: Ensure the user is part of the trade they are trying to update
        $stmt = $pdo->prepare("
            SELECT t.buyer_id, l.seller_id 
            FROM trades t 
            JOIN listings l ON t.listing_id = l.listing_id 
            WHERE t.trade_id = ?
        ");
        $stmt->execute([$tradeId]);
        $trade = $stmt->fetch();

        if (!$trade || ($trade['buyer_id'] != $userId && $trade['seller_id'] != $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized. You are not a participant in this trade.']);
            return;
        }

        // TRANSACTION: Ensure trade status and listing status update together
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE trades SET trade_status = ? WHERE trade_id = ?");
        $stmt->execute([$status, $tradeId]);
        
        // If completed, also mark the book as completed
        if ($status === 'completed') {
            $stmt2 = $pdo->prepare("UPDATE listings SET status = 'completed' WHERE listing_id = (SELECT listing_id FROM trades WHERE trade_id = ?)");
            $stmt2->execute([$tradeId]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Status update failed: ' . $e->getMessage()]);
    }
}
?>
