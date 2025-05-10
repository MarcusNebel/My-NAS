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

    <link rel="stylesheet" href="assets/css/trash.css" />
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <header>
		<div class="container transparancy">
      		<h2><a class="link-no-decoration" href="index.php"><span>MY </span>NAS</a></h2>
			<nav>
				<a class="hover-underline-animation left" href="index.php">Startseite</a>
				<a class="hover-underline-animation left" href="User_Files.php">Dateien</a>
				<a class="hover-underline-animation left" href="messenger.php">Messenger</a>
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
		<a href="User_Files.php">Dateien</a>
		<a href="messenger.php">Messenger</a>
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
                <h4>Gelöschte Dateien:</h4>

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

                <!-- Werkzeugleiste mit Formular -->
                <form action="assets/php/delete_handler.php" method="POST" id="delete-form">
                    <div class="toolbar">
                        <div class="left">
                            <a href="User_Files.php">
                                <i class='bx bx-left-arrow-alt' ></i>
                            </a>

                            <div class="search-and-sort">
                                <input type="text" id="search-input" placeholder="Dateien suchen..." style="display: inline;">
                            </div>

                            <div class="edit-items" id="edit-toolbar" style="display: none;">
                                <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="delete-selected" title="Löschen">
                                    <i class='bx bxs-trash'></i>
                                </a>
                                <a class="restore-btn" href="javascript:void(0);" style="text-decoration: none;" id="restore-files-btn" title="Wiederherstellen">
                                    <i class='bx bx-refresh'></i>
                                </a>
                            </div>
                        </div>
                        <div class="right">
                            <a class="trash-btn" id="clear-trash-btn" href="javascript:void(0);" onclick="clearTrash(); return false;">
                                <i class='bx bxs-trash-alt'></i> Papierkorb leeren
                            </a>
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
                        <?php include 'assets/php/list_trash_files.php'; ?>
                    </ul>
                </form>

        </div>
    </main>
    <input type="hidden" name="current_path" id="current_path" value="<?php echo isset($_GET['path']) ? htmlspecialchars($_GET['path']) : ''; ?>">
    <script src="assets/js/trash.js"></script>
    <script>
        function clearTrash() {
            if (!confirm("Möchten Sie wirklich alle Dateien und Ordner in diesem Papierkorb-Ordner endgültig löschen?")) {
                return;
            }

            if (!confirm("Sind Sie sicher? Dieser Vorgang kann nicht rückgängig gemacht werden.")) {
                return;
            }

            const urlParams = new URLSearchParams(window.location.search);
            const path = urlParams.get('path') || '';

            var deleteForm = document.createElement("form");
            deleteForm.method = "POST";
            deleteForm.action = `assets/php/clear_trash.php?path=${encodeURIComponent(path)}`;
            deleteForm.style.display = "none";

            document.body.appendChild(deleteForm);
            deleteForm.submit();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toolbar = document.getElementById('edit-toolbar');

            function updateToolbarVisibility() {
                const checkboxes = document.querySelectorAll('.file-checkbox');
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                toolbar.style.display = anyChecked ? 'flex' : 'none';
            }

            // Bei direktem Checkbox-Klick
            document.addEventListener('change', function (event) {
                if (event.target.matches('.file-checkbox')) {
                    updateToolbarVisibility();
                }
            });

            // Bei Klick auf Zeile – Verzögerung, damit Checkbox-Status aktualisiert ist
            document.querySelectorAll('.file-item').forEach(item => {
                item.addEventListener('click', function () {
                    setTimeout(updateToolbarVisibility, 0); // nach Checkbox-Änderung prüfen
                });
            });

            // Initial
            updateToolbarVisibility();
        });
    </script>
    <script>
        document.getElementById('rename-item').addEventListener('click', () => {
        const checked = document.querySelectorAll('.file-checkbox:checked');
        if (checked.length !== 1) {
            alert('Bitte genau eine Datei oder einen Ordner auswählen.');
            return;
        }

        const fileInput = checked[0];
        const oldName = fileInput.value;
        const isFolder = fileInput.dataset.type === 'folder';
        const fullPath = fileInput.dataset.fullPath;

        let baseName = oldName;
        let extension = '';

        if (!isFolder) {
            const dotIndex = oldName.lastIndexOf('.');
            if (dotIndex > 0) {
                baseName = oldName.substring(0, dotIndex);
                extension = oldName.substring(dotIndex);
            }
        }

        // Modal anzeigen und voreingestellten Namen einfügen
        const modal = document.getElementById('renameModal');
        const newNameInput = document.getElementById('newNameInput');
        newNameInput.value = baseName;

        // Öffne Modal
        modal.style.display = 'flex';

        // Fokussiere das Eingabefeld und wähle den Text aus
        newNameInput.focus(); // Fokussiert das Eingabefeld
        newNameInput.select(); // Markiert den gesamten Text

        // Wenn der Benutzer auf "Bestätigen" klickt
        const confirmRename = document.getElementById('confirmRename');
        confirmRename.addEventListener('click', function () {
            const newBaseName = newNameInput.value.trim();
            if (!newBaseName || newBaseName === baseName) {
                modal.style.display = 'none';
                return;
            }

            const newName = newBaseName + extension;

            const formData = new FormData();
            formData.append('old_name', oldName);
            formData.append('new_name', newName);
            formData.append('is_folder', isFolder ? '1' : '0');
            formData.append('path', fullPath);

            fetch('assets/php/rename.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                // Wenn Fehler aufgetreten sind, wird eine Fehlermeldung angezeigt
                if (!data.success) {
                    alert(data.message); // Nur Fehler anzeigen, kein Erfolg
                }

                // Seite neu laden, ohne Erfolgsmeldung anzuzeigen
                location.reload();
            })
            .catch(err => {
                console.error(err);
                alert('Ein Fehler ist aufgetreten.');
            });

            modal.style.display = 'none'; // Schließe das Modal nach der Bestätigung
        });

        // Wenn der Benutzer auf "Abbrechen" klickt
        document.getElementById('cancelRename').addEventListener('click', function () {
            modal.style.display = 'none'; // Schließe das Modal
        });

        // Wenn der Benutzer auf das X (Schließen-Button) klickt
        document.querySelector('#closeRenameModal').addEventListener('click', function () {
            modal.style.display = 'none'; // Schließe das Modal
        });

        // Event Listener für Enter-Taste
        newNameInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                confirmRename.click(); // Simuliere einen Klick auf den Bestätigungs-Button
            }
        });

    });

    // Wenn der Benutzer außerhalb des Modals klickt, schließt sich das Modal
    window.onclick = function(event) {
        const modal = document.getElementById('renameModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };
    </script>
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
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById("search-input");
            const fileList = document.getElementById('file-list');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            
            // Toolbar-Logik wie beschrieben
            function setupFileListEventListeners() {
                const toolbar = document.getElementById('edit-toolbar');
                function updateToolbarVisibility() {
                    const checkboxes = document.querySelectorAll('.file-checkbox');
                    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                    toolbar.style.display = anyChecked ? 'flex' : 'none';
                }
                document.querySelectorAll('.file-checkbox').forEach(cb => {
                    cb.addEventListener('change', updateToolbarVisibility);
                });
                document.querySelectorAll('.file-item').forEach(item => {
                    item.addEventListener('click', function () {
                        setTimeout(updateToolbarVisibility, 0);
                    });
                });
                updateToolbarVisibility();
            }
            setupFileListEventListeners();

            // Suchfeld-Listener
            searchInput.addEventListener('input', function () {
                const query = this.value.trim();
                searchFiles(query);
            });

            
            function searchFiles(query) {
                const currentPath = document.getElementById("current_path").value;
                fetch(`assets/php/list_trash_files.php?search=${encodeURIComponent(query)}&path=${encodeURIComponent(currentPath)}`)
                    .then(response => response.text())
                    .then(data => {
                        fileList.innerHTML = data;
                        setupFileListEventListeners();

                        // "Alle auswählen"-Checkbox beim Suchen abwählen und indeterminate entfernen
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    })
                    .catch(error => {
                        console.error('Fehler bei der Anfrage:', error);
                    });
            }
        });
    </script>
	<script src="assets/js/lang.js"></script>
</body>
</html>
