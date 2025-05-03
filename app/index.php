<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#12002F">
	<title>My NAS | Startseite</title>
	<link rel="website icon" href="Logo/Logo_512px.png">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

	<link rel="stylesheet" href="assets/css/index.css" />
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
			<?php if(isset($_SESSION["id"])): ?>
				<div class="logged-in">
					<div class="greeting">
						<strong><? include 'assets/php/greeting.php'; ?></strong>
					</div>

					<div class="recent-files">
						<h1 style="margin-bottom: 15px;">Zuletzt verwendete Dateien:</h1>
						<ul class="file-list">
							<? include 'assets/php/recent_files.php'; ?>
						</ul>
					</div>

					<div class="shared-files">
						<h1 style="margin-top: 30px; margin-bottom: 15px;">Geteilte Dateien:</h1>
						<!-- Platz für geteilte Dateien -->
					</div>
				</div>
			<?php else: ?>
				<div class="logged-out">
					<div class="greeting">
						<strong><? include 'assets/php/greeting.php'; ?></strong>
						<br>
						<div class="sub-text">
							<p>Der <span>sicheren Cloud</span> in deinem Netzwerk</p>
							<p>Speichere deine Daten sicher auf deinem <span>privaten</span> Server</p>
							<br>
							<p><a class="register" href="account-system/register.php">Registrieren</a></p>
						</div>
					</div>
				</div>

				<div class="other-content">
					<div class="mobile-app">
						<p><strong>Entdecke auch unsere Mobile App:</strong></p>
						<a class="github-link" href="https://github.com/MarcusNebel/My-NAS-Flutter-App"><img class="github-logo" src="Logo/github-mark-white.png" alt="png">My NAS Mobile</a>
						<p>Downloade die App in den <a class="release-link" href="https://github.com/MarcusNebel/My-NAS-Flutter-App/releases">Releases</a>.</p>
					</div>
				</div>
			<?php endif; ?>
	</main>
	<script src="assets/js/main.js"></script>
	<script src="../assets/js/update.js"></script>
	<script src="assets/js/lang.js"></script>
</body>
</html>