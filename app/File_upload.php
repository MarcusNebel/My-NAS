<?php
session_start();
if (!isset($_SESSION["id"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"]; // Speichert die aktuelle Seite
    header("Location: account-system/Login.php"); // Weiterleitung zur Login-Seite
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Datei-Upload</title>
    <link rel="website icon" href="Logo/Logo_512px.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <link rel="stylesheet" href="assets/css/File_upload.css" />
</head>
<body>
    <header>
		<div class="container transparancy">
      		<h2><a class="link-no-decoration" href="index.php"><span>MY </span>NAS</a></h2>
			<nav>
				<a class="hover-underline-animation left" href="index.php" data-lang="home">Startseite</a>
				<a class="hover-underline-animation left" href="User_Files.php" data-lang="files">Meine Dateien</a>
				<a class="hover-underline-animation left" href="messenger.php" data-lang="messenger">Messenger</a>
				<?php if(isset($_SESSION["id"])): ?>
					<a class="hover-underline-animation left" href="account-system/account.php" data-lang="account">Mein Account</a>
				<?php else: ?>
					<a class="hover-underline-animation left" href="account-system/Login.php" data-lang="account">Mein Account</a>
				<?php endif; ?>
				<a class="hover-underline-animation left" href="Contact_Page/Contact_Page.php" data-lang="contact">Kontakt</a>
			</nav>
			<?php if (isset($_SESSION["id"])): ?>
                <a class="login_button" href="account-system/logout.php" data-lang="logout">Abmelden</a>
            <?php else: ?>
                <a class="login_button" href="account-system/Login.php" data-lang="login">Anmelden</a>
            <?php endif; ?>
			<button class="hamburger">
				<div class="bar"></div>
			</button>
		</div>
	</header>
	<nav class="mobile-nav">
		<a href="index.php" data-lang="home">Startseite</a>
		<a href="User_Files.php" data-lang="files">Meine Dateien</a>
		<a href="messenger.php" data-lang="messenger">Messenger</a>
		<?php if(isset($_SESSION["id"])): ?>
			<a href="account-system/account.php" data-lang="account">Mein Account</a>
		<?php else: ?>
			<a href="account-system/Login.php" data-lang="account">Mein Account</a>
		<?php endif; ?>
		<a href="Contact_Page/Contact_Page.php" data-lang="contact">Kontakt</a>
	</nav>
    <main>
        <section class="upload-section">
            <div class="container-upload-section">
                <div class="upload-form">
                    <a href="User_Files.php">
                        <i class='bx bx-left-arrow-alt' ></i>
                    </a>
                    <h5>Datei-Upload:</h5>

                    <?php
                    require_once 'account-system/mysql.php';

                    // Benutzername holen
                    $username = 'Unbekannt';
                    if (isset($_SESSION['id'])) {
                        $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
                        $stmt->bindParam(':id', $_SESSION['id']);
                        $stmt->execute();
                        $user = $stmt->fetch();
                        if ($user) {
                            $username = $user['USERNAME'];
                        }
                    }
                    ?>

                    <form id="uploadForm">
                    <input type="file" id="fileInput" name="file[]" multiple required>
                        <input type="hidden" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
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
    <script>
        const userId = "<?php echo $_SESSION['id']; ?>";
    </script>
    <script>
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('fileInput');
        const uploadStatus = document.getElementById('uploadStatus');
        const progressBar = document.getElementById('progress-bar');
        const uploadSpeed = document.getElementById('upload-speed');
        const usernameField = document.getElementById('username');

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const files = fileInput.files;
            if (files.length === 0) return;

            const formData = new FormData();

            // Alle Dateien zum FormData-Objekt hinzufügen
            for (let i = 0; i < files.length; i++) {
                formData.append('file[]', files[i]); // 'file[]' für mehrere Dateien
            }

            formData.append('username', usernameField.value); // Benutzername mitsenden

            const xhr = new XMLHttpRequest();

            // Fortschrittsanzeige
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';

                    const seconds = (Date.now() - startTime) / 1000;
                    const speed = (e.loaded / 1024 / 1024) / seconds;
                    uploadSpeed.textContent = `Upload-Geschwindigkeit: ${speed.toFixed(2)} MB/s`;
                }
            });

            // Upload abgeschlossen
            xhr.onload = function() {
                if (xhr.status === 200) {
                    uploadStatus.textContent = '✅ Upload erfolgreich!';
                    window.location.href = 'User_Files.php';  // Weiterleitung nach dem erfolgreichen Upload
                } else {
                    uploadStatus.textContent = '❌ Fehler beim Upload: ' + xhr.responseText;
                }
            };

            xhr.onerror = function() {
                uploadStatus.textContent = '❌ Fehler beim Upload';
            };

            const startTime = Date.now();
            xhr.open('POST', 'https://__SERVER_IP__:8080/upload', true);
            xhr.send(formData);
        });
    </script>
    <script src="assets/js/main.js"></script>
	<script src="assets/js/lang.js"></script>
</body>
</html>
