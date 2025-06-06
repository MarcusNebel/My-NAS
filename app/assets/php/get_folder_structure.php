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

// Root-Verzeichnis des Benutzers
$root = "/home/nas-website-files/user_files/$username";

function listFolders($dir) {
    if (basename($dir) === 'trash') return;

    $relative = str_replace($GLOBALS['root'], '', $dir);
    
    // Wenn das relative Verzeichnis leer ist, ist es das Root – setze "/"
    $relativePath = $relative !== '' ? $relative : '/';
    $displayPath = $relativePath;

    echo "<div class='folder-option' data-path='" . htmlspecialchars($relativePath, ENT_QUOTES) . "'>" . htmlspecialchars($displayPath) . "</div>";

    $folders = array_filter(glob($dir . '/*'), function ($folder) {
        return is_dir($folder) && basename($folder) !== 'trash';
    });

    foreach ($folders as $folder) {
        listFolders($folder); // rekursiv
    }
}

listFolders($root);
?>
