<?php
// download.php?path=uploads/sir%20jason%20-%20week%201%262.zip

// Optional: Zugangsschutz hier einbauen (Session-Check, Auth, ...)

// Basis-Upload-Ordner (bitte anpassen)
$baseDir = '/home/nas-website-files/';
$uploadDir = $baseDir . 'uploads/';

// Pfad holen und absichern
if (!isset($_GET['path'])) {
    http_response_code(400);
    echo "Kein Pfad übergeben!";
    exit;
}

$relativePath = $_GET['path'];

// Verhindere Directory Traversal
if (strpos($relativePath, '..') !== false) {
    http_response_code(400);
    echo "Ungültiger Pfad!";
    exit;
}

// Absoluten Dateipfad bestimmen
$filePath = realpath($baseDir . $relativePath);

// Prüfen, ob Datei existiert und im uploads-Ordner liegt
if (
    !$filePath ||
    strpos($filePath, realpath($uploadDir)) !== 0 ||
    !is_file($filePath)
) {
    http_response_code(404);
    echo "Datei nicht gefunden oder ungültiger Zugriff!";
    exit;
}

// Dateiname für Download extrahieren
$fileName = basename($filePath);

// Header setzen für Download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . rawurlencode($fileName) . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');
ob_clean();
flush();

// Datei ausgeben
readfile($filePath);
exit;
?>