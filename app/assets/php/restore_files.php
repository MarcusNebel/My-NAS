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

// Überprüfen, ob die Anfrage eine POST-Anfrage ist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $files = $_POST['files'] ?? [];
    $folders = $_POST['folders'] ?? [];
    $path = $_GET['path'] ?? '';

    $relativePath = trim($path, '/');

    function moveBackWithStructure($sourcePath, $trashDir, $baseDir) {
        $relative = ltrim(str_replace($trashDir, '', $sourcePath), '/');
        $destinationPath = $baseDir . '/' . $relative;

        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0775, true);
        }

        $finalPath = $destinationPath;
        $i = 1;
        while (file_exists($finalPath)) {
            $finalPath = $destinationPath . "_$i";
            $i++;
        }

        return rename($sourcePath, $finalPath);
    }

    // Dateien wiederherstellen
    foreach ($files as $file) {
        // Dateipfad mit Pfadangabe kombinieren
        $filePath = $trashDir . '/' . $relativePath . '/' . $file;

        if (!file_exists($filePath)) {
            echo "Datei existiert nicht im Trash: " . htmlspecialchars($filePath) . "<br>";
            continue;
        }

        if (!moveBackWithStructure($filePath, $trashDir, $baseDir)) {
            echo "Fehler beim Wiederherstellen der Datei: " . htmlspecialchars($filePath) . "<br>";
        }
    }

    // Ordner wiederherstellen
    foreach ($folders as $folder) {
        $folderPath = $trashDir . '/' . $relativePath . '/' . $folder;

        if (!file_exists($folderPath)) {
            echo "Ordner existiert nicht im Trash: " . htmlspecialchars($folderPath) . "<br>";
            continue;
        }

        $realFolderPath = realpath($folderPath);
        if (!$realFolderPath || strpos($realFolderPath, $trashDir) !== 0) {
            echo "Ungültiger Pfad im Trash: " . htmlspecialchars($folderPath) . "<br>";
            continue;
        }

        if (!moveBackWithStructure($folderPath, $trashDir, $baseDir)) {
            echo "Fehler beim Wiederherstellen des Ordners: " . htmlspecialchars($folderPath) . "<br>";
        }
    }

    header("Location: ../../User_Files.php?path=" . urlencode($relativePath));
    exit;
} else {
    echo "Ungültige Anfrage. Bitte verwenden Sie eine POST-Anfrage.";
}
?>
