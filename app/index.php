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
				<a class="hover-underline-animation left" href="index.php">Startseite</a>
				<a class="hover-underline-animation left" href="User_Files.php">Meine Dateien</a>
				<a class="hover-underline-animation left" href="File_upload.php">Dateien hochladen</a>
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
		<a href="User_Files.php">Meine Dateien</a>
		<a href="File_upload.php">Dateien hochladen</a>
		<?php if(isset($_SESSION["id"])): ?>
			<a href="account-system/account.php">Mein Account</a>
		<?php else: ?>
			<a href="account-system/Login.php">Mein Account</a>
		<?php endif; ?>
		<a href="Contact_Page/Contact_Page.php">Kontakt</a>
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
		<div id="scroll-indicator">
			<svg width="100" height="100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 16l-4-4h8l-4 4z" fill="currentColor"/>
			</svg>
		</div>
		<section class="dashboard">
				<div class="container">
					<div class="grid">
						<div class="card">
							<h3>CPU Last</h3>
							<p><?php echo shell_exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2 + $4}'").'%'; ?></p>
						</div>
						<div class="card">
							<h3>RAM Nutzung</h3>
							<p><?php echo shell_exec("free -m | awk 'NR==2{print $3 \"MB / \" $2 \"MB\"}'"); ?></p>
						</div>
						<div class="card">
							<h3>Speicherplatz</h3>
							<p><?php echo shell_exec("df -h | grep '/$'"); ?></p>
						</div>
						<div class="card" id="weather-card">
							<h3>Wetter in <span id="city">...</span></h3>
							<p>Temperatur: <span id="temp">...</span>°C</p>
							<p>Bedingung: <span id="condition">...</span></p>
							<p>Höchsttemperatur: <span id="max-temp"></span></p>
							<p>Tiefsttemperatur: <span id="min-temp"></span></p>

							<h3>Vorschau</h3>
							<div id="hourly-forecast-container">
								<ul id="hourly-forecast"></ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>
	<script src="assets/js/main.js"></script>
</body>
</html>