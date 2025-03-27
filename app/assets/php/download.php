<?php
session_start();
if (!isset($_SESSION["id"]) || !isset($_GET['file'])) {
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

// Datei validieren
$file = basename($_GET['file']);
$directory = "/home/nas-website-files/user_files/$username/";
$filePath = $directory . $file;

if (!file_exists($filePath)) {
    die("Datei nicht gefunden.");
}

// Datei als Download senden
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit();
?>
