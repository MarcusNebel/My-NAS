<?php
session_start();
require_once 'mysql.php';

if(isset($_SESSION['id'])) {
    $stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
    $stmt->bindParam(":id", $_SESSION['id']);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentUserId = $currentUser['chat_user_id'] ?? null;
} else {
    $currentUserId = null;
}

if(isset($_GET['chatUserID'])) {
    $chatUserId = $_GET['chatUserID'];
} else {
    $chatUserId = null;
}

if (!$currentUserId || !$chatUserId) {
    echo json_encode([]);
    exit;
}

$stmt = $mysql->prepare("
    SELECT sender, receiver, message, attachment_path, status, timestamp 
    FROM messages 
    WHERE (sender = :currentUser AND receiver = :chatUser) 
       OR (sender = :chatUser AND receiver = :currentUser)
    ORDER BY timestamp ASC
");

$stmt->execute(['currentUser' => $currentUserId, 'chatUser' => $chatUserId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>