<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/auth.php';

$username = authenticateUser();
$basePath = getUserBasePath($username);

// 🔐 Sanitize input path
if (!isset($_GET['path'])) {
    http_response_code(400);
    echo "Missing 'path' parameter.";
    exit;
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['api_key'] ?? '';

if (empty($authHeader)) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$requestedPath = urldecode($_GET['path']);
$fullPath = realpath($basePath . '/' . $requestedPath);

// 🔒 Sicherheitsprüfung
if ($fullPath === false || strpos($fullPath, $basePath) !== 0) {
    http_response_code(403);
    echo "Invalid path.";
    exit;
}

if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// ✅ Datei ausliefern
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);

error_log("🔍 basePath: $basePath");
error_log("🔍 requestedPath: $requestedPath");
error_log("🔍 fullPath: $fullPath");
error_log("🔍 file_exists: " . (file_exists($fullPath) ? "yes" : "no"));
error_log("🔍 is_file: " . (is_file($fullPath) ? "yes" : "no"));

exit;