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
            echo "<input type='checkbox' name='files[]' value='$file' class='file-checkbox'> $file";
            echo "</li>";
        }
    } else {
        echo "Der Benutzerordner $username existiert nicht.";
    }
} else {
    echo "Kein Benutzer angemeldet.";
}
?>
