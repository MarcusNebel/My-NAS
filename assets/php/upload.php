<?php
session_start();

// Überprüfe, ob der Benutzer eingeloggt ist
if (!isset($_SESSION["username"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"]; // Aktuelle Seite speichern
    header("Location: ../../Login/Login.php"); // Weiterleitung zur Login-Seite
    exit();
}

// Absoluter Pfad (anstelle von ~ verwenden)
$uploadDir = "/home/youruser/nas-website-files/user_files/";

// Erstelle das Verzeichnis, falls es nicht existiert
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Hole den Benutzernamen aus der Session
$username = $_SESSION["username"];

// Benutzer-spezifischer Ordner
$userDir = $uploadDir . $username . '/';

// Erstelle den Benutzer-Ordner, falls er nicht existiert
if (!file_exists($userDir)) {
    mkdir($userDir, 0777, true);
}

// Überprüfe, ob eine Datei hochgeladen wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Datei-Name und Ziel-Dateipfad
    $fileName = basename($_FILES['file']['name']);
    $targetFile = $userDir . $fileName;

    // Datei verschieben und Erfolg/Niederlage ausgeben
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo "Datei erfolgreich hochgeladen: " . htmlspecialchars($fileName);
    } else {
        echo "Fehler beim Hochladen der Datei.";
    }
} else {
    echo "Keine Datei hochgeladen.";
}
?>
