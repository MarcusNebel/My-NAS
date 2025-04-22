<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ordnerName = trim($_POST["folderName"]);
    $aktuellerPfad = isset($_GET["path"]) ? trim($_GET["path"]) : "";

    // Prüfen auf ungültige Zeichen im Ordnernamen
    if (empty($ordnerName) || preg_match('/[\/\\\\]/', $ordnerName)) {
        http_response_code(400);
        echo "Ungültiger Ordnername.";
        exit;
    }

    // Prüfen, ob der Benutzer eingeloggt ist
    if (!isset($_SESSION["id"])) {
        http_response_code(403);
        echo "Nicht eingeloggt.";
        exit;
    }

    // Benutzername aus der Datenbank abrufen
    require("mysql.php");
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute([":id" => $_SESSION["id"]]);
    $benutzer = $stmt->fetchColumn();

    // Basisverzeichnis des Benutzers
    $basisPfad = "/home/nas-website-files/user_files/$benutzer/";

    // Finalen Pfad berechnen
    $vollPfad = $basisPfad . ($aktuellerPfad ? $aktuellerPfad . '/' : '') . $ordnerName;

    // Sicherstellen, dass der Pfad innerhalb des Benutzerverzeichnisses bleibt
    $realBasisPfad = realpath($basisPfad);
    $realVollPfad = realpath(dirname($vollPfad));

    if (strpos($realVollPfad, $realBasisPfad) !== 0) {
        http_response_code(400);
        echo "Ungültiger Pfad.";
        exit;
    }

    // Ordner erstellen
    if (!file_exists($vollPfad)) {
        if (mkdir($vollPfad, 0775, true)) {
            http_response_code(200);
            echo "Ordner erfolgreich erstellt.";
            exit;
        } else {
            http_response_code(500);
            echo "Fehler beim Erstellen des Ordners.";
            exit;
        }
    } else {
        http_response_code(409); // Konflikt
        echo "Ordner existiert bereits.";
        exit;
    }
}

http_response_code(405); // Methode nicht erlaubt
echo "Ungültige Anfrage.";
exit;
?>