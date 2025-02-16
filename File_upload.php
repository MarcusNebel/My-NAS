<?php
session_start();
if (!isset($_SESSION["username"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"]; // Speichert die aktuelle Seite
    header("Location: ../Login/Login.php"); // Weiterleitung zur Login-Seite
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Datei-Upload</title>
    <link rel="website icon" href="Logo.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700;900&display=swap" rel="stylesheet" />
    
    <link rel="stylesheet" href="/assets/css/style.css" />
</head>
<body>
    <header>
        <div class="container transparancy">
            <h2><a class="link-no-decoration" href="index.php"><span>MY </span>NAS</a></h2>
            <nav>
                <a href="index.php">Startseite</a>
                <a href="File_upload.php">Dateien</a>
                <a href="#">Bilder</a>
                <a href="#">Kontakt</a>
            </nav>
            <?php if (isset($_SESSION["username"])): ?>
                <button class="login_button">
                    <a href="../Login/logout.php">Abmelden</a>
                </button>
            <?php else: ?>
                <button class="login_button">
                    <a href="../Login/Login.php">Anmelden</a>
                </button>
            <?php endif; ?>
            <button class="hamburger">
                <div class="bar"></div>
            </button>
        </div>
    </header>
    <nav class="mobile-nav">
        <a href="/index.php">Startseite</a>
        <a href="/File_upload.php">Dateien</a>
        <a href="#">Bilder</a>
        <a href="#">Kontakt</a>
    </nav>
    <main>
        <section class="upload-section">
            <div class="container">
                <div class="upload-form">
                    <h1>Datei-Upload</h1>
                    <form id="uploadForm" action="./assets/php/upload.php" method="post" enctype="multipart/form-data">
                        <input type="file" id="fileInput" name="file" required>
                        <button type="submit">Hochladen</button>
                    </form>
                    <p id="uploadStatus"></p>

                    <!-- Fortschrittsbalken -->
                    <div id="progress-container" class="progress-container">
                        <div id="progress-bar" class="progress-bar">0%</div>
                    </div>

                    <!-- Anzeige der Upload-Geschwindigkeit -->
                    <p id="upload-speed" class="upload-speed">Upload-Geschwindigkeit: 0 MB/s</p>
                </div>
            </div>
        </section>
    </main>
    <script src="./assets/js/upload_info.js"></script>
</body>
</html>
