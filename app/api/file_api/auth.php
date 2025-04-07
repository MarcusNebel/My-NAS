<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../mysql.php';

// Funktion um den API-Key aus den Headern sicher zu holen
function getApiKeyFromHeaders() {
    $headers = getallheaders();

    // Normalfall: Flutter schickt den API-Key im Authorization-Header
    if (isset($headers['Authorization'])) {
        return $headers['Authorization'];
    }

    // Fallback: manchmal heißen die Keys anders (z. B. lowercase, nginx-Fallbacks)
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            return $value;
        }
    }

    return null;
}

function authenticateUser() {
    global $mysql;

    $api_key = getApiKeyFromHeaders();

    if (!$api_key) {
        http_response_code(401);
        echo json_encode(["error" => "Kein API-Key"]);
        exit;
    }

    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE api_key = ?");
    $stmt->execute([$api_key]);

    $username = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$username) {
        http_response_code(401);
        echo json_encode(["error" => "Ungültiger API-Schlüssel"]);
        exit;
    }

    return $username['USERNAME'];
}

function getUserBasePath($username) {
    $path = '/home/nas-website-files/user_files/' . $username;
    error_log("🔍 Checke Pfad: " . $path);
    $base = realpath($path);

    error_log("📁 realpath result: " . ($base ? $base : 'false'));

    if (!$base || !is_dir($base)) {
        http_response_code(500);
        echo json_encode(["error" => "Benutzerverzeichnis nicht gefunden"]);
        exit;
    }

    return $base;
}

function resolveUserPath($base, $relative) {
    $target = realpath($base . '/' . $relative);
    if (strpos($target, $base) !== 0) {
        http_response_code(400);
        echo json_encode(["error" => "Ungültiger Pfad"]);
        exit;
    }
    return $target;
}
?>