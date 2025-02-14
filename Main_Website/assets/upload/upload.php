<?php
    $uploadDir = __DIR__ . 'Main_Website\Files';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $fileName = basename($_FILES['file']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            echo "Datei erfolgreich hochgeladen: " . $fileName;
        } else {
            echo "Fehler beim Hochladen der Datei.";
        }
    } else {
        echo "Keine Datei hochgeladen.";
    }
    ?>