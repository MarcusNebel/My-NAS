<?php
session_start();
if (!isset($_SESSION["id"])) {
    die("Nicht autorisiert.");
}

require("mysql.php");

// Benutzername abrufen
$stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
$stmt->execute(array(":id" => $_SESSION["id"]));
$username = $stmt->fetchColumn();

if (!$username) {
    die("Benutzername nicht gefunden.");
}

// Trash-Verzeichnis definieren
$root = "/home/nas-website-files/user_files/$username";

function listFolders($dir) {
    $folders = array_filter(glob($dir . '/*'), function ($folder) {
        return is_dir($folder) && basename($folder) !== 'trash';
    });

    foreach ($folders as $folder) {
        $relative = str_replace($GLOBALS['root'], '', $folder);
        $displayPath = $relative ?: '/';

        echo "<div class='folder-option' data-path='$relative'>" . htmlspecialchars($displayPath) . "</div>";
        listFolders($folder); // rekursiv
    }
}
listFolders($root);
?>
