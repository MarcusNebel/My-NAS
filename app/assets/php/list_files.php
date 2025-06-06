<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["id"])) {
    require("mysql.php");
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $username = $stmt->fetchColumn();
}

$user_folder = "/home/nas-website-files/user_files/" . $username;
$trash_folder = $user_folder . "/trash";

if (!is_dir($user_folder)) {
    mkdir($user_folder, 0755, true);
    chmod($user_folder, 0755);
}

// Prüfen und ggf. "trash"-Ordner anlegen
if (!is_dir($trash_folder)) {
    mkdir($trash_folder, 0755, true);
    chmod($trash_folder, 0755);
}

$baseDirectory = "/home/nas-website-files/user_files/";
$currentPath = isset($_GET['path']) ? $_GET['path'] : '';
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

if (isset($username)) {
    $directory = $baseDirectory . $username . ($currentPath ? '/' . $currentPath : '');
    $search = isset($_GET['search']) ? strtolower($_GET['search']) : '';
    $filesAndDirs = array_diff(scandir($directory), array('.', '..'));

    $filteredFiles = [];

    foreach ($filesAndDirs as $item) {
        // "trash"-Ordner überspringen (nur im Hauptverzeichnis des Users)
        if ($currentPath === '' && strtolower($item) === 'trash') {
            continue;
        }

        $itemPath = $directory . "/" . $item;
        $isDir = is_dir($itemPath);
        $modifiedTime = filemtime($itemPath);
        $formattedDate = date("d.m.Y H:i:s", $modifiedTime);

        if ($isDir) {
            $itemCount = count(array_diff(scandir($itemPath), array('.', '..')));
        } else {
            $fileSize = filesize($itemPath);
        }

        if ($search === '' || strpos(strtolower($item), $search) !== false) {
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

    $sort_by = $_GET['sort'] ?? 'type';  // Standard: nach Typ sortieren
    $sort_order = $_GET['order'] ?? 'asc'; // Standard: aufsteigend

    usort($filteredFiles, function ($a, $b) use ($sort_by, $sort_order) {
        // Wenn nach "type" sortiert wird: Ordner zuerst
        if ($sort_by === 'type') {
            if ($a['is_dir'] && !$b['is_dir']) return $sort_order === 'asc' ? -1 : 1;
            if (!$a['is_dir'] && $b['is_dir']) return $sort_order === 'asc' ? 1 : -1;
        }

        // Vergleichswert extrahieren
        $valueA = $a[$sort_by] ?? '';
        $valueB = $b[$sort_by] ?? '';

        // Typabhängige Konvertierung
        if ($sort_by === 'size' || $sort_by === 'item_count') {
            $valueA = (int) $valueA;
            $valueB = (int) $valueB;
        } elseif ($sort_by === 'date') {
            $valueA = strtotime($a['date'] ?? '1970-01-01');
            $valueB = strtotime($b['date'] ?? '1970-01-01');
        } else {
            $valueA = strtolower((string)$valueA);
            $valueB = strtolower((string)$valueB);
        }

        $result = $valueA <=> $valueB;
        return $sort_order === 'desc' ? -$result : $result;
    });

    if (count($filteredFiles) > 0) {
        echo "<ul>";
        foreach ($filteredFiles as $file) {
            $filePath = $file['path'];
            if ($file['is_dir']) {
                echo "<li class='file-item directory' data-path='" . htmlspecialchars($filePath) . "'>";
                echo "<input type='checkbox' class='file-checkbox' data-type='folder' data-full-path='" . htmlspecialchars($directory . "/" . $filePath) . "' value='" . htmlspecialchars($file['name']) . "'>";
                echo "<i class='bx bxs-folder'></i> " . htmlspecialchars($file['name']);
                echo '<br class="desktop-only">';
                echo "<span class='file-info'>" . $file['item_count'] . " Objekte | " . $file['date'] . "</span>";
                echo "</li>";
            } else {
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
        echo "</ul>";
    } else {
        echo "<p id='no-files-text' style='margin-left: 16px;'>Keine Dateien oder Ordner gefunden.</p>";
    }
} else {
    header("Location: ../../User_Files.php");
    exit();
}
?>
