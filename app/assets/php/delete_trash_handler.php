<?php
session_start();
if (!isset($_SESSION["id"])) {
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

// Basisverzeichnis definieren
$baseDir = "/home/nas-website-files/user_files/$username/trash";

// Überprüfen, ob die Anfrage eine POST-Anfrage ist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Empfange die übermittelten Dateien, Ordnernamen und den Pfad
    $files = $_POST['files'] ?? [];
    $folders = $_POST['folders'] ?? [];
    $path = $_GET['path'] ?? '';

    // Pfad bereinigen und zusammensetzen
    $relativePath = trim($path, '/');
    $fullPath = rtrim($baseDir, '/') . '/' . $relativePath;

    // Sicherheitsprüfung: Existiert der Pfad?
    if (!is_dir($fullPath)) {
        die("Pfad nicht korrekt: " . htmlspecialchars($fullPath));
    }

    // Funktion zum rekursiven Löschen des Ordners
    function deleteFolder($folderPath) {
        if (is_dir($folderPath)) {
            $items = array_diff(scandir($folderPath), ['.', '..']);
            foreach ($items as $item) {
                $itemPath = $folderPath . DIRECTORY_SEPARATOR . $item;
                is_dir($itemPath) ? deleteFolder($itemPath) : unlink($itemPath);
            }
            return rmdir($folderPath);
        }
        return false;
    }

    // Dateien löschen
    foreach ($files as $file) {
        $filePath = $fullPath . '/' . $file;

        // Sicherheitsprüfung: Datei existiert
        if (!file_exists($filePath)) {
            echo "Datei existiert nicht: " . htmlspecialchars($file) . "<br>";
            continue;
        }

        // Datei löschen
        if (!unlink($filePath)) {
            echo "Fehler beim Löschen der Datei: " . htmlspecialchars($file) . "<br>";
        }
    }

    // Ordner löschen
    foreach ($folders as $folder) {
        $folder = basename($folder); // Entfernt unsichere Pfadbestandteile
        $folderPath = $fullPath . '/' . $folder;

        // Existiert der Ordner?
        if (!file_exists($folderPath)) {
            echo "Der Ordner existiert nicht: " . htmlspecialchars($folder) . "<br>";
            continue;
        }

        // Sicherheitsprüfung: Pfad validieren
        $realFolderPath = realpath($folderPath);
        if (!$realFolderPath || strpos($realFolderPath, $baseDir) !== 0) {
            echo "Ungültiger Pfad: " . htmlspecialchars($folder) . "<br>";
            continue;
        }

        // Ordner löschen
        if (!deleteFolder($folderPath)) {
            echo "Fehler beim Löschen des Ordners: " . htmlspecialchars($folder) . "<br>";
        }
    }

    // Nach der Verarbeitung zurückleiten
    header("Location: ../../trash.php?path=$relativePath");
    exit;
} else {
    echo "Ungültige Anfrage. Bitte verwenden Sie eine POST-Anfrage.";
}