<?php
session_start();
require_once 'mysql.php';

if (!isset($_SESSION['id']) || !isset($_POST['contact_id'])) {
    http_response_code(400);
    exit("Fehlende Daten");
}

// Step 1: Hole die chat_user_id des aktuellen Benutzers
$stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
$stmt->bindParam(":id", $_SESSION['id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['chat_user_id'])) {
    http_response_code(400);
    exit("Benutzer nicht gefunden");
}

$ownerChatUserId = $user['chat_user_id'];
$contactId = trim($_POST['contact_id']);

// Step 2: Prüfe, ob der Kontakt schon existiert
$stmt = $mysql->prepare("SELECT * FROM contacts WHERE owner_id = :owner AND contact_id = :contact");
$stmt->execute(['owner' => $ownerChatUserId, 'contact' => $contactId]);

if ($stmt->rowCount() > 0) {
    header("Location: ../../../messenger.php");
    exit;
}

// Step 3: Füge den Kontakt hinzu
$stmt = $mysql->prepare("INSERT INTO contacts (owner_id, contact_id) VALUES (:owner, :contact)");
$stmt->execute(['owner' => $ownerChatUserId, 'contact' => $contactId]);

header("Location: ../../../messenger.php");
exit;
?>
