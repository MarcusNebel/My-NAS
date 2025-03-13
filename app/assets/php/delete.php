<?php
session_start();

// Angenommen, der Benutzername wird in der Session gespeichert
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Überprüfen, ob der Benutzername gesetzt wurde
if ($username && isset($_GET['file'])) {
    $file = $_GET['file'];
    $file_path = "/home/nas-website-files/user_files/$username/$file"; // Dynamischer Pfad basierend auf dem Benutzernamen

    // Überprüfen, ob die Datei existiert
    if (file_exists($file_path)) {
        // Datei löschen
        if (unlink($file_path)) {
            header('Location: ../../User_Files.php');
            exit;
        } else {
            echo "Es gab ein Problem beim Löschen der Datei.";
        }
    } else {
        echo "Die angeforderte Datei existiert nicht.";
    }
} else {
    echo "Kein Benutzer angemeldet oder keine Datei angegeben.";
}
?>
