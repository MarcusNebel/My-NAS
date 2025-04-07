<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$username = authenticateUser(); // Holt den User aus der DB via API-Key

$basePath = getUserBasePath($username);

// Für dieses Beispiel: Liste der Dateien im Benutzerverzeichnis
$files = [];
$dir = new DirectoryIterator($basePath);
foreach ($dir as $fileinfo) {
    if ($fileinfo->isDot()) continue;

    $files[] = [
        'name' => $fileinfo->getFilename(),
        'size' => $fileinfo->getSize(),
        'modified' => date("Y-m-d H:i:s", $fileinfo->getMTime()),
    ];
}

echo json_encode($files);
?>
