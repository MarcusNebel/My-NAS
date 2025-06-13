<?php
session_start();
require_once 'mysql.php';

if (!isset($_SESSION['id']) || !isset($_POST['contact_id'])) {
    http_response_code(400);
    exit("Fehlende Daten");
}

// Eigene chat_user_id abrufen
$stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
$stmt->bindParam(":id", $_SESSION['id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['chat_user_id'])) {
    http_response_code(400);
    exit("Benutzer nicht gefunden");
}

$ownerId = $user['chat_user_id'];
$contactId = trim($_POST['contact_id']);

// Kontakt löschen
$stmt = $mysql->prepare("DELETE FROM contacts WHERE owner_id = :owner AND contact_id = :contact OR owner_id = :contact AND contact_id = :owner");
$stmt->execute([
    'owner' => $ownerId,
    'contact' => $contactId
]);
?>
