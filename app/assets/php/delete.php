<?php
session_start();

// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['id'])) {
    die("Fehler: Nicht eingeloggt.");
}

// Datenbankverbindung
require("mysql.php");
$stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
$stmt->execute(array(":id" => $_SESSION["id"]));
$username = $stmt->fetchColumn(); // Richtiger Benutzername

if (!$username) {
    die("Fehler: Benutzer nicht gefunden.");
}

// Prüfen, ob Dateiname übergeben wurde
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Fehler: Keine Datei angegeben.");
}

// Sicherheitsfix: Nur den Dateinamen verwenden (keine Verzeichnistricks möglich)
$file = basename($_GET['file']);
$file_path = "/home/nas-website-files/user_files/$username/$file"; // Sicherer Pfad

// Überprüfen, ob die Datei existiert
if (!file_exists($file_path)) {
    header("Location: ../../User_Files.php");
}

// Datei löschen
if (unlink($file_path)) {
    header("Location: ../../User_Files.php");
    exit();
} else {
    echo "Fehler: Datei konnte nicht gelöscht werden.";
}
?>
