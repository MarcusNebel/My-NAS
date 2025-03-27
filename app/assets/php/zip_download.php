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

$directory = "/home/nas-website-files/user_files/$username/";
$zipName = "MyFiles_" . date("Y-m-d_H-i-s") . ".zip";
$zipPath = "/tmp/" . $zipName; // Temporärer Speicherort

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    die("ZIP-Datei konnte nicht erstellt werden.");
}

// Dateien hinzufügen
foreach ($_POST['files'] as $file) {
    $filePath = $directory . basename($file);
    if (file_exists($filePath)) {
        $zip->addFile($filePath, basename($file));
    }
}

$zip->close();

// ZIP-Datei zum Download bereitstellen
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);

// ZIP-Datei nach dem Download löschen
unlink($zipPath);
exit();
?>
