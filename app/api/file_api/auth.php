<?php
require_once "../mysql.php"; // DB-Verbindung etc.

function authenticateUser($api_key) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT USERNAME FROM accounts WHERE api_key = ?");
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
    $base = realpath("/home/nas-website-files/user_files/$username");
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
