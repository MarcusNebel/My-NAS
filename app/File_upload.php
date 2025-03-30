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
    <title>My NAS | Datei-Upload</title>
    <link rel="website icon" href="Logo/Logo_512px.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <link rel="stylesheet" href="assets/css/style.css" />
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
        <section class="upload-section">
            <div class="container-upload-section">
                <div class="upload-form">
                <button onclick="redirectToUserFiles()"><i class='bx bx-left-arrow-alt' ></i></button>
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
    </main>
    <script src="assets/js/upload_info.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function redirectToUserFiles() {
        window.location.href = "../../User_Files.php";
        }
    </script>
</body>
</html>
