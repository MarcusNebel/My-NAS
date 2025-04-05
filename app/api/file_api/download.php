<?php
require_once "auth.php";

$api_key = $_GET['api_key'] ?? '';
$file_path = $_GET['path'] ?? ''; // Relativer Pfad, z.B. "Bilder/urlaub.jpg"

$username = authenticateUser($api_key);
$base = realpath("/home/nas-website-files/user_files/$username");
$file = $base . '/' . $file_path;

if (!file_exists($file) || is_dir($file)) {
    http_response_code(404);
    echo json_encode(["error" => "Datei nicht gefunden"]);
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
?>
