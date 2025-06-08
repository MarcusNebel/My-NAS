<?php
session_start();
require_once 'mysql.php';

if (!isset($_SESSION['id'], $_POST['message'], $_POST['receiver'])) {
    http_response_code(400);
    exit("Fehlende Daten");
}

// Zeitzone auf Berlin setzen
date_default_timezone_set('Europe/Berlin');
$timestamp = date('Y-m-d H:i:s');

// Eigene chat_user_id holen
$stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
$stmt->bindParam(":id", $_SESSION['id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$senderId = $user['chat_user_id'] ?? null;

$message = trim($_POST['message']);
$receiverId = trim($_POST['receiver']);

if (!$senderId || !$receiverId || $message === '') {
    http_response_code(400);
    exit("Ungültige Daten");
}

// Nachricht speichern mit PHP-Zeitstempel
$stmt = $mysql->prepare("INSERT INTO messages (sender, receiver, group_id, message, attachment_path, status, timestamp)
                         VALUES (:sender, :receiver, NULL, :message, NULL, 'sent', :timestamp)");

$stmt->execute([
    'sender' => $senderId,
    'receiver' => $receiverId,
    'message' => $message,
    'timestamp' => $timestamp
]);

echo "OK";
