<?php
session_start();
if (!isset($_SESSION["id"])) {
    die("Nicht autorisiert.");
}

require("mysql.php");

// Benutzername abrufen
$stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
$stmt->execute([":id" => $_SESSION["id"]]);
$username = $stmt->fetchColumn();

if (!$username) {
    die("Benutzername nicht gefunden.");
}

$root = "/home/nas-website-files/user_files/$username";

// Parameter auslesen
$currentPath = $_GET["path"] ?? '';
$target = $_POST["target"] ?? '';
$targetPath = rtrim($root . '/' . ltrim($target, '/'), '/');
$files = $_POST["files"] ?? [];
$folders = $_POST["folders"] ?? [];

// Absoluter Quell- und Zielpfad
$sourceBase = $root . "/" . $currentPath;

// File to root-driverctory if targetPath is empty
if (empty($target)) {
    $targetPath = $root;  // Fallback: Standardverzeichnis
}

if (!$sourceBase || strpos($sourceBase, $root) !== 0) {
    die("Ungültiger Quellpfad.");
}
if (strpos(realpath(realpath($targetPath)), $root) !== 0) {
    die("Ungültiger Zielpfad.");
}

// Funktion zum rekursiven Verschieben
function safeMove($source, $destination) {
    if (!file_exists($source)) return;

    if (is_dir($source)) {
        if (!file_exists(dirname($destination))) {
            mkdir(dirname($destination), 0775, true);
        }
        rename($source, $destination);
    } else {
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0775, true);
        }
        rename($source, $destination);
    }
}

// Dateien verschieben
foreach ($files as $file) {
    $sanitized = str_replace(['..', '\\', '//'], '', $file);
    $source = $sourceBase . '/' . ltrim($sanitized, '/');

    if (!file_exists($source)) {
        continue;
    }

    $dest = $targetPath . "/" . basename($sanitized);
    safeMove($source, $dest);
}

// Ordner verschieben
foreach ($folders as $folder) {
    $sanitized = str_replace(['..', '\\', '//'], '', $folder);
    $source = $sourceBase . '/' . ltrim($sanitized, '/');

    if (!file_exists($source) || !is_dir($source)) {
        continue;
    }

    $dest = $targetPath . "/" . basename($sanitized);
    safeMove($source, $dest);
}

header("Location: ../../User_Files.php?path=" . urlencode(ltrim($target, "/")));
exit;
