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
date_default_timezone_set("Europe/Berlin");
$zipName = "My-NAS_" . $username . date("_s-i-H_d-m-Y") . ".zip";
$zipPath = "/home/nas-website-files/tmp_zips/$zipName"; // Temporärer Speicherort

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

// Gib den Dateinamen der ZIP-Datei zurück, damit der Client den richtigen Namen verwenden kann
echo json_encode([
    'filePath' => "../../nas-website-files/tmp_zips/$zipName",  // Der Pfad zur ZIP-Datei
    'zipName' => $zipName  // Der Dateiname der ZIP-Datei
]);

exit();
?>
