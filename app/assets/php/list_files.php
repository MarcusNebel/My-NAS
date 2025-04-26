<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Nur starten, wenn keine Sitzung aktiv ist
}

if (isset($_SESSION["id"])) {
    require("mysql.php");
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $username = $stmt->fetchColumn();
}

$baseDirectory = "/home/nas-website-files/user_files/";
$currentPath = isset($_GET['path']) ? $_GET['path'] : ''; // Aktueller Pfad

// Sicherstellen, dass $currentPath ein String ist
$currentPath = (string) $currentPath;

// Sicherheitsüberprüfung des Pfads
if (isset($username)) {
    // Kombiniere den Basisordner mit dem Benutzernamen und dem aktuellen Pfad
    $directory = $baseDirectory . $username . ($currentPath ? '/' . $currentPath : '');

    // Suche
    $search = isset($_GET['search']) ? strtolower($_GET['search']) : '';
    $filesAndDirs = array_diff(scandir($directory), array('.', '..')); // Verzeichnisse und Dateien scannen
    $filteredFiles = [];

    foreach ($filesAndDirs as $item) {
        $itemPath = $directory . "/" . $item;
        if ($search === '' || strpos(strtolower($item), $search) !== false) {
            // Filtere nach der Suchanfrage
            $filteredFiles[] = [
                'name' => $item,
                'is_dir' => is_dir($itemPath),
                'path' => $currentPath . ($currentPath ? '/' : '') . $item
            ];
        }
    }

    usort($filteredFiles, function ($a, $b) {
        // Zuerst: Ordner vor Dateien
        if ($a['is_dir'] && !$b['is_dir']) return -1;
        if (!$a['is_dir'] && $b['is_dir']) return 1;
    
        // Dann alphabetisch (unabhängig von Groß-/Kleinschreibung)
        return strcasecmp($a['name'], $b['name']);
    });        

    // Wenn Dateien oder Ordner gefunden wurden
    if (count($filteredFiles) > 0) {
        echo "<ul>"; // Beginne Liste von Dateien/Ordnern
        foreach ($filteredFiles as $file) {
            $filePath = $file['path'];
            if ($file['is_dir']) {
                // Ordner
                echo "<li class='file-item directory' data-path='" . htmlspecialchars($filePath) . "'>";
                echo "<input type='checkbox' class='file-checkbox' data-type='folder' value='" . htmlspecialchars($file['name']) . "'>";
                echo "<i class='bx bxs-folder'></i> " . htmlspecialchars($file['name']);
                echo "</li>";
            } else {
                // Datei
                $fileSize = filesize($directory . "/" . $file['name']);
                echo "<li class='file-item'>";
                echo "<input type='checkbox' name='files[]' value='" . htmlspecialchars($file['name']) . "' 
                        class='file-checkbox' 
                        data-type='file'
                        data-name='" . htmlspecialchars($file['name']) . "' 
                        data-size='" . $fileSize . "' 
                        data-path='" . htmlspecialchars($directory . "/" . $file['name']) . "'>";
                echo "<span class='file-name'> " . htmlspecialchars($file['name']) . "</span>";
                echo "</li>";
            }
        }
        echo "</ul>"; // Ende Liste
    } else {
        echo "<p id='no-files-text' style='margin-left: 16px;'>Keine Dateien oder Ordner gefunden.</p>";
    }
} else {
    // Weiterleitung, wenn kein Benutzer angemeldet ist
    header("Location: ../../User_Files.php");
    exit();
}
?>