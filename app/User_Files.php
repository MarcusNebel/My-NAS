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
                    <a href="javascript:void(0);" style="text-decoration: none;" id="create-folder">
                        <i class='bx bx-folder-plus'></i>
                    </a>

                    <a href="File_upload.php?path=<?php echo urlencode($_GET['path'] ?? ''); ?>" style="text-decoration: none;" id="upload-file">
                        <i class='bx bx-upload'></i>
                    </a>

                    <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="download-selected" title="Ausgewählte Dateien herunterladen">
                        <i class='bx bx-download'></i>
                    </a>

                    <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="delete-selected" title="Löschen">
                        <i class='bx bxs-trash'></i>
                    </a>

                    <i id="search-icon" class='bx bx-search'></i>
                    <input type="text" id="search-input" placeholder="Dateien suchen...">
                    
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

                    <input type="hidden" name="current_path" id="current_path" value="<?php echo isset($_GET['path']) ? htmlspecialchars($_GET['path']) : ''; ?>">

                    <div class="path-navigation" id="path-nav">
                        <?php
                        // Start mit dem Haus
                        echo "<a href='?'><i class='bx bxs-home'></i></a>";

                        $currentPath = isset($_GET['path']) ? $_GET['path'] : '';
                        $pathParts = explode('/', $currentPath);
                        $currentSubPath = '';
                        foreach ($pathParts as $part) {
                            $currentSubPath .= $currentSubPath ? "/$part" : $part;
                            echo " <span class='path-separator'>›</span> <a href='?path=" . urlencode($currentSubPath) . "'>$part</a>";
                        }
                        ?>
                    </div>

                    <div style="margin-bottom: 20px; margin-left: 16px;">
                        <label class="select-all-cb">
                            <input type="checkbox" class="select-all-cb" id="select-all-checkbox"> Alle auswählen
                        </label>
                    </div>

                    <ul id="file-list" class="file-list">
                        <?php include 'assets/php/list_files.php'; ?>
                    </ul>
                    </form>

        </div>
    </main>
    <div id="file-info-panel" class="hidden">
        <div id="file-info-content">
                <!-- Einzeldatei-Infos -->
            <div id="single-file-info">
            </div>

            <!-- Mehrere Dateien ausgewählt -->
            <div id="multi-file-info" style="display: none;">
            </div>
        </div>
    </div>
    <input type="hidden" name="current_path" id="current_path" value="<?php echo isset($_GET['path']) ? htmlspecialchars($_GET['path']) : ''; ?>">
    <script src="assets/js/main.js"></script>
    <script>
        // Event-Listener für das Eltern-Element (file-list)
        document.querySelector('.file-list').addEventListener('click', function (e) {
            const fileItem = e.target.closest('.file-item');
            if (!fileItem) return;

            // Verhindere Aktion bei Doppelklick – wird dort separat behandelt
            if (e.detail === 2) return;

            // Einzelklick → Checkbox toggeln (aber nicht auf Link direkt)
            if (!e.target.closest('a')) {
                const checkbox = fileItem.querySelector('.file-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateSelectAllCheckbox();
                }
            }
        });

        document.querySelector('.file-list').addEventListener('dblclick', function(e) {
            console.log("geladen")
            const item = e.target.closest('.file-item.directory');
            if (item) {
                // Alle Checkboxen abwählen
                document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = false);

                // Die zugehörige Checkbox in diesem Ordner aktivieren
                const checkbox = item.querySelector('.file-checkbox');
                if (checkbox) {
                    checkbox.checked = true;
                }

                // In den Ordner navigieren
                const path = item.getAttribute('data-path');
                if (path) {
                    window.location.href = '?path=' + encodeURIComponent(path);
                }
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

            fetch("assets/php/create_folder.php?path=<?php echo urlencode($_GET['path'] ?? ''); ?>", {
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
        const fileList = document.getElementById('file-list');

        // Zeigt das Suchfeld an oder blendet es aus, wenn auf das Such-Icon geklickt wird
        searchIcon.addEventListener("click", () => {
            searchInput.classList.toggle("show");

            if (searchInput.classList.contains("show")) {
                searchInput.focus();
            } else {
                searchInput.value = ""; // Eingabe löschen, wenn ausgeblendet
                searchFiles(""); // Alle Dateien anzeigen
            }
        });

        // Eventlistener für Eingaben im Suchfeld
        searchInput.addEventListener('input', function () {
            const query = this.value.trim(); // Holen der Eingabe und Entfernen von Leerzeichen

            // Bei leerer Eingabe alle Dateien anzeigen
            if (query === '') {
                searchFiles('');
            } else {
                // Anfrage senden, wenn eine Eingabe gemacht wurde
                searchFiles(query);
            }
        });

        // Funktion für die Suchanfrage
        function searchFiles(query) {
            const currentPath = document.getElementById("current_path").value;

            fetch(`assets/php/list_files.php?search=${encodeURIComponent(query)}&path=${encodeURIComponent(currentPath)}`)
                .then(response => response.text())
                .then(data => {
                    fileList.innerHTML = data;
                })
                .catch(error => {
                    console.error('Fehler bei der Anfrage:', error);
                });
        }
    </script>
	<script src="assets/js/lang.js"></script>
</body>
</html>
