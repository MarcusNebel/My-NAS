<?php
require_once "auth.php";

$api_key = $_POST['api_key'] ?? '';
$path = $_POST['path'] ?? '';

$username = authenticateUser($api_key);
$base = realpath("/home/nas-website-files/user_files/$username");
$target = $base . '/' . $path;

function deleteRecursive($path) {
    if (is_file($path)) {
        return unlink($path);
    } elseif (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            deleteRecursive("$path/$file");
        }
        return rmdir($path);
    }
    return false;
}

if (deleteRecursive($target)) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Löschen fehlgeschlagen"]);
}
?>
