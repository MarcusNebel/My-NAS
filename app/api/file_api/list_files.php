<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$username = authenticateUser();
$basePath = getUserBasePath($username);

$user_folder = "/home/nas-website-files/user_files/" . $username;
if (!is_dir($user_folder)) {
    mkdir($user_folder, 0755, true); // Ordner erstellen, falls er nicht existiert
    chmod($user_folder, 0755);
}

function scanDirectory($path, $relativePath = '') {
    $result = [];
    $items = new DirectoryIterator($path);

    foreach ($items as $item) {
        if ($item->isDot()) continue;

        $isDir = $item->isDir();
        $fullPath = $item->getPathname();
        $relativeItemPath = $relativePath . $item->getFilename();

        $entry = [
            'name' => $item->getFilename(),
            'path' => $relativeItemPath,
            'type' => $isDir ? 'folder' : 'file',
            'size' => $isDir ? null : $item->getSize(),
            'modified' => date("Y-m-d H:i:s", $item->getMTime())
        ];

        if ($isDir) {
            // Zähle Inhalte im Ordner (aber nicht rekursiv)
            $subItems = new FilesystemIterator($fullPath, FilesystemIterator::SKIP_DOTS);
            $entry['count'] = iterator_count($subItems);

            // Rekursiv die Unterordner holen und deren Größen berechnen
            $subfolderContents = scanDirectory($fullPath . '/', $relativeItemPath . '/');
            $result = array_merge($result, $subfolderContents);

            // Berechne die Gesamtgröße des Ordners
            $folderSize = 0;
            foreach ($subfolderContents as $subfile) {
                if ($subfile['type'] == 'file') {
                    $folderSize += $subfile['size'];
                }
            }
            $entry['size'] = $folderSize;
        }

        $result[] = $entry;
    }

    return $result;
}

$allFiles = scanDirectory($basePath);

echo json_encode($allFiles);