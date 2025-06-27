<?php
header('Content-Type: application/json');

// 1. Pfad holen
if (!isset($_GET['path'])) {
    echo json_encode(['error' => 'Kein Pfad übergeben!']);
    exit;
}

$relativePath = $_GET['path'];
$baseDir = '/home/nas-website-files/'; // Mit abschließendem /

// 2. Den echten Pfad berechnen (Path traversal verhindern!)
$filePath = realpath($baseDir . $relativePath);

// 3. Sicherheitscheck: Liegt $filePath wirklich IM baseDir?
if (!$filePath || strpos($filePath, realpath($baseDir)) !== 0) {
    echo json_encode(['error' => 'Ungültiger Dateipfad!']);
    exit;
}

// 4. Existiert die Datei?
if (!file_exists($filePath)) {
    echo json_encode(['error' => 'Datei nicht gefunden!']);
    exit;
}

// 5. Dateigröße holen
$fileSize = filesize($filePath);

echo json_encode([
    'size_bytes' => $fileSize,
    'size_formatted' => formatBytes($fileSize)
]);

function formatBytes($bytes, $precision = 1) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>