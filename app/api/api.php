<?php
header("Content-Type: application/json");

// MySQL-Verbindung
$servername = "db";
$username = "root";
$password = "59LWrt!mDo6GC4";
$dbname = "nas-website";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$response = [];

// 📌 1. Benutzer abrufen
$sql = "SELECT USERNAME FROM accounts"; 
$result = $conn->query($sql);

$users = [];
while($row = $result->fetch_assoc()) {
    $users[] = $row['USERNAME']; // Nur Benutzernamen speichern
}
$response["users"] = $users;

// 📌 2. Dateien abrufen
$uploadBaseDir = realpath(__DIR__ . '/../nas-website-files/user_files/');
$baseUrl = 'http://localhost:8443/nas-website-files/user_files/';

$filesByUser = [];

if ($uploadBaseDir && is_dir($uploadBaseDir)) {
    foreach ($users as $username) {
        $userDir = $uploadBaseDir . '/' . $username;

        if (is_dir($userDir)) {
            $files = array_values(array_diff(scandir($userDir), ['.', '..']));
            $fileUrls = array_map(fn($file) => $baseUrl . $username . '/' . urlencode($file), $files);
            $filesByUser[$username] = $fileUrls;
        } else {
            $filesByUser[$username] = [];
        }
    }
} else {
    $response["files"] = ["error" => "Upload-Verzeichnis existiert nicht."];
}

// 📌 3. JSON-Antwort zurückgeben
$response["files"] = $filesByUser;
$conn->close();

echo json_encode($response);
?>
