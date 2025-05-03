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
if (!is_dir($user_folder)) {
    mkdir($user_folder, 0755, true);
    chmod($user_folder, 0755);
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    elseif ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    elseif ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    elseif ($bytes > 1) return $bytes . ' Bytes';
    elseif ($bytes == 1) return '1 Byte';
    else return '0 Bytes';
}

if (isset($username)) {
    $directory = $user_folder;

    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $files[] = [
                'name' => $file->getFilename(),
                'path' => str_replace($directory . '/', '', $file->getPathname()),
                'size' => $file->getSize(),
                'mtime' => $file->getMTime(),
            ];
        }
    }

    // Nur die 10 neuesten Dateien
    usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
    $files = array_slice($files, 0, 5);

    if (count($files) > 0) {
        echo "<ul>";
        foreach ($files as $file) {
            $filePath = $file['path'];
            $filename = $file['name'];
            $sizeFormatted = formatFileSize($file['size']);
            $dateFormatted = date("d.m.Y H:i:s", $file['mtime']);
            $fullPath = $directory . "/" . $filePath;

            echo "<li class='file-item'>";
            echo "<input type='hidden' name='files[]' value='" . htmlspecialchars($filename) . "' 
                    class='file-checkbox' 
                    data-type='file'
                    data-name='" . htmlspecialchars($filename) . "' 
                    data-size='" . $file['size'] . "' 
                    data-full-path='" . htmlspecialchars($fullPath) . "'>";
            echo "<span class='file-name'> " . htmlspecialchars($filename) . "</span>";
            echo '<br class="desktop-only">';
            echo "<span class='file-info'>" . $sizeFormatted . " | " . $dateFormatted . "</span>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p id='no-files-text' style='margin-left: 16px;'>Keine zuletzt verwendeten Dateien gefunden.</p>";
    }
} else {
    header("Location: ../../User_Files.php");
    exit();
}
?>
