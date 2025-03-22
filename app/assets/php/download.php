<?php
session_start();

// Prüfen, ob Benutzer eingeloggt ist
if (!isset($_SESSION['id'])) {
    die("Fehler: Nicht eingeloggt.");
}

require("mysql.php");
$stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
$stmt->execute(array(":id" => $_SESSION["id"]));
$username = $stmt->fetchColumn(); // Korrigiert, um den reinen String zu erhalten

if (!$username) {
    die("Fehler: Benutzer nicht gefunden.");
}

// Prüfen, ob Dateiname übergeben wurde
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Fehler: Keine Datei angegeben.");
}

// Sicherheitsmaßnahmen: Dateiname bereinigen
$fileName = basename($_GET['file']);
$filePath = "/home/nas-website-files/user_files/$username/$fileName";

// Prüfen, ob Datei existiert
if (!file_exists($filePath)) {
    die("Fehler: Datei nicht gefunden.");
}

// Sicherstellen, dass keine vorherige Ausgabe stört
if (ob_get_length()) ob_clean();

// Header setzen, um die Datei korrekt zu senden
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Datei sicher ausgeben
flush();
if (!readfile($filePath)) {
    die("Fehler: Datei konnte nicht gelesen werden.");
}

exit;
?>
