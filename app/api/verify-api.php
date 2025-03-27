<?php
require("mysql.php");

header("Content-Type: application/json");

// Prüfen, ob der API-Key per POST übergeben wurde
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["api_key"])) {
    $api_key = $_POST["api_key"];

    // API-Key in der Datenbank suchen
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE api_key = :api_key");
    $stmt->execute(array(":api_key" => $api_key));
    $row = $stmt->fetch();

    if ($row) {
        // Erfolgreiche Authentifizierung
        echo json_encode(["success" => true, "username" => $row["USERNAME"]]);
    } else {
        // API-Key ungültig
        echo json_encode(["success" => false, "message" => "Ungültiger API-Schlüssel."]);
    }
} else {
    // Fehlerhafte Anfrage
    echo json_encode(["success" => false, "message" => "Ungültige Anfrage."]);
}
?>
