<?php
session_start();
if (isset($_POST['files']) && is_array($_POST['files'])) {
    $filesToDelete = $_POST['files'];

    // Den Benutzernamen aus der Session holen
    if(isset($_SESSION["id"])){
        require("mysql.php");
        $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
        $stmt->execute(array(":id" => $_SESSION["id"]));
        $username = $stmt->fetchColumn(); // Holt nur den USERNAME als String
    }

    // Überprüfen, ob der Benutzername gesetzt wurde
    if ($username) {
        $directory = "/home/nas-website-files/user_files/$username"; // Dynamischer Pfad basierend auf dem Benutzernamen

        foreach ($filesToDelete as $file) {
            $filePath = $directory . '/' . $file;

            // Überprüfen, ob die Datei existiert und löschen
            if (file_exists($filePath)) {
                unlink($filePath); // Löscht die Datei
            }
        }

        // Weiterleitung nach erfolgreichem Löschen
        header("Location: ../../User_Files.php");
        exit();
    } else {
        echo "Kein Benutzer angemeldet.";
    }
} else {
    echo "Keine Dateien ausgewählt.";
}
?>
