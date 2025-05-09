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

// Basisverzeichnis und Trash-Verzeichnis definieren
$baseDir = "/home/nas-website-files/user_files/$username";
$trashDir = $baseDir . "/trash";

// Trash-Verzeichnis erstellen, falls es nicht existiert
if (!is_dir($trashDir)) {
    mkdir($trashDir, 0775, true);
}

// Überprüfen, ob die Anfrage eine POST-Anfrage ist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $files = $_POST['files'] ?? [];
    $folders = $_POST['folders'] ?? [];
    $path = $_GET['path'] ?? '';

    $relativePath = trim($path, '/');
    $fullPath = rtrim($baseDir, '/') . '/' . $relativePath;

    if (!is_dir($fullPath)) {
        die("Pfad nicht korrekt: " . htmlspecialchars($fullPath));
    }

    function moveWithStructure($sourcePath, $baseDir, $trashDir) {
        // Relativen Pfad berechnen (relativ zum baseDir)
        $relative = ltrim(str_replace($baseDir, '', $sourcePath), '/');

        // Zielpfad im Trash-Verzeichnis
        $destinationPath = $trashDir . '/' . $relative;

        // Übergeordnete Ordnerstruktur im Trash erstellen
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0775, true);
        }

        // Falls Ziel schon existiert, neuen Namen finden
        $finalPath = $destinationPath;
        $i = 1;
        while (file_exists($finalPath)) {
            $finalPath = $destinationPath . "_$i";
            $i++;
        }

        return rename($sourcePath, $finalPath);
    }

    // Dateien verschieben
    foreach ($files as $file) {
        $filePath = $fullPath . '/' . basename($file);

        if (!file_exists($filePath)) {
            echo "Datei existiert nicht: " . htmlspecialchars($file) . "<br>";
            continue;
        }

        if (!moveWithStructure($filePath, $baseDir, $trashDir)) {
            echo "Fehler beim Verschieben der Datei: " . htmlspecialchars($file) . "<br>";
        }
    }

    // Ordner verschieben
    foreach ($folders as $folder) {
        $folderPath = $fullPath . '/' . basename($folder);

        if (!file_exists($folderPath)) {
            echo "Ordner existiert nicht: " . htmlspecialchars($folder) . "<br>";
            continue;
        }

        $realFolderPath = realpath($folderPath);
        if (!$realFolderPath || strpos($realFolderPath, $baseDir) !== 0) {
            echo "Ungültiger Pfad: " . htmlspecialchars($folder) . "<br>";
            continue;
        }

        if (!moveWithStructure($folderPath, $baseDir, $trashDir)) {
            echo "Fehler beim Verschieben des Ordners: " . htmlspecialchars($folder) . "<br>";
        }
    }

    header("Location: ../../User_Files.php?path=$relativePath");
    exit;
} else {
    echo "Ungültige Anfrage. Bitte verwenden Sie eine POST-Anfrage.";
}
