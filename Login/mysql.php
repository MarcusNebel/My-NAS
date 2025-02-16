<?php
// Datenbank-Verbindungsdetails
$host = "localhost";        // Hostname oder IP-Adresse des MySQL-Servers
$dbname = "nas-website";    // Der Name deiner Datenbank
$user = "root";             // Dein MySQL-Benutzername
$password = "59LWrt!mDo6GC4";             // Dein MySQL-Passwort (leerlassen, wenn du kein Passwort verwendest)

// Datenbank-Verbindung herstellen
try {
    $mysql = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Setzen von PDO-Attributen für Fehlerbehandlung
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysql->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Rückgabe von Ergebnissen als assoziative Arrays
} catch (PDOException $e) {
    // Fehlerbehandlung
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>
