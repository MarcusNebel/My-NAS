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

$user_folder = "/home/nas-website-files/user_files/" . $username;
if (!is_dir($user_folder)) {
    mkdir($user_folder, 0755, true); // Ordner erstellen, falls er nicht existiert
    chmod($user_folder, 0755);
}

$baseDirectory = "/home/nas-website-files/user_files/";
$currentPath = isset($_GET['path']) ? $_GET['path'] : ''; // Aktueller Pfad

// Sicherstellen, dass $currentPath ein String ist
$currentPath = (string) $currentPath;

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' Bytes';
    } elseif ($bytes == 1) {
        return '1 Byte';
    } else {
        return '0 Bytes';
    }
}

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
        $isDir = is_dir($itemPath);
        $modifiedTime = filemtime($itemPath); // Letzte Änderungszeit
        $formattedDate = date("d.m.Y H:i:s", $modifiedTime); // Formatieren von Datum und Uhrzeit
        
        if ($isDir) {
            // Anzahl der Objekte im Ordner zählen
            $itemCount = count(array_diff(scandir($itemPath), array('.', '..')));
        } else {
            // Dateigröße
            $fileSize = filesize($itemPath);
        }

        if ($search === '' || strpos(strtolower($item), $search) !== false) {
            // Filtere nach der Suchanfrage
            $filteredFiles[] = [
                'name' => $item,
                'is_dir' => $isDir,
                'path' => $currentPath . ($currentPath ? '/' : '') . $item,
                'date' => $formattedDate,
                'size' => $isDir ? null : $fileSize,
                'item_count' => $isDir ? $itemCount : null
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
                echo "<input type='checkbox' class='file-checkbox' data-type='folder' data-full-path='" . htmlspecialchars($directory . "/" . $filePath) . "' value='" . htmlspecialchars($file['name']) . "'>";
                echo "<i class='bx bxs-folder'></i> " . htmlspecialchars($file['name']);
                echo '<br class="desktop-only">';
                echo "<span class='file-info'>" . $file['item_count'] . " Objekte | " . $file['date'] . "</span>";
                echo "</li>";
            } else {
                // Datei
                echo "<li class='file-item'>";
                echo "<input type='checkbox' name='files[]' value='" . htmlspecialchars($file['name']) . "' 
                        class='file-checkbox' 
                        data-type='file'
                        data-name='" . htmlspecialchars($file['name']) . "' 
                        data-size='" . $file['size'] . "' 
                        data-full-path='" . htmlspecialchars($directory . "/" . $file['name']) . "'>";
                echo "<span class='file-name'> " . htmlspecialchars($file['name']) . "</span>";
                echo '<br class="desktop-only">';
                echo "<span class='file-info'>" . formatFileSize($file['size']) . " | " . $file['date'] . "</span>";
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