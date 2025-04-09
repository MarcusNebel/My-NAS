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
    <title>My NAS | Meine Dateien</title>
    <link rel="website icon" href="Logo/Logo_512px.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="assets/css/User_Files.css" />
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
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
        <section class="file-list-section">
            <div class="container_file-list">
                <h4>Meine Dateien:</h4>

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

                <!-- Verstecktes Input-Feld für den Benutzernamen -->
                <input type="hidden" id="username-hidden" value="<?php echo $username; ?>">

                <!-- Apple-Style Overlay und Lade-Kreis -->
                <div id="overlay" style="display: none; justify-content: center; align-items: center; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);">
                    <div class="apple-loader">
                        <div></div><div></div><div></div><div></div>
                        <div></div><div></div><div></div><div></div>
                    </div>
                </div>

                <!-- Werkzeugleiste mit Formular -->
                <form action="assets/php/delete_handler.php" method="POST" id="delete-form">
                    <i id="search-icon" class='bx bx-search'></i>
                    <input type="text" id="search-input" placeholder="Datei suchen..." />

                    <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="download-selected" title="Ausgewählte Dateien herunterladen">
                        <i class='bx bxs-download'></i>
                    </a>
                    
                    <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="delete-selected" title="Löschen">
                        <i class='bx bxs-trash'></i>
                    </a>

                    <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="toggle-menu" title="Hinzufügen">
                         <i class='bx bxs-plus-circle'></i>
                    </a>

                    <!-- Dropdown-Menü -->
                    <div class="dropdown-menu">
                        <a href="File_upload.php" id="upload-file"><i class='bx bx-upload'></i> Dateien hochladen</a>
                        <a href="javascript:void(0);" id="create-folder"><i class='bx bx-folder-plus'></i> Neuen Ordner erstellen</a>
                    </div>
                    
                    <strong><p id="downloadStatus" style="margin-top: 10px;"></p></strong>

                    <!-- Modal Fenster -->
                    <div id="folderModal" class="modal" style="display: none;">
                        <div class="modal-content" style="background: #fff; padding: 20px; border-radius: 10px; width: 300px; margin: auto;">
                            <span id="closeModal" style="float:right; cursor:pointer;">&times;</span>
                            <h3>Ordner erstellen</h3>
                            <input type="text" id="folderNameInput" placeholder="Ordnername" style="width: 100%; margin-top: 10px; padding: 8px;">
                            <button id="confirmCreateFolder" type="button" style="margin-top: 10px;">Erstellen</button>
                        </div>
                    </div>

                    <hr style="border: 2px solid #000; margin: 20px 0;">

                    <ul class="file-list">
                        <div style="margin-bottom: 20px; margin-left: 16px;">
                            <label class="select-all-cb">
                                <input type="checkbox" class="select-all-cb" id="select-all-checkbox"> Alle auswählen
                            </label>
                        </div>
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
        // Event-Listener für das Eltern-Element (file-list)
        document.querySelector('.file-list').addEventListener('click', function(e) {
            const target = e.target;

            // Wenn auf eine Datei (li.file-item) oder den Dateinamen geklickt wurde
            if (target.closest('.file-item')) {
                const checkbox = target.closest('.file-item').querySelector('.file-checkbox');
                checkbox.checked = !checkbox.checked;  // Checkbox umschalten
                updateSelectAllCheckbox();  // Überprüfen, ob alle Checkboxen ausgewählt sind
            }
        });

        // Alle auswählen (Checkbox "select-all-checkbox")
        document.getElementById("select-all-checkbox").addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll('.file-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;  // Alle Checkboxen setzen
            });
            updateSelectAllCheckbox();  // Überprüfen, ob alle Checkboxen ausgewählt sind
        });

        // Funktion zum Überprüfen des Status der "alle auswählen"-Checkbox
        function updateSelectAllCheckbox() {
            const allCheckboxes = document.querySelectorAll('.file-checkbox');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            
            // Prüfen, ob alle Checkboxen aktiviert sind
            const allChecked = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
            selectAllCheckbox.checked = allChecked;  // Setzt die "alle auswählen"-Checkbox entsprechend

            // Wenn nicht alle Checkboxen aktiv sind, wird sie deaktiviert
            selectAllCheckbox.indeterminate = !allChecked && Array.from(allCheckboxes).some(checkbox => checkbox.checked);
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const modal = document.getElementById("folderModal");
            const openBtn = document.getElementById("create-folder");
            const closeBtn = document.getElementById("closeModal");

            // Öffnen
            openBtn.addEventListener("click", () => {
                modal.style.display = "flex";
                modal.classList.add("fade-in");
                modal.classList.remove("fade-out");

                // Dropdown-Menü ausblenden
                const dropdownMenu = document.querySelector(".dropdown-menu");
                dropdownMenu.style.display = "none";
            });

            // Schließen mit X
            closeBtn.addEventListener("click", () => {
                closeModal();
            });

            // Schließen bei Klick außerhalb des Modals
            window.addEventListener("click", function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Schließen mit Escape
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape") {
                    closeModal();
                }
            });

            // Fade-Out-Funktion
            function closeModal() {
                modal.classList.remove("fade-in");
                modal.classList.add("fade-out");
                setTimeout(() => {
                    modal.style.display = "none";
                }, 200); // gleiche Dauer wie in der Animation
            }
        });

        document.getElementById("confirmCreateFolder").addEventListener("click", () => {
            const folderName = document.getElementById("folderNameInput").value.trim();

            if (folderName === "") {
                alert("Bitte gib einen Ordnernamen ein.");
                return;
            }

            fetch("assets/php/create_folder.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `folderName=${encodeURIComponent(folderName)}`
            })
            .then(response => {
                return response.text().then(text => {
                    if (!response.ok) throw new Error(text); // Fehler auslösen mit Servertext
                    return text;
                });
            })
            .then(data => {
                closeModal();
                location.reload();
            })
            .catch(error => {
                alert(error.message); // Zeigt echten Server-Fehlertext an
                console.error(error);
            });
        });

        // Modal schließen
        function closeModal() {
            const modal = document.getElementById("folderModal");
            modal.style.display = "none";
        }
    </script>
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
    <script>
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const fileCheckboxes = document.querySelectorAll('.file-checkbox');

        selectAllCheckbox.addEventListener('change', function() {
            fileCheckboxes.forEach(cb => cb.checked = this.checked);
        });

        fileCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                if (!cb.checked) {
                    selectAllCheckbox.checked = false;
                } else if ([...fileCheckboxes].every(cb => cb.checked)) {
                    selectAllCheckbox.checked = true;
                }
            });
        });
    </script>
    <script>
        const searchIcon = document.getElementById("search-icon");
        const searchInput = document.getElementById("search-input");

        searchIcon.addEventListener("click", () => {
            searchInput.classList.toggle("show");

            if (searchInput.classList.contains("show")) {
            searchInput.focus();
            } else {
            searchInput.value = ""; // Eingabe löschen
            filterFiles("");        // Alle Dateien wieder anzeigen
            }
        });

        searchInput.addEventListener("input", () => {
            const filter = searchInput.value.toLowerCase();
            filterFiles(filter);
        });

        function filterFiles(filter) {
            const files = document.querySelectorAll(".file-item");
            files.forEach(file => {
            const fileName = file.querySelector(".file-name").textContent.toLowerCase();
            file.style.display = fileName.includes(filter) ? "block" : "none";
            });
        }
    </script>
	<script src="assets/js/lang.js"></script>
</body>
</html>
