<?php
require_once 'db_connect.php';
$users = $pdo->query("SELECT user_id, first_name, university FROM users")->fetchAll(PDO::FETCH_ASSOC);
$listings = $pdo->query("SELECT listing_id, seller_id, title FROM listings")->fetchAll(PDO::FETCH_ASSOC);
$trades = $pdo->query("SELECT * FROM trades")->fetchAll(PDO::FETCH_ASSOC);
$messages = $pdo->query("SELECT * FROM messages")->fetchAll(PDO::FETCH_ASSOC);

$out = "USERS:\n" . print_r($users, true) . "\n\n";
$out .= "LISTINGS:\n" . print_r($listings, true) . "\n\n";
$out .= "TRADES:\n" . print_r($trades, true) . "\n\n";
$out .= "MESSAGES:\n" . print_r($messages, true) . "\n";

file_put_contents('db_debug_dump.txt', $out);
?>