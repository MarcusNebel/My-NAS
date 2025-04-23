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
$baseDir = "/home/nas-website-files/user_files/$username";

// Überprüfen, ob die Anfrage eine POST-Anfrage ist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Empfange die übermittelten Ordnernamen und den Pfad
    $folders = $_POST['folders'] ?? [];
    $path = $_GET['path'] ?? '';

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

    foreach ($folders as $folder) {
        // Pfad bereinigen und zusammensetzen
        $folder = basename($folder); // Entfernt unsichere Pfadbestandteile
        $fullFolderPath = $baseDir . '/' . $path . '/' . $folder;

        // Existiert der Ordner?
        if (!file_exists($fullFolderPath)) {
            echo "Der Ordner existiert nicht: " . htmlspecialchars($folder) . "<br>";
            continue; // Zur nächsten Schleifeniteration gehen
        }

        // Sicherheitsprüfung: Pfad validieren
        $folderPath = realpath($fullFolderPath);
        if (!$folderPath || strpos($folderPath, $baseDir) !== 0) {
            echo "Ungültiger Pfad: " . htmlspecialchars($folder) . "<br>";
            continue; // Zur nächsten Schleifeniteration gehen
        }

        // Ordner löschen
        if (!deleteFolder($folderPath)) {
            echo "Fehler beim Löschen des Ordners: " . htmlspecialchars($folder) . "<br>";
        }
    }

    // Nach der Verarbeitung aller Ordner zurückleiten
    header("Location: ../../User_Files.php?path=$path");
    exit;
} else {
    echo "Ungültige Anfrage. Bitte verwenden Sie eine POST-Anfrage.";
}