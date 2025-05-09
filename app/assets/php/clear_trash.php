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

// Trash-Verzeichnis definieren
$trashDir = "/home/nas-website-files/user_files/$username/trash";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $path = $_GET['path'] ?? '';
    $relativePath = trim($path, '/');
    $fullPath = rtrim($trashDir, '/') . '/' . $relativePath;

    if (!is_dir($fullPath)) {
        die("Pfad nicht korrekt: " . htmlspecialchars($fullPath));
    }

    function deleteEverythingInFolder($dir) {
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                deleteEverythingInFolder($itemPath);
                rmdir($itemPath);
            } else {
                unlink($itemPath);
            }
        }
    }

    // Sicherheitsprüfung
    $realFullPath = realpath($fullPath);
    if (!$realFullPath || strpos($realFullPath, $trashDir) !== 0) {
        die("Ungültiger Pfad.");
    }

    // Löschen starten
    deleteEverythingInFolder($realFullPath);

    // Zurück zur Trash-Ansicht
    header("Location: ../../Trash.php?path=" . urlencode($relativePath));
    exit;
} else {
    echo "Ungültige Anfrage. Bitte verwenden Sie eine POST-Anfrage.";
}
