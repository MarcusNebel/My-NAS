<?php
session_start();
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
        <section class="chat-container">
            <div class="chat-box" id="chat-box">
                <!-- Nachrichten werden hier geladen -->
            </div>
            <div class="chat-input">
                <input type="text" id="message" placeholder="Nachricht eingeben..." />
                <button id="send-btn">Senden</button>
            </div>
        </section>
    </main>
    
    <script>
        document.getElementById("send-btn").addEventListener("click", function() {
            let message = document.getElementById("message").value;
            if (message.trim() !== "") {
                let chatBox = document.getElementById("chat-box");
                let messageElement = document.createElement("div");
                messageElement.classList.add("chat-message");
                messageElement.textContent = message;
                chatBox.appendChild(messageElement);
                document.getElementById("message").value = "";
            }
        });
    </script>
	<script src="assets/js/lang.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
