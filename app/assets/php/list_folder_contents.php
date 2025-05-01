<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folderPath = $_POST['folderPath'] ?? '';

    if (empty($folderPath) || !is_dir($folderPath)) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültiger Ordnerpfad']);
        exit;
    }

    $response = [];
    $dir = new DirectoryIterator($folderPath);

    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $response[] = [
                'name' => $fileinfo->getFilename(),
                'path' => $fileinfo->getPathname(),
                'is_dir' => $fileinfo->isDir(),
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>