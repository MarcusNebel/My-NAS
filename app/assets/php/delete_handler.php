<?php
session_start();
if (!isset($_SESSION["id"]) || !isset($_POST['files'])) {
    die("Nicht autorisiert.");
}

require("mysql.php");

// Benutzername abrufen
$stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
$stmt->execute(array(":id" => $_SESSION["id"]));
$username = $stmt->fetchColumn();

if (!$username) {
    die("Benutzername nicht gefunden.");
}

// Den path-Parameter aus der URL holen, falls vorhanden
$path = isset($_GET['path']) ? $_GET['path'] : '';  // Den Pfad aus der URL

// Verzeichnis, in dem sich die Dateien befinden
$directory = "/home/nas-website-files/user_files/$username/$path/";  // Den Pfad in den Dateipfad einfügen
$deletedFiles = [];

foreach ($_POST['files'] as $file) {
    $filePath = $directory . basename($file);
    
    if (file_exists($filePath) && unlink($filePath)) {
        $deletedFiles[] = $file;
    } else {
        echo "Pfad nicht korrekt:";
        echo ($filePath);
    }
}

// Erfolgsnachricht ausgeben und zurück zur Dateiliste leiten
if (!empty($deletedFiles)) {
    $_SESSION['message'] = "Die ausgewählten Dateien wurden erfolgreich gelöscht.";
} else {
    $_SESSION['message'] = "Fehler beim Löschen der Dateien.";
}

header("Location: ../../User_Files.php");
exit();
?>
