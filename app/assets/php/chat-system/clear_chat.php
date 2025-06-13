<?php
session_start();
require_once 'mysql.php';

if (!isset($_SESSION['id'], $_POST['receiver'])) {
    http_response_code(400);
    exit("Fehlende Daten");
}

// Eigene chat_user_id holen
$stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
$stmt->bindParam(":id", $_SESSION['id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$senderId = $user['chat_user_id'] ?? null;

$receiverId = trim($_POST['receiver']);

if (!$senderId || !$receiverId) {
    http_response_code(400);
    exit("Ungültige Daten");
}

// Nachrichten, die vom Benutzer gesendet wurden – setze deleted_for_sender = 1
$stmt = $mysql->prepare("
    UPDATE messages 
    SET deleted_for_sender = 1
    WHERE sender = :sender AND receiver = :receiver AND group_id IS NULL
");
$stmt->execute([
    'sender' => $senderId,
    'receiver' => $receiverId
]);

// Nachrichten, die an den Benutzer gesendet wurden – setze deleted_for_receiver = 1
$stmt = $mysql->prepare("
    UPDATE messages 
    SET deleted_for_receiver = 1
    WHERE sender = :receiver AND receiver = :sender AND group_id IS NULL
");
$stmt->execute([
    'sender' => $senderId,
    'receiver' => $receiverId
]);

// Optional: Endgültig löschen, wenn beide gelöscht haben
$stmt = $mysql->prepare("
    DELETE FROM messages
    WHERE ((sender = :sender AND receiver = :receiver)
        OR (sender = :receiver AND receiver = :sender))
      AND deleted_for_sender = 1
      AND deleted_for_receiver = 1
      AND group_id IS NULL
");
$stmt->execute([
    'sender' => $senderId,
    'receiver' => $receiverId
]);

echo "OK";
?>
