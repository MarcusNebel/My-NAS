<?php
header('Content-Type: application/json');

// Fehler-Reporting aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Session starten, um Benutzerinformationen zu erhalten

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Verbindung zur Datenbank herstellen (mysql.php importieren)
require_once 'mysql.php';

// Benutzerinformationen abrufen
$userId = $_SESSION['id'];
$stmt = $mysql->prepare("SELECT server_rank FROM accounts WHERE ID = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

// Prüfen, ob der Benutzer ein Admin ist
if (!$user || $user['server_rank'] !== "Admin") {
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

// Aktuelle Version aus der Datei lesen
$currentVersion = @file_get_contents('version.txt');
if ($currentVersion === false) {
    echo json_encode(['error' => 'version.txt file not found or inaccessible']);
    exit;
}
$currentVersion = trim($currentVersion); // Entferne Leerzeichen oder Zeilenumbrüche

// GitHub API aufrufen, um die neueste Version zu erhalten
$repoOwner = "MarcusNebel";
$repoName = "My-NAS";
$apiUrl = "https://api.github.com/repos/$repoOwner/$repoName/releases/latest";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'My-NAS-Update-Checker'); // User-Agent bleibt erforderlich
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'GitHub API request failed', 'http_code' => $httpCode]);
    exit;
}

$releaseData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON from GitHub API', 'response' => $response]);
    exit;
}

// Version aus dem Feld "name" holen
$latestVersion = trim($releaseData['name']); // Aktuelle Versionsnummer aus dem Feld "name"

// Release Notes aus dem Feld "body" holen
$releaseNotes = isset($releaseData['body']) ? trim($releaseData['body']) : "Keine Release Notes verfügbar.";

// Vergleich der Versionen
if (version_compare(ltrim($latestVersion, 'v'), ltrim($currentVersion, 'v'), '>')) {
    echo json_encode([
        'update_available' => true,
        'latest_version' => $latestVersion,
        'release_notes' => $releaseNotes // Markdown wird direkt weitergegeben
    ]);
} else {
    echo json_encode([
        'update_available' => false,
        'latest_version' => $latestVersion,
        'release_notes' => $releaseNotes
    ]);
}