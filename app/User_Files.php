<?php
session_start();
if (!isset($_SESSION["id"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"]; // Speichert die aktuelle Seite
    header("Location: account-system/Login.php"); // Weiterleitung zur Login-Seite
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Meine Dateien</title>
    <link rel="website icon" href="Logo/Logo_512px.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="assets/css/style.css" />
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container transparancy">
            <h2><a class="link-no-decoration" href="index.php"><span>MY </span>NAS</a></h2>
            <nav>
                <a class="hover-underline-animation left" href="index.php">Startseite</a>
                <a class="hover-underline-animation left" href="User_Files.php">Meine Dateien</a>
                <a class="hover-underline-animation left" href="File_upload.php">Dateien hochladen</a>
                <?php if(isset($_SESSION["id"])): ?>
                    <a class="hover-underline-animation left" href="account-system/account.php">Mein Account</a>
                <?php else: ?>
                    <a class="hover-underline-animation left" href="account-system/Login.php">Mein Account</a>
                <?php endif; ?>
                <a class="hover-underline-animation left" href="Contact_Page/Contact_Page.php">Kontakt</a>
            </nav>
            <?php if (isset($_SESSION["id"])): ?>
                <a class="login_button" href="account-system/logout.php">Abmelden</a>
            <?php else: ?>
                <a class="login_button" href="account-system/Login.php">Anmelden</a>
            <?php endif; ?>
            <button class="hamburger">
                <div class="bar"></div>
            </button>
        </div>
    </header>
    <nav class="mobile-nav">
        <a href="index.php">Startseite</a>
        <a href="User_Files.php">Meine Dateien</a>
        <a href="File_upload.php">Dateien hochladen</a>
        <?php if(isset($_SESSION["id"])): ?>
            <a href="account-system/account.php">Mein Account</a>
        <?php else: ?>
            <a href="account-system/Login.php">Mein Account</a>
        <?php endif; ?>
        <a href="Contact_Page/Contact_Page.php">Kontakt</a>
    </nav>
    <main>
        <section class="file-list-section">
            <div class="container_file-list">
                <h4>Meine Dateien:</h4>

                <!-- Werkzeugleiste mit Formular -->
                <form action="assets/php/delete_handler.php" method="POST" id="delete-form">
                    <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="download-selected" title="Ausgewählte Dateien herunterladen">
                        <i class='bx bxs-download'></i>
                    </a>
                    
                    <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="delete-selected" title="Löschen">
                        <i class='bx bxs-trash'></i>
                    </a>

                    <div id="plus-icon">
                        <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="toggle-menu" title="Hinzufügen">
                            <i class='bx bxs-plus-circle'></i>
                        </a>
                    </div>

                    <!-- Dropdown-Menü -->
                    <div class="dropdown-menu">
                        <a href="javascript:void(0);" id="create-folder"><i class='bx bx-folder-plus'></i> Neuen Ordner erstellen</a>
                        <a href="File_upload.php" id="upload-file"><i class='bx bx-upload'></i> Datei hochladen</a>
                    </div>

                    <hr style="border: 2px solid #000; margin: 20px 0;">

                    <ul class="file-list">
                        <?php include 'assets/php/list_files.php'; ?>
                    </ul>
                </form>

            <!-- Abschnitt für den Datei-Upload, anfangs ausgeblendet -->
            <section class="upload-section" id="upload-section" style="display: none;">
                <div class="container-upload-section">
                    <div class="upload-form">
                        <h5>Datei-Upload:</h5>
                        <form id="uploadForm" action="assets/php/upload.php" method="post" enctype="multipart/form-data">
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
            </div>
        </section>
    </main>
    <div id="file-info-panel" class="hidden">
        <div id="file-info-content">
                <!-- Einzeldatei-Infos -->
            <div id="single-file-info">
                <p><strong>Name:</strong> <span id="file-name"></span></p>
                <p><strong>Typ:</strong> <span id="file-type"></span></p>
                <p><strong>Größe:</strong> <span id="file-size"></span></p>
                <p><strong>Pfad:</strong> <span id="file-path"></span></p>
            </div>

            <!-- Mehrere Dateien ausgewählt -->
            <div id="multi-file-info" style="display: none;">
                <p><strong>Ausgewählte Dateien:</strong> <span id="total-files"></span></p>
                <p><strong>Gesamtgröße:</strong> <span id="total-size"></span></p>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        document.getElementById('toggle-menu').addEventListener('click', function() {
            var dropdownMenu = document.querySelector('.dropdown-menu');
            
            // Überprüfen, ob das Dropdown-Menü gerade angezeigt wird oder nicht
            if (dropdownMenu.style.display === 'block') {
                dropdownMenu.style.display = 'none';  // Wenn es angezeigt wird, dann ausblenden
            } else {
                dropdownMenu.style.display = 'block';  // Wenn es nicht angezeigt wird, dann einblenden
            }
        });
    </script>
</body>
</html>
