<?php
error_reporting(E_ALL);
ini_set(display_errors, 1);
require_once C:/xampp/htdocs/../includes/db_connect.php;

echo "Checking DB...\n";
try {
    $users = $pdo->query("SELECT count(*) as cnt FROM users")->fetch();
    echo "Users count: " . $users[cnt] . "\n";
    $listings = $pdo->query("SELECT count(*) as cnt FROM listings")->fetch();
    echo "Listings count: " . $listings[cnt] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
