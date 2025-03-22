<?php
session_start();

// Überprüfe, ob der Benutzer eingeloggt ist
if (!isset($_SESSION["id"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"];
    header("Location: ../../account-system/Login.php");
    exit();
}

// Datenbankverbindung
require("mysql.php");
$stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
$stmt->execute(array(":id" => $_SESSION["id"]));
$username = $stmt->fetchColumn(); // ✅ Fix: Nur den String holen

if (!$username) {
    die("Fehler: Benutzer nicht gefunden.");
}

// Basisverzeichnis für Uploads
$uploadDir = "/home/nas-website-files/user_files/";
$userDir = $uploadDir . $username . "/";

// Falls Benutzerverzeichnis nicht existiert, erstelle es
if (!is_dir($userDir)) {
    mkdir($userDir, 0775, true);
    chmod($userDir, 0775); // ✅ Fix: Keine chown()/chgrp(), sondern chmod()
}

// Datei-Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']); // ✅ Sicherheitsfix gegen Pfadangriffe
    $targetFile = $userDir . $fileName;

    // Dateityp validieren
    $allowedTypes = ['image/png', 'image/jpeg', 'application/pdf', 'text/plain'];
    $fileType = mime_content_type($_FILES['file']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        die("Fehler: Ungültiger Dateityp.");
    }

    // Datei verschieben
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo "Datei erfolgreich hochgeladen: " . htmlspecialchars($fileName);
    } else {
        echo "Fehler beim Hochladen der Datei.";
    }
} else {
    echo "Keine Datei hochgeladen.";
}
?>
