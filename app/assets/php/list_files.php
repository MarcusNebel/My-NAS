<?php
if (isset($_SESSION["id"])) {
    require("mysql.php");
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $username = $stmt->fetchColumn();
}

if ($username) {
    $directory = "/home/nas-website-files/user_files/$username";
    if (is_dir($directory)) {
        $files = array_diff(scandir($directory), array('.', '..'));

        echo "<p id='no-files-text' style='margin-left: 16px; display: none;'>Keine Dateien vorhanden.</p>"; // Immer da, aber evtl. unsichtbar

        if (count($files) > 0) {
            foreach ($files as $file) {
                $filePath = $directory . "/" . $file;
                $fileSize = filesize($filePath);
                echo "<li class='file-item'>";
                echo "<input type='checkbox' name='files[]' value='$file' 
                            class='file-checkbox' 
                            data-name='$file' 
                            data-size='$fileSize' 
                            data-path='$filePath'> $file";
                echo "</li>";
            }
        } else {
            echo "<script>document.getElementById('no-files-text').style.display = 'block';</script>";
        }
    } else {
        echo "<p id='no-files-text'>Der Benutzerordner $username existiert nicht.</p>";
    }
} else {
    echo "<p id='no-files-text'>Kein Benutzer angemeldet.</p>";
}
?>
