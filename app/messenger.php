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
                    <a class="emoji"><i class="bxr bx-smile"></i></a>
                    <a class="attach"><i class="bxr bx-paperclip"></i></a>
                    <input type="text" id="messageInput" placeholder="Nachricht" />
                    <a class="sendMessageBtn" id="sendMessageBtn"><i class="bxr bx-send-alt"></i></a>
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
        function loadMessages(receiverUsername) {
            const messagesContainer = document.querySelector('.chat-area .messages');
            const currentUserId = document.getElementById('current-user-id').dataset.id;
            const scrollDownBtn = document.querySelector('.scroll-down-btn');

            if (!receiverUsername || receiverUsername.trim() === "") {
                messagesContainer.innerHTML = '<p>Kein Chat ausgewählt.</p>';
                return;
            }

            // Schritt 1: Hole die chat_user_id anhand des Benutzernamens
            fetch(`assets/php/chat-system/get_chat_user_id_by_username.php?chatUserUSERNAME=${encodeURIComponent(receiverUsername)}`)
                .then(response => response.json())
                .then(data => {
                    const chatUserId = data.chat_user_id;

                    if (!chatUserId) {
                        messagesContainer.innerHTML = '<p>Benutzer-ID konnte nicht geladen werden.</p>';
                        return;
                    }

                    // Schritt 2: Hole die Nachrichten
                    fetch(`assets/php/chat-system/get_messages.php?chatUserID=${encodeURIComponent(chatUserId)}`)
                        .then(response => response.json())
                        .then(messages => {
                            const isAtBottom = messagesContainer.scrollTop + messagesContainer.clientHeight >= messagesContainer.scrollHeight - 10;

                            messagesContainer.innerHTML = '';

                            messages.forEach(msg => {
                                const messageDiv = document.createElement('div');
                                messageDiv.classList.add('message');
                                messageDiv.classList.add(msg.sender === currentUserId ? 'sent' : 'received');

                                if (msg.attachment_path) {
                                    const attachmentLink = document.createElement('a');
                                    attachmentLink.href = msg.attachment_path;
                                    attachmentLink.textContent = '[Datei]';
                                    attachmentLink.target = '_blank';
                                    messageDiv.appendChild(attachmentLink);
                                }

                                if (msg.message) {
                                    const textNode = document.createTextNode(msg.message);
                                    messageDiv.appendChild(textNode);
                                }

                                if (msg.timestamp) {
                                    const time = new Date(msg.timestamp);
                                    const timeDiv = document.createElement('div');
                                    timeDiv.classList.add('message-time');
                                    timeDiv.textContent = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                    messageDiv.appendChild(timeDiv);
                                }

                                messagesContainer.appendChild(messageDiv);
                            });

                            if (isAtBottom) {
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                                scrollDownBtn.classList.remove('active');
                            } else {
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
    </script>
    <script>
        document.getElementById('sendMessageBtn').addEventListener('click', () => {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            const activeChat = document.querySelector('.conversation.active');
            const receiverId = activeChat?.dataset.userid;

            if (message && receiverId) {
                fetch('assets/php/chat-system/send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        message: message,
                        receiver: receiverId
                    })
                })
                .then(res => res.text())
                .then(response => {
                    console.log('Send message response:', response);
                    messageInput.value = '';

                    // Nach dem Senden neu laden!
                    
                })
                .catch(err => console.error('Fehler beim Senden der Nachricht:', err));
            }
        });
    </script>
    <script>
        document.getElementById('messageInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('sendMessageBtn').click();
                loadMessages(currentReceiverId);
            }
        });
    </script>
    <script>
        let pollingInterval;
        let currentReceiverId = null;

        document.querySelectorAll('.conversation-list .conversation').forEach(contact => {
            contact.addEventListener('click', () => {
                document.querySelectorAll('.conversation-list .conversation').forEach(c => c.classList.remove('active'));
                contact.classList.add('active');

                const chatArea = document.querySelector('.chat-area');
                chatArea.classList.remove('hidden');

                const chatHeader = document.querySelector('.chat-header');
                chatHeader.querySelector('.chat-header-left').textContent = contact.textContent.trim();

                currentReceiverId = contact.dataset.userid;
                loadMessages(currentReceiverId);

                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(() => {
                    if (currentReceiverId) {
                        document.querySelector('.chat-area').classList.remove('hidden');
                        loadMessages(currentReceiverId);
                    }
                }, 500);
            });
        });
    </script>
</body>
</html>
