<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ordnerName = trim($_POST["folderName"]);

    if (empty($ordnerName) || preg_match('/[\/\\\\]/', $ordnerName)) {
        http_response_code(400);
        echo "Ungültiger Ordnername.";
        exit;
    }

    if (!isset($_SESSION["id"])) {
        http_response_code(403);
        echo "Nicht eingeloggt.";
        exit;
    }

    require("mysql.php");
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute([":id" => $_SESSION["id"]]);
    $benutzer = $stmt->fetchColumn();

    $basisPfad = "/home/nas-website-files/user_files/$benutzer/";
    $vollPfad = $basisPfad . $ordnerName;

    if (!file_exists($vollPfad)) {
        if (mkdir($vollPfad, 0775, true)) {
            http_response_code(200);
            exit;
        } else {
            http_response_code(500);
            echo "Fehler beim Erstellen des Ordners.";
            exit;
        }
    } else {
        http_response_code(409); // Conflict
        echo "Ordner existiert bereits.";
        exit;
    }
}
http_response_code(405); // Methode nicht erlaubt
echo "Ungültige Anfrage.";
exit;
?>
