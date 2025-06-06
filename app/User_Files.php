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
                    <div class="toolbar">
                        <div class="left">
                            <div class="dropdown-wrapper">
                                <a class="new-button" href="javascript:void(0);" id="new-button">
                                    <i class='bx bx-plus'></i> Neu
                                </a>

                                <div id="new-dropdown">
                                    <a class="new-dropdown-item" href="javascript:void(0);" id="create-folder">
                                        <i class='bx bx-folder-plus'></i> Neuer Ordner
                                    </a>
                                    <a href="#" class="new-dropdown-item" id="openUploadModal">
                                        <i class='bx bx-upload'></i> Datei hochladen
                                    </a>
                                </div>
                            </div>

                            <div class="search-and-sort">
                                <input type="text" id="search-input" placeholder="Dateien suchen..." style="display: inline;">
                            </div>

                            <div class="edit-items" id="edit-toolbar" style="display: none;">
                                <a href="#" style="text-decoration: none;" onclick="submitCopyForm()">
                                    <i class='bx bx-copy'></i>
                                </a>
                                <a href="#" style="text-decoration: none;" onclick="submitMoveForm()">
                                    <i class='bx bx-right-arrow-alt' ></i>
                                </a>
                                <a href="javascript:void(0);" style="text-decoration: none;" id="rename-item" title="Umbenennen">
                                    <i class='bx bx-rename'></i>
                                </a>
                                <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="download-selected" title="Herunterladen">
                                    <i class='bx bx-download'></i>
                                </a>
                                <a class="pen-a" href="javascript:void(0);" style="text-decoration: none;" id="delete-selected" title="Löschen">
                                    <i class='bx bxs-trash'></i>
                                </a>
                            </div>
                        </div>
                        <div class="right">
                            <a class="trash-btn" href="trash.php">
                                <i class='bx bxs-trash-alt'></i> Gelöschte Dateien
                            </a>
                        </div>
                    </div>

                    
                    
                    
                    <strong><p id="downloadStatus" style="margin-top: 10px;"></p></strong>

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
            <div id="folderModal" class="modal" style="display: none;">
                <div class="modal-content" style="background: #fff; padding: 20px; border-radius: 10px; width: 300px; margin: auto;">
                    <span id="closeModal" style="float:right; cursor:pointer;">&times;</span>
                    <h3>Ordner erstellen</h3>
                    <input type="text" id="folderNameInput" placeholder="Ordnername" style="width: 100%; margin-top: 10px; padding: 8px;">
                    <button id="confirmCreateFolder" type="button" style="margin-top: 10px;">Erstellen</button>
                </div>
            </div>

            <div id="renameModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span id="closeRenameModal" style="float:right; cursor:pointer;">&times;</span>
                    <h3>Datei umbenennen</h3>
                    <input type="text" id="newNameInput" placeholder="Neuer Name" style="width: 100%; margin-top: 10px; padding: 8px;">
                    <button id="confirmRename" type="button" style="margin-top: 10px;">Bestätigen</button>
                    <button id="cancelRename" type="button" style="margin-top: 10px;">Abbrechen</button>
                </div>
            </div>

            <div id="uploadModal" class="modal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 9999;">
                <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    <button id="closeModal" style="position: absolute; top: 10px; right: 10px; font-size: 18px; background: none; border: none; cursor: pointer;">&times;</button>
                    <h2 style="margin-bottom: 20px;">Datei hochladen</h2>

                    <?php
                    $username = '';
                    if (isset($_SESSION['id'])) {
                        require("assets/php/mysql.php");

                        $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
                        $stmt->execute([':id' => $_SESSION['id']]);
                        $username = $stmt->fetchColumn();
                    }
                    ?>
                    <input type="text" id="usernameModal" hidden value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="text" id="pathModal" hidden>

                    <div id="dropArea" class="drop-area" style="text-align: center; padding: 20px; border: 2px dashed #2894f4; border-radius: 10px;">
                        <p>Dateien hierher ziehen oder</p>
                        <button id="fileSelectBtn" style="margin-top: 10px;">Dateien auswählen</button>
                        <input type="file" id="fileInputModal" multiple style="display: none;">
                    </div>

                    <p id="uploadStatusModal" style="margin-top: 15px;"></p>
                    <div id="progress-container-modal" class="progress-container" style="background: #e3f3ff; height: 25px; border-radius: 10px; overflow: hidden;">
                        <div id="progress-bar-modal" class="progress-bar" style="width: 0%; background: #2894f4; height: 100%; margin-top: 1px; text-align: center; line-height: 20px;">0%</div>
                    </div>
                    <p id="upload-speed-modal" class="upload-speed" style="margin-top: 10px;">Upload-Geschwindigkeit: 0 MB/s</p>
                </div>
            </div>

            <div id="copyModal" style="display:none;">
                <div class="copy-modal-content">
                    <h2>Zielordner auswählen</h2>
                    <div id="copy-folder-list">
                        <!-- Hier werden die Ordner geladen -->
                    </div>
                    <button id="confirmCopy">Kopieren</button>
                    <button id="cancelCopy">Abbrechen</button>
                </div>
            </div>

            <div id="moveModal" style="display:none;">
                <div class="move-modal-content">
                    <h2>Zielordner auswählen</h2>
                    <div id="move-folder-list">
                        <!-- Hier werden die Ordner geladen -->
                    </div>
                    <button id="confirmMove">Verschieben</button>
                    <button id="cancelMove">Abbrechen</button>
                </div>
            </div>
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
        // Modal-Elemente
        const uploadModal = document.getElementById('uploadModal');
        const openModalBtn = document.getElementById('openUploadModal');
        const closeModalBtn = document.getElementById('closeModal');

        // Neue Upload-Elemente
        const fileSelectBtn = document.getElementById('fileSelectBtn');
        const fileInputModal = document.getElementById('fileInputModal');
        const dropArea = document.getElementById('dropArea');

        const uploadStatusModal = document.getElementById('uploadStatusModal');
        const progressBarModal = document.getElementById('progress-bar-modal');
        const uploadSpeedModal = document.getElementById('upload-speed-modal');
        const usernameModal = document.getElementById('usernameModal');
        const pathModal = document.getElementById('pathModal');

        let flaskServerURL = '';

        // Konfiguration laden
        async function loadConfig() {
            const response = await fetch('../../config.json');
            const config = await response.json();
            flaskServerURL = config.flaskServerURL + '/upload';

            // Pfad aus URL in Modal-Input setzen
            const params = new URLSearchParams(window.location.search);
            const pathFromUrl = params.get('path') || '';
            pathModal.value = pathFromUrl;
        }

        loadConfig();

        // Modal öffnen
        openModalBtn.addEventListener('click', e => {
            e.preventDefault();
            uploadStatusModal.textContent = '';
            progressBarModal.style.width = '0%';
            progressBarModal.textContent = '0%';
            uploadSpeedModal.textContent = 'Upload-Geschwindigkeit: 0 MB/s';
            fileInputModal.value = '';
            uploadModal.style.display = 'flex';
        });

        // Modal schließen
        closeModalBtn.addEventListener('click', () => {
            uploadModal.style.display = 'none';
        });

        // Klick außerhalb Modal schließt es
        window.addEventListener('click', e => {
            if (e.target === uploadModal) {
                uploadModal.style.display = 'none';
            }
        });

        // 📁 Datei-Auswahl über Button
        fileSelectBtn.addEventListener('click', () => {
            fileInputModal.click();
        });

        // Auswahl im versteckten File-Input
        fileInputModal.addEventListener('change', () => {
            if (fileInputModal.files.length > 0) {
                handleFileUpload(fileInputModal.files);
            }
        });

        // 📤 Drag-and-Drop
        dropArea.addEventListener('dragover', e => {
            e.preventDefault();
            dropArea.classList.add('dragover');
        });

        dropArea.addEventListener('dragleave', e => {
            e.preventDefault();
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', e => {
            e.preventDefault();
            dropArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                handleFileUpload(e.dataTransfer.files);
            }
        });

        // 🔁 Upload-Funktion
        function handleFileUpload(files) {
            uploadStatusModal.textContent = `${files.length} Datei(en) werden vorbereitet...`;

            setTimeout(() => {
                const formData = new FormData();
                for (let i = 0; i < files.length; i++) {
                    formData.append('file[]', files[i]);
                }

                formData.append('username', usernameModal.value);
                formData.append('path', pathModal.value);

                const xhr = new XMLHttpRequest();
                const startTime = Date.now();

                xhr.upload.addEventListener('progress', e => {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBarModal.style.width = percent + '%';
                        progressBarModal.textContent = percent + '%';

                        const seconds = (Date.now() - startTime) / 1000;
                        const speed = (e.loaded / 1024 / 1024) / seconds;
                        uploadSpeedModal.textContent = `Upload-Geschwindigkeit: ${speed.toFixed(2)} MB/s`;
                    }
                });

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        uploadStatusModal.textContent = '✅ Upload erfolgreich!';
                        const pathValue = pathModal.value;
                        const redirectUrl = pathValue ? `User_Files.php?path=${encodeURIComponent(pathValue)}` : 'User_Files.php';
                        window.location.href = redirectUrl;
                    } else {
                        uploadStatusModal.textContent = '❌ Fehler beim Upload: ' + xhr.responseText;
                    }
                };

                xhr.onerror = () => {
                    uploadStatusModal.innerHTML = 'Fehler beim Upload. Bitte <a href="' + flaskServerURL + '" target="_blank" rel="noopener noreferrer">den Upload-Server</a> manuell überprüfen.';
                };

                xhr.open('POST', flaskServerURL, true);
                xhr.send(formData);
            }, 1000); // 1 Sekunde Verzögerung zum Anzeigen der Dateianzahl
        }
    </script>
    <script>
        function submitCopyForm() {
            var checkboxes = document.querySelectorAll('.file-checkbox:checked');
            var files = [];
            var folders = [];

            // Dateien und Ordner trennen
            checkboxes.forEach(checkbox => {
                if (checkbox.dataset.type === "folder") {
                    folders.push(checkbox.value);
                } else {
                    files.push(checkbox.value);
                }
            });

            if (files.length === 0 && folders.length === 0) {
                alert("Bitte wähle mindestens eine Datei oder einen Ordner zum Kopieren aus.");
                return;
            }

            // URL-Parameter für den aktuellen Pfad
            const urlParams = new URLSearchParams(window.location.search);
            const path = urlParams.get('path') || '';

            // Modal öffnen
            var modal = document.getElementById("copyModal");
            modal.style.display = "block";

            // Ordner laden und in das Modal einfügen
            var folderList = document.getElementById("copy-folder-list");
            folderList.innerHTML = ''; // Vorherige Liste löschen

            fetch(`assets/php/get_folder_structure.php?path=${encodeURIComponent(path)}`)
                .then(response => response.text())
                .then(data => {
                    folderList.innerHTML = data;  // Füge Ordnerliste in das Modal ein
                });

            // Bestätigungsbutton im Modal
            var confirmBtn = document.getElementById("confirmCopy");
            confirmBtn.onclick = function () {
                var selected = document.querySelector(".folder-option.selected");
                if (!selected) {
                    alert("Bitte wähle einen Zielordner aus.");
                    return;
                }

                var target = selected.dataset.path;

                // Formular erstellen
                var copyForm = document.createElement("form");
                copyForm.method = "POST";
                copyForm.action = `assets/php/copy_handler.php?path=${encodeURIComponent(path)}`;
                copyForm.style.display = "none";

                // Dateien hinzufügen
                files.forEach(file => {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "files[]";
                    input.value = file;
                    copyForm.appendChild(input);
                });

                // Ordner hinzufügen
                folders.forEach(folder => {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "folders[]";
                    input.value = folder;
                    copyForm.appendChild(input);
                });

                // Zielordner
                var targetInput = document.createElement("input");
                targetInput.type = "hidden";
                targetInput.name = "target";
                targetInput.value = target;
                copyForm.appendChild(targetInput);

                // Formular abschicken
                document.body.appendChild(copyForm);
                copyForm.submit();

                // Modal schließen
                modal.style.display = "none";
            };

            // Abbrechen-Button
            var cancelBtn = document.getElementById("cancelCopy");
            cancelBtn.onclick = function () {
                modal.style.display = "none";  // Modal schließen
            };
        }

        // Ordnerauswahl aktivieren
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains("folder-option")) {
                document.querySelectorAll(".folder-option").forEach(el => el.classList.remove("selected"));
                e.target.classList.add("selected");
            }
        });
    </script>
    <script>
        function submitMoveForm() {
            var checkboxes = document.querySelectorAll('.file-checkbox:checked');
            var files = [];
            var folders = [];

            // Dateien und Ordner trennen
            checkboxes.forEach(checkbox => {
                if (checkbox.dataset.type === "folder") {
                    folders.push(checkbox.value);
                } else {
                    files.push(checkbox.value);
                }
            });

            if (files.length === 0 && folders.length === 0) {
                alert("Bitte wähle mindestens eine Datei oder einen Ordner zum Verschieben aus.");
                return;
            }

            // URL-Parameter für den aktuellen Pfad
            const urlParams = new URLSearchParams(window.location.search);
            const path = urlParams.get('path') || '';

            // Modal öffnen
            var modal = document.getElementById("moveModal");
            modal.style.display = "block";

            // Ordner laden und in das Modal einfügen
            var folderList = document.getElementById("move-folder-list");
            folderList.innerHTML = ''; // Vorherige Liste löschen

            fetch(`assets/php/get_folder_structure.php?path=${encodeURIComponent(path)}`)
                .then(response => response.text())
                .then(data => {
                    folderList.innerHTML = data;  // Füge Ordnerliste in das Modal ein
                });

            // Bestätigungsbutton im Modal
            var confirmBtn = document.getElementById("confirmMove");
            confirmBtn.onclick = function () {
                var selected = document.querySelector(".folder-option.selected");
                if (!selected) {
                    alert("Bitte wähle einen Zielordner aus.");
                    return;
                }

                var target = selected.dataset.path;

                // Formular erstellen
                var copyForm = document.createElement("form");
                copyForm.method = "POST";
                copyForm.action = `assets/php/move_handler.php?path=${encodeURIComponent(path)}`;
                copyForm.style.display = "none";

                // Dateien hinzufügen
                files.forEach(file => {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "files[]";
                    input.value = file;
                    copyForm.appendChild(input);
                });

                // Ordner hinzufügen
                folders.forEach(folder => {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "folders[]";
                    input.value = folder;
                    copyForm.appendChild(input);
                });

                // Zielordner
                var targetInput = document.createElement("input");
                targetInput.type = "hidden";
                targetInput.name = "target";
                targetInput.value = target;
                copyForm.appendChild(targetInput);

                // Formular abschicken
                document.body.appendChild(copyForm);
                copyForm.submit();

                // Modal schließen
                modal.style.display = "none";
            };

            // Abbrechen-Button
            var cancelBtn = document.getElementById("cancelMove");
            cancelBtn.onclick = function () {
                modal.style.display = "none";  // Modal schließen
            };
        }

        // Ordnerauswahl aktivieren
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains("folder-option")) {
                document.querySelectorAll(".folder-option").forEach(el => el.classList.remove("selected"));
                e.target.classList.add("selected");
            }
        });
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
        document.getElementById("new-button").addEventListener("click", function (e) {
            e.stopPropagation();
            const dropdown = document.getElementById("new-dropdown");
            dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
        });

        document.addEventListener("click", function () {
            document.getElementById("new-dropdown").style.display = "none";
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
                fetch(`assets/php/list_files.php?search=${encodeURIComponent(query)}&path=${encodeURIComponent(currentPath)}`)
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
