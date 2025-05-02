<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht angemeldet']);
    exit;
}

require("mysql.php");
$stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
$stmt->execute([':id' => $_SESSION['id']]);
$username = $stmt->fetchColumn();

if (!$username) {
    echo json_encode(['success' => false, 'message' => 'Benutzer nicht gefunden']);
    exit;
}

$baseDir = "/home/nas-website-files/user_files/" . $username . "/";
$fullPath = $_POST['path'] ?? '';
$oldName = basename($_POST['old_name'] ?? '');
$newName = basename($_POST['new_name'] ?? '');

if (!$oldName || !$newName) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Eingaben']);
    exit;
}

$oldPath = $fullPath;
$newPath = dirname($oldPath) . "/" . $newName;

// Überprüfen, ob der neue Name bereits existiert und eine Zahl in Klammern hinzufügen, falls notwendig
$counter = 1;
$baseName = pathinfo($newName, PATHINFO_FILENAME); // Der Basisname ohne die Dateiendung
$extension = pathinfo($newName, PATHINFO_EXTENSION); // Die Dateiendung

// Wenn es keine Dateiendung gibt, z.B. bei Ordnern
if (!$extension) {
    $extension = ''; // Leerzeichen für Ordnernamen
}

// Solange der Name existiert, füge eine Zahl in Klammern hinzu
while (file_exists($newPath)) {
    $newName = $baseName . " (" . $counter . ")" . ($extension ? "." . $extension : '');
    $newPath = dirname($oldPath) . "/" . $newName;
    $counter++;
}

if (!file_exists($oldPath)) {
    echo json_encode(['success' => false, 'message' => 'Originaldatei nicht gefunden']);
    exit;
}

if (rename($oldPath, $newPath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Umbenennen']);
}
