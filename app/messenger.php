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
    <meta name="theme-color" content="#12002F">
    <title>My NAS | Messenger</title>
    <link rel="website icon" href="Logo/Logo_512px.png">
    
    <link rel="stylesheet" href="assets/css/messenger.css" />
    <!-- Stelle sicher, dass das in deinem <head> steht -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
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
        <div class="messenger-container">
            <div class="sidebar">
                <div class="sidebar-header">
                    <p>Nachrichten</p>
                    <a id="openAddContactModal" class="add-contact-button">
                        <i class='bxr bx-message-circle-plus'></i> 
                    </a>
                </div>
                <div class="conversation-list">
                    <?php include "assets/php/chat-system/get_user_chats.php" ?>
                </div>
                <div class="foot-area">
                    <?php include "assets/php/chat-system/get_chat_user_id.php"; ?>
                    <p id="current-user-id" data-id="<?php echo htmlspecialchars($chatUserId); ?>" hidden></p>
                    <p class="UserID">Benutzer ID: <?php echo htmlspecialchars($chatUserId ?? 'Wird geladen...'); ?></p>
                </div>
            </div>

            <div class="chat-area hidden">
                <div class="chat-header">
                    <div class="chat-header-left"></div>
                    <div class="chat-header-right">
                        <a href="#">
                            <i class="bxr bx-phone"></i>
                        </a>
                        <a href="#">
                            <i class="bxr bx-video"></i>
                        </a>
                        <a href="#" id="dropdown-toggle">
                            <i class="bxr bx-dots-vertical-rounded"></i>
                        </a>
                        <div id="dropdown-menu" class="dropdown-menu">
                            <ul>
                                <li><a id="clear-chat-btn" class="black" href="#">Chat leeren</a></li>
                                <li><a id="removeContactBtn" class="red" href="#">Chat löschen</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="messages"></div>
                
                <a href="#" class="scroll-down-btn">
                    <i class='bxr bx-arrow-down'></i> 
                </a>

                <div class="input-area">
                    <a class="emoji"><i class="bx bx-smile"></i></a>
                    <a class="attach" id="attachBtn"><i class="bx bx-paperclip"></i></a>
                    <input type="file" id="fileInput" style="display:none;" multiple>
                    <input type="text" id="messageInput" placeholder="Nachricht" />
                    <a class="sendMessageBtn" id="sendMessageBtn"><i class="bx bx-send-alt"></i></a>
                </div>
            </div>
        </div>

        <div id="addContactModal" class="modal-overlay">
            <div class="modal">
                <h2>Kontakt hinzufügen</h2>
                <form id="addContactForm" method="POST" action="assets/php/chat-system/add_contact.php">
                    <input type="text" id="contact_id" name="contact_id" placeholder="Benutzer ID" required>

                    <div class="modal-actions">
                        <button type="submit" id="addContactButton">Hinzufügen</button>
                        <button type="button" id="closeAddContactModal">Abbrechen</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputArea = document.querySelector('.input-area');
            const fileInput = document.getElementById('fileInput');
            const attachBtn = document.getElementById('attachBtn');
            const sendBtn = document.getElementById('sendMessageBtn');
            const messageInput = document.getElementById('messageInput');
            let currentFile = null;

            // Hilfsfunktion für Vertrauensfehler/SSL
            function showTrustError(flaskUrl) {
                if (document.getElementById("trust-error-modal-overlay")) return; // Nur einmal anzeigen

                const overlay = document.createElement('div');
                overlay.id = "trust-error-modal-overlay";

                const modal = document.createElement('div');
                modal.id = "trust-error-modal";
                modal.innerHTML = `
                    <strong>Verbindungsfehler!</strong><br>
                    Möglicherweise vertraut dein Browser dem Zertifikat des Servers nicht.<br><br>
                    <a href="${flaskUrl}/upload_messenger" target="_blank" rel="noopener">Hier klicken</a>, um die Seite zu öffnen und das Zertifikat zu akzeptieren.
                    <br>
                    <button onclick="document.getElementById('trust-error-modal-overlay').remove()">Meldung schließen</button>
                `;
                overlay.appendChild(modal);
                document.body.appendChild(overlay);
            }

            attachBtn.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', function() {
                if (this.files.length === 0) return;
                currentFile = this.files[0];
                showFileBubble(currentFile);
                this.value = '';
            });

            function showFileBubble(file) {
                removeFileBubble();
                const bubble = document.createElement('div');
                bubble.className = 'file-preview-bubble';
                bubble.innerHTML = `
                    <span class="fileicon"><i class="bx bx-file"></i></span>
                    <span class="filename">${file.name}</span>
                    <button class="remove-btn" title="Entfernen">&times;</button>
                    <div class="progress-container" style="display:none;"><div class="progressbar"></div></div>
                `;
                bubble.querySelector('.remove-btn').onclick = function() {
                    currentFile = null;
                    removeFileBubble();
                };
                inputArea.appendChild(bubble);
            }

            function removeFileBubble() {
                const prev = inputArea.querySelector('.file-preview-bubble');
                if (prev) prev.remove();
            }

            function updateProgress(percent) {
                const bar = inputArea.querySelector('.file-preview-bubble .progressbar');
                if (bar) bar.style.width = percent + "%";
            }

            async function sendMessage() {
                const message = messageInput.value.trim();

                // IDs holen
                const myChatUserId = document.getElementById('current-user-id').dataset.id;
                const contact = document.querySelector('.conversation.active');
                if (!contact) {
                    alert('Kein Chat ausgewählt!');
                    return;
                }
                const receiverUsername = contact.querySelector('.username').textContent.trim();
                const receiverId = await fetch('assets/php/chat-system/get_chat_user_id_by_username.php?chatUserUSERNAME=' + encodeURIComponent(receiverUsername))
                    .then(res => res.json())
                    .then(data => data.chat_user_id || null);
                if (!receiverId) {
                    alert('Empfänger-ID konnte nicht gefunden werden!');
                    return;
                }
                if (!window._config) {
                    window._config = await fetch('config.json').then(r => r.json());
                }
                const flaskUrl = window._config.flaskServerURL;

                // Prüfe, ob wenigstens Text oder Datei vorhanden ist
                if (!currentFile && message === "") return;

                let formData = new FormData();
                formData.append('owner_id', myChatUserId);
                formData.append('contact_id', receiverId);
                formData.append('message', message);
                if (currentFile) formData.append('file', currentFile);

                try {
                    // Mit Progressbar, falls Datei
                    if (currentFile) {
                        const progressContainer = inputArea.querySelector('.file-preview-bubble .progress-container');
                        if (progressContainer) progressContainer.style.display = "block";
                        updateProgress(0);

                        const progressbar = inputArea.querySelector('.progressbar');
                        await new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', flaskUrl + '/upload_messenger');
                            xhr.upload.onprogress = function(e) {
                                if (e.lengthComputable && progressbar) {
                                    const percent = Math.round((e.loaded / e.total) * 100);
                                    updateProgress(percent);
                                }
                            };
                            xhr.onload = function() {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    resolve();
                                } else {
                                    reject(new Error("Serverantwort: " + xhr.status));
                                }
                            };
                            xhr.onerror = function() {
                                reject(new Error("Verbindungsfehler"));
                            };
                            xhr.send(formData);
                        });
                    } else {
                        // Nur Text: fetch ohne Progressbar
                        const response = await fetch(flaskUrl + '/upload_messenger', {
                            method: 'POST',
                            body: formData
                        });
                        if (!response.ok) {
                            throw new Error("Serverantwort: " + response.status);
                        }
                    }

                    // Nach dem Senden alles zurücksetzen
                    removeFileBubble();
                    currentFile = null;
                    messageInput.value = "";
                    fileInput.value = "";
                    // Optional: Nachrichten neu laden, z.B.
                    // loadMessages(receiverId, {forceScroll: true});
                } catch (err) {
                    showTrustError(flaskUrl);
                }
            }

            sendBtn.addEventListener('click', sendMessage);

            // Enter-Handler für Textnachricht
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function getCurrentReceiverId() {
                const activeConversation = document.querySelector('.conversation.active');
                return activeConversation ? activeConversation.dataset.userid : null;
            }

            const removeContactBtn = document.getElementById('removeContactBtn');
            if (removeContactBtn) {
                removeContactBtn.addEventListener('click', (e) => {
                    e.preventDefault();

                    const receiverId = getCurrentReceiverId();
                    if (!receiverId) {
                        alert("Kein Kontakt ausgewählt.");
                        return;
                    }

                    if (!confirm("Möchtest du diesen Kontakt wirklich löschen?")) {
                        return;
                    }

                    fetch('assets/php/chat-system/remove_contact.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            contact_id: receiverId
                        }),
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Fehler beim Löschen des Kontakts");
                        return response.text();
                    })
                    .then(msg => {

                        // Konversation entfernen
                        const activeConv = document.querySelector('.conversation.active');
                        if (activeConv) activeConv.remove();

                        // Chat-Bereich leeren und ausblenden
                        const chatArea = document.querySelector('.chat-area');
                        if (chatArea) {
                            chatArea.classList.add('hidden');
                            const messagesContainer = chatArea.querySelector('.messages');
                            if (messagesContainer) {
                                messagesContainer.innerHTML = '<p>Kein Chat ausgewählt.</p>';
                            }

                            // Dropdown-Menü ausblenden
                            const toggle = document.getElementById('dropdown-toggle');
                            const menu = document.getElementById('dropdown-menu');
                            menu.classList.remove('show');

                            // Optional: Header leeren
                            const chatHeader = chatArea.querySelector('.chat-header .username');
                            if (chatHeader) {
                                chatHeader.textContent = '';
                            }

                            location.reload();
                        }
                    })
                    .catch(err => {
                        alert(err.message);
                    });
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Aktive Konversation setzen beim Klick
            document.querySelectorAll('.conversation').forEach(conv => {
                conv.addEventListener('click', () => {
                    document.querySelectorAll('.conversation').forEach(c => c.classList.remove('active'));
                    conv.classList.add('active');
                    
                    // Hier kannst du z.B. den Chat laden
                    const username = conv.dataset.username;
                    loadMessages(username); // Falls du die Funktion schon hast
                });
            });

            // 2. Funktion zum Auslesen der aktiven ReceiverId
            function getCurrentReceiverId() {
                const activeConversation = document.querySelector('.conversation.active');
                return activeConversation ? activeConversation.dataset.userid : null;
            }

            // 3. Eventlistener für "Chat leeren" Button
            const clearChatBtn = document.querySelector('li > a.black[href="#"]');
            if (clearChatBtn) {
                clearChatBtn.addEventListener('click', (e) => {
                    e.preventDefault();

                    const receiverId = getCurrentReceiverId();
                    if (!receiverId) {
                        alert("Kein Chat ausgewählt.");
                        return;
                    }

                    // AJAX POST Anfrage zum Chat-Leeren
                    fetch('assets/php/chat-system/clear_chat.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            receiver: receiverId
                        }),
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Fehler beim Löschen des Chats");
                        return response.text();
                    })
                    .then(data => {
                        // Chatbereich leeren
                        const messagesContainer = document.querySelector('.chat-area .messages');
                        clearChatUI();
                        loadMessages(currentReceiverId); // oder receiverUsername, je nach deiner Logik

                        // Dropdown-Menü ausblenden
                        const toggle = document.getElementById('dropdown-toggle');
                        const menu = document.getElementById('dropdown-menu');
                        menu.classList.remove('show');
                    })
                    .catch(err => {
                        alert(err.message);
                    });
                });
            }
        });

        // Beispiel-Funktion, um den Receiver zu bekommen
        function getCurrentReceiverId() {
            // Das hängt davon ab, wie du den aktuellen Chat/Empfänger verwaltest.
            // Beispiel: 
            const receiverElement = document.querySelector('#current-chat-receiver-id');
            return receiverElement ? receiverElement.dataset.receiverId : null;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('dropdown-toggle');
            const menu = document.getElementById('dropdown-menu');

            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                menu.classList.toggle('show');
            });

            // Klick außerhalb schließt das Dropdown
            document.addEventListener('click', (e) => {
                if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
        });
    </script>
    <script>
        const modal = document.getElementById("addContactModal");

        // Modal anzeigen
        document.getElementById("openAddContactModal").addEventListener("click", function () {
            modal.style.display = "flex";
            // Reflow erzwingen
            void modal.offsetWidth;
            modal.classList.add("visible");
        });

        // Modal schließen
        function closeModal() {
            modal.classList.remove("visible");
            // nach der Transition auch display:none setzen
            setTimeout(() => {
                modal.style.display = "none";
            }, 300);
        }

        // Klick auf Schließen-Button
        document.getElementById("closeAddContactModal").addEventListener("click", closeModal);

        // Klick außerhalb des Inhalts
        window.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Beispiel: Klick auf „Hinzufügen“-Button
        document.getElementById("addContactButton").addEventListener("click", function () {
            // Hier kannst du natürlich auch deine Logik zum Speichern einfügen
            closeModal();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contacts = document.querySelectorAll('.conversation-list .conversation');
            const chatArea = document.querySelector('.chat-area');
            const chatHeader = document.querySelector('.chat-header');

            contacts.forEach(contact => {
                contact.addEventListener('click', () => {
                    contacts.forEach(c => c.classList.remove('active'));
                    contact.classList.add('active');
                    chatArea.classList.remove('hidden');

                    const selectedUser = contact.querySelector('.username').textContent.trim();
                    chatHeader.querySelector('.chat-header-left').textContent = selectedUser;

                    loadMessages(selectedUser);
                });
            });
        });
    </script>
    <script>
        function loadMessages(receiverUsername, options = {forceScroll: false}) {
            const messagesContainer = document.querySelector('.chat-area .messages');
            const currentUserId = document.getElementById('current-user-id').dataset.id;
            const scrollDownBtn = document.querySelector('.scroll-down-btn');

            if (!receiverUsername || receiverUsername.trim() === "") {
                messagesContainer.innerHTML = '<p>Kein Chat ausgewählt.</p>';
                return;
            }

            // Prüfe vor dem Rendern, ob wir aktuell am unteren Ende sind
            const wasAtBottom = messagesContainer.scrollTop + messagesContainer.clientHeight >= messagesContainer.scrollHeight - 10;

            if (!window._displayedMessageIds) window._displayedMessageIds = [];
            const displayedMessageIds = window._displayedMessageIds;

            fetch(`assets/php/chat-system/get_chat_user_id_by_username.php?chatUserUSERNAME=${encodeURIComponent(receiverUsername)}`)
                .then(response => response.json())
                .then(data => {
                    const chatUserId = data.chat_user_id;

                    if (!chatUserId) {
                        messagesContainer.innerHTML = '<p>Benutzer-ID konnte nicht geladen werden.</p>';
                        return;
                    }

                    fetch(`assets/php/chat-system/get_messages.php?chatUserID=${encodeURIComponent(chatUserId)}`)
                        .then(response => response.json())
                        .then(messages => {
                            let appended = false;
                            messages.forEach(msg => {
                                if (!msg.id) return;
                                if (!displayedMessageIds.includes(msg.id)) {
                                    // Wrapper für Nachricht + Menü
                                    const rowDiv = document.createElement('div');
                                    rowDiv.classList.add('message-row', msg.sender === currentUserId ? 'sent' : 'received');
                                    rowDiv.dataset.msgId = msg.id;

                                    const bubbleDiv = document.createElement('div');
                                    bubbleDiv.classList.add('message-bubble');

                                    // Datei-Bubble, falls Anhang vorhanden
                                    if (msg.attachment_path) {
                                        const fileBubble = document.createElement('div');
                                        fileBubble.className = 'file-bubble';

                                        // Fileicon
                                        const fileIcon = document.createElement('span');
                                        fileIcon.className = 'fileicon';
                                        fileIcon.innerHTML = '<i class="bx bx-file"></i>';
                                        fileBubble.appendChild(fileIcon);

                                        // Info-Container
                                        const fileInfo = document.createElement('div');
                                        fileInfo.className = 'file-info';

                                        // Dateiname
                                        const fileNameSpan = document.createElement('span');
                                        fileNameSpan.className = 'file-name';
                                        const filename = msg.attachment_path.split('/').pop();
                                        fileNameSpan.textContent = filename;
                                        fileInfo.appendChild(fileNameSpan);

                                        // Platzhalter für Dateigröße
                                        const fileSizeSpan = document.createElement('span');
                                        fileSizeSpan.className = 'file-size';
                                        fileSizeSpan.textContent = '...'; // Ladeanzeige
                                        fileInfo.appendChild(fileSizeSpan);

                                        fileBubble.appendChild(fileInfo);

                                        // Download-Button
                                        const downloadBtn = document.createElement('a');
                                        downloadBtn.className = 'download-btn';
                                        downloadBtn.href = 'assets/php/chat-system/download.php?path=' + encodeURIComponent(msg.attachment_path);
                                        downloadBtn.setAttribute('download', filename);
                                        downloadBtn.title = 'Herunterladen';
                                        downloadBtn.innerHTML = '<i class="bx bx-download"></i>';
                                        downloadBtn.style.textDecoration = 'none'
                                        fileBubble.appendChild(downloadBtn);

                                        // Bubble einfügen
                                        bubbleDiv.appendChild(fileBubble);

                                        // === Dateigröße asynchron laden ===
                                        fetch('assets/php/chat-system/get_file_size.php?path=' + encodeURIComponent(msg.attachment_path))
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.size_formatted) {
                                                fileSizeSpan.textContent = data.size_formatted;
                                            } else {
                                                fileSizeSpan.textContent = '';
                                            }
                                        })
                                        .catch(() => {
                                            fileSizeSpan.textContent = '';
                                        });
                                    }

                                    // Nachrichtentext (falls vorhanden)
                                    if (msg.message) {
                                        const textSpan = document.createElement('span');
                                        textSpan.classList.add('message-text');
                                        textSpan.textContent = msg.message;
                                        bubbleDiv.appendChild(textSpan);
                                    }

                                    // Zeit
                                    if (msg.timestamp) {
                                        const time = new Date(msg.timestamp);
                                        const timeDiv = document.createElement('div');
                                        timeDiv.classList.add('message-time');
                                        timeDiv.textContent = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                        bubbleDiv.appendChild(timeDiv);
                                    }

                                    // Menü
                                    const actionsDiv = document.createElement('div');
                                    actionsDiv.classList.add('message-actions');

                                    let menuHTML = '';
                                    if (msg.sender === currentUserId) {
                                        menuHTML = `
                                        <button class="message-menu-toggle" title="Optionen">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="message-dropdown">
                                            <ul>
                                            <li><a href="#" class="edit-message">Bearbeiten</a></li>
                                            <li><a href="#" class="delete-message">Löschen</a></li>
                                            <li><a href="#" class="copy-message">Text kopieren</a></li>
                                            </ul>
                                        </div>
                                        `;
                                    } else {
                                        menuHTML = `
                                        <button class="message-menu-toggle" title="Optionen">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="message-dropdown">
                                            <ul>
                                            <li><a href="#" class="copy-message">Text kopieren</a></li>
                                            </ul>
                                        </div>
                                        `;
                                    }
                                    actionsDiv.innerHTML = menuHTML;

                                    // Zusammensetzen
                                    rowDiv.appendChild(bubbleDiv);
                                    rowDiv.appendChild(actionsDiv);

                                    messagesContainer.appendChild(rowDiv);
                                    displayedMessageIds.push(msg.id);
                                    appended = true;
                                }
                            });

                            // Nach dem Rendern ggf. scrollen:
                            if (options.forceScroll || (appended && wasAtBottom)) {
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                                scrollDownBtn.classList.remove('active');
                            } else if (appended) {
                                scrollDownBtn.classList.add('active');
                            }
                        })
                        .catch(() => {
                            messagesContainer.innerHTML = '<p>Fehler beim Laden der Nachrichten.</p>';
                        });
                })
                .catch(() => {
                    messagesContainer.innerHTML = '<p>Fehler beim Abrufen der Benutzer-ID.</p>';
                });
        }

        // Wenn du einen neuen Chat auswählst, solltest du das zurücksetzen:
        function resetMessagesContainer() {
            const messagesContainer = document.querySelector('.chat-area .messages');
            messagesContainer.innerHTML = '';
            window._displayedMessageIds = [];
        }

        function clearChatUI() {
            const messagesContainer = document.querySelector('.chat-area .messages');
            messagesContainer.innerHTML = '';
            window._displayedMessageIds = [];
        }

        // Event-Listener, damit der Button beim Klick nach unten scrollt
        document.querySelector('.scroll-down-btn').addEventListener('click', function (e) {
            e.preventDefault();
            const messagesContainer = document.querySelector('.chat-area .messages');
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
            this.classList.remove('active'); // Button ausblenden, weil du jetzt unten bist
        });

        // Optional: Scroll-Event, um Button dynamisch zu steuern, wenn Nutzer manuell scrollt
        document.querySelector('.chat-area .messages').addEventListener('scroll', function () {
            const scrollDownBtn = document.querySelector('.scroll-down-btn');
            const isAtBottom = this.scrollTop + this.clientHeight >= this.scrollHeight - 10;
            if (isAtBottom) {
                scrollDownBtn.classList.remove('active');
            } else {
                scrollDownBtn.classList.add('active');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const contacts = document.querySelectorAll('.conversation-list .conversation');
            const chatArea = document.querySelector('.chat-area');
            const chatHeader = document.querySelector('.chat-header');

            contacts.forEach(contact => {
                contact.addEventListener('click', () => {
                    contacts.forEach(c => c.classList.remove('active'));
                    contact.classList.add('active');
                    chatArea.classList.remove('hidden');

                    const selectedUser = contact.querySelector('.username').textContent.trim();
                    chatHeader.querySelector('.chat-header-left').textContent = selectedUser;

                    loadMessages(selectedUser);
                });
            });
        });

        function scrollToBottom(container) {
            container.scrollTop = container.scrollHeight;
        }

        function formatFileSize(bytes) {
            if (!bytes) return '';
            if (bytes >= 1048576)
                return (bytes/1048576).toFixed(1) + " MB";
            if (bytes >= 1024)
                return (bytes/1024).toFixed(1) + " KB";
            return bytes + " B";
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Menü öffnen/schließen
            document.querySelector('.chat-area .messages').addEventListener('click', function (e) {
                if (e.target.closest('.message-menu-toggle')) {
                e.stopPropagation();
                document.querySelectorAll('.message-dropdown').forEach(dd => dd.style.display = 'none');
                const btn = e.target.closest('.message-menu-toggle');
                const dropdown = btn.nextElementSibling;
                if (dropdown) dropdown.style.display = 'block';
                }
                // Kopieren
                if (e.target.matches('.message-dropdown a.copy-message')) {
                e.preventDefault();
                // Den Nachrichtentext finden
                const row = e.target.closest('.message-row');
                const text = row.querySelector('.message-text').textContent;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text)
                    .then(() => alert('Text kopiert!'))
                    .catch(() => alert('Kopieren fehlgeschlagen!'));
                } else {
                    alert('Ihr Browser unterstützt das Kopieren nicht.');
                }
                document.querySelectorAll('.message-dropdown').forEach(dd => dd.style.display = 'none');
                }
                // Bearbeiten
                if (e.target.matches('.message-dropdown a.edit-message')) {
                e.preventDefault();
                alert('Nachricht bearbeiten');
                document.querySelectorAll('.message-dropdown').forEach(dd => dd.style.display = 'none');
                }
                // Löschen
                if (e.target.matches('.message-dropdown a.delete-message')) {
                e.preventDefault();
                alert('Nachricht löschen');
                document.querySelectorAll('.message-dropdown').forEach(dd => dd.style.display = 'none');
                }
            });

            // Klick außerhalb schließt Dropdown
            document.addEventListener('click', () => {
                document.querySelectorAll('.message-dropdown').forEach(dd => dd.style.display = 'none');
            });
        });
    </script>
    <script>
        let pollingInterval = null;
        let currentReceiverId = null;

        // Hilfsfunktion, um alles zurückzusetzen (Nachrichtenfeld und IDs)
        function resetMessagesContainer() {
            const messagesContainer = document.querySelector('.chat-area .messages');
            messagesContainer.innerHTML = '';
            window._displayedMessageIds = [];
        }

        document.querySelectorAll('.conversation-list .conversation').forEach(contact => {
            contact.addEventListener('click', () => {
                // Markiere aktive Konversation
                document.querySelectorAll('.conversation-list .conversation').forEach(c => c.classList.remove('active'));
                contact.classList.add('active');

                // Chatbereich einblenden
                const chatArea = document.querySelector('.chat-area');
                chatArea.classList.remove('hidden');

                // Chatheader aktualisieren
                const chatHeader = document.querySelector('.chat-header');
                if (chatHeader && chatHeader.querySelector('.chat-header-left')) {
                    chatHeader.querySelector('.chat-header-left').textContent = contact.querySelector('.username').textContent.trim();
                }

                // Aktuelle Receiver-Id (hier: Username oder UserId, je nach Bedarf für loadMessages)
                const receiverUsername = contact.querySelector('.username').textContent.trim();
                currentReceiverId = receiverUsername;

                // Beim Wechsel alles zurücksetzen
                resetMessagesContainer();

                // Initial laden
                loadMessages(currentReceiverId);

                // Vorheriges Polling stoppen
                if (pollingInterval) clearInterval(pollingInterval);

                // Polling-Intervall setzen (nur für den aktiven Chat)
                pollingInterval = setInterval(() => {
                    if (currentReceiverId) {
                        loadMessages(currentReceiverId);
                    }
                }, 1000); // 1 Sekunde ist oft ausreichend, 500ms ist recht viel
            });
        });
    </script>
</body>
</html>
