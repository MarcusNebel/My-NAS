<?php
$uploadDir = __DIR__ . '/../../../user_files/'; // Absoluter Pfad relativ zu NAS-Website

// Überprüfen, ob das Verzeichnis existiert, falls nicht, erstellen
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo "Datei erfolgreich hochgeladen: " . $fileName;
    } else {
        echo "Fehler beim Hochladen der Datei nach: " . $targetFile;
    }
} else {
    echo "Keine Datei hochgeladen.";
}
?>
