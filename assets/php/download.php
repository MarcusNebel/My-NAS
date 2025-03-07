<?php
// Session starten, um den Benutzernamen zu bekommen (falls du Sessions verwendest)
session_start();

// Benutzernamen aus der Session holen, falls der Benutzer eingeloggt ist
$username = $_SESSION['username']; // Oder $username direkt aus einem GET-Parameter, falls gewünscht

// Den Pfad zur Datei zusammenstellen
$filePath = '/home/nas-website-files/user_files/' . $username . '/' . $_GET['file'];

// Überprüfen, ob die Datei existiert
if (file_exists($filePath)) {
    // Den richtigen Header setzen, um den Download zu starten
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));

    // Datei ausgeben
    readfile($filePath);
    exit;
} else {
    echo "Die Datei ist nicht verfügbar.";
}
?>
