<?php
session_start();

// Prüfen, ob Benutzer eingeloggt ist
if (!isset($_SESSION['username'])) {
    die("Fehler: Nicht eingeloggt.");
}

$username = $_SESSION['username'];

// Prüfen, ob Dateiname übergeben wurde
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Fehler: Keine Datei angegeben.");
}

$filePath = '/home/nas-website-files/user_files/' . $username . '/' . $_GET['file'];

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
readfile($filePath);
exit;
?>
