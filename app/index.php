<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#12002F">
	<title>My NAS | Startseite</title>
	<link rel="website icon" href="Logo/Logo_512px.png">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />

	<link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
	<header>
		<div class="container transparancy">
      		<h2><a class="link-no-decoration" href="index.php"><span>MY </span>NAS</a></h2>
			<nav>
				<a href="index.php">Startseite</a>
				<a href="User_Files.php">Meine Dateien</a>
				<a href="File_upload.php">Dateien hochladen</a>
				<?php if(isset($_SESSION["id"])): ?>
					<a href="account-system/account.php">Mein Account</a>
				<?php else: ?>
					<a href="account-system/Login.php">Mein Account</a>
				<?php endif; ?>
				<a href="#">Kontakt</a>
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
		<a href="#">Kontakt</a>
	</nav>
	<main>
		<section class="banner">
			<div class="container">
				<h1>
					Die unsicherste Webseite <br class="hide-mob" />
					für <span>DEINE</span> Daten
					<br>
					<a class="subtitle">Die unsichere Cloud in deinem Netzwerk</a>
				</h1>
			</div>
		</section>
	</main>
	<script src="assets/js/main.js"></script>
</body>
</html>