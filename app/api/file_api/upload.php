<?php
require_once "auth.php";

$api_key = $_POST['api_key'] ?? '';
$upload_path = $_POST['path'] ?? ''; // Relativer Pfad, z.B. "Bilder"

$username = authenticateUser($api_key);
$base = realpath("/home/nas-website-files/user_files/$username");
$target_dir = $base . '/' . $upload_path;

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["error" => "Keine Datei hochgeladen"]);
    exit;
}

$filename = basename($_FILES['file']['name']);
$target_file = $target_dir . '/' . $filename;

if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
    echo json_encode(["success" => true, "file" => $filename]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Hochladen"]);
}
?>
