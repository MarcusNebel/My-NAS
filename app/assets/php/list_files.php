<?php
if(isset($_SESSION["id"])){
    require("mysql.php");
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $username = $stmt->fetchColumn(); // Holt nur den USERNAME als String
}

// Überprüfen, ob der Benutzername gesetzt wurde
if ($username) {
    $directory = "/home/nas-website-files/user_files/$username"; // Dynamischer Pfad basierend auf dem Benutzernamen

    // Überprüfen, ob der Ordner existiert
    if (is_dir($directory)) {
        $files = scandir($directory); // Listet alle Dateien im Ordner auf
        $files = array_diff($files, array('.', '..')); // Entfernt '.' und '..'

        // Liste der Dateien ausgeben
        foreach ($files as $file) {
            echo "<li class='file-item'>";
            // Download-Link zu download.php mit dem Dateinamen als GET-Parameter
            echo "$file - ";
            echo "<a href='assets/php/download.php?file=$file' download>Herunterladen</a>";
            // Löschen-Link
            echo " | <a href='assets/php/delete.php?file=$file' onclick='return confirm(\"Möchten Sie diese Datei wirklich löschen?\")'>Löschen</a>";
            echo "</li>";
        }
    } else {
        echo "Der Benutzerordner $username existiert nicht.";
    }
} else {
    echo "Kein Benutzer angemeldet.";
}
?>