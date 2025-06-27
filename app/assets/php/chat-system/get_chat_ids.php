<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'mysql.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Nicht eingeloggt']);
    exit;
}

// Eigene chat_user_id holen:
$stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
$stmt->bindParam(":id", $_SESSION['id']);
$stmt->execute();
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
$owner_id = $userRow['chat_user_id'] ?? null;

if (!$owner_id) {
    echo json_encode(['error' => 'Kein chat_user_id für eigenen Account']);
    exit;
}

// Kontakt-ID holen: Entweder per username oder per chat_user_id
// Beispiel: Via POST 'contact_username' schicken
$contact_id = null;
if (isset($_POST['contact_username'])) {
    $stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE USERNAME = :username");
    $stmt->bindParam(":username", $_POST['contact_username']);
    $stmt->execute();
    $contactRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $contact_id = $contactRow['chat_user_id'] ?? null;
} elseif (isset($_POST['contact_id'])) {
    $contact_id = $_POST['contact_id'];
}

if (!$contact_id) {
    echo json_encode(['error' => 'Kein contact_id angegeben oder gefunden']);
    exit;
}

// Optional: Prüfen, ob Kontakt in contacts-Tabelle existiert
$stmt = $mysql->prepare("SELECT 1 FROM contacts WHERE (owner_id = :owner AND contact_id = :contact) OR (owner_id = :contact AND contact_id = :owner)");
$stmt->execute(['owner' => $owner_id, 'contact' => $contact_id]);
if (!$stmt->fetchColumn()) {
    echo json_encode(['error' => 'Kein Kontakt zwischen owner_id und contact_id vorhanden']);
    exit;
}

// Erfolg:
echo json_encode([
    'owner_id' => $owner_id,
    'contact_id' => $contact_id
]);