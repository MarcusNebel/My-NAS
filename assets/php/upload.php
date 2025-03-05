<?php
session_start();

// Überprüfe, ob der Benutzer eingeloggt ist
if (!isset($_SESSION["username"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"];
    header("Location: ../../Login/Login.php");
    exit();
}

// Basisverzeichnis für Uploads
$uploadDir = "/home/nas-website-files/user_files/";

// Falls Verzeichnis nicht existiert, erstelle es
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0775, true);
    chown($uploadDir, "www-data");
    chgrp($uploadDir, "www-data");
}

// Benutzerverzeichnis innerhalb von user_files
$username = $_SESSION["username"] ?? "guest";  // Falls kein Benutzername gesetzt ist, als "guest" speichern
$userDir = $uploadDir . $username . "/";

// Falls Benutzerverzeichnis nicht existiert, erstelle es
if (!file_exists($userDir)) {
    mkdir($userDir, 0775, true);
    chown($userDir, "www-data");
    chgrp($userDir, "www-data");
}

// Datei-Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $targetFile = $userDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo "Datei erfolgreich hochgeladen: " . $fileName;
    } else {
        echo "Fehler beim Hochladen der Datei.";
    }
} else {
    echo "Keine Datei hochgeladen.";
}
