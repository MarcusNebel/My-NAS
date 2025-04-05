<?php
require_once "auth.php";

$api_key = $_POST['api_key'] ?? '';
$path = $_POST['path'] ?? ''; // Relativer Pfad zur Datei
$new_name = $_POST['new_name'] ?? ''; // Neuer Name

$username = authenticateUser($api_key);
$base = realpath("/home/nas-website-files/user_files/$username");
$old_path = $base . '/' . $path;
$new_path = dirname($old_path) . '/' . $new_name;

if (rename($old_path, $new_path)) {
    echo json_encode(["success" => true, "new_name" => $new_name]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Umbenennen fehlgeschlagen"]);
}
?>
