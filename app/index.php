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

	<link rel="stylesheet" href="assets/css/index.css" />
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
		<section class="banner">
			<div class="container">
				<h1>
					Die sicherste Webseite <br class="hide-mob" />
					für <span>DEINE</span> Daten
					<br>
					<a class="subtitle">Die sichere Cloud in deinem Netzwerk</a>
				</h1>
			</div>
		</section>
		<div id="scroll-indicator">
			<svg width="100" height="100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 16l-4-4h8l-4 4z" fill="currentColor"/>
			</svg>
			<p>Dashboard</p>
		</div>
		<section class="dashboard">
			<div class="container-dashboard">
				<div class="flex">
					<div class="chart-container card">
						<h3>CPU Auslastung</h3>
						<canvas id="cpuChart"></canvas>
					</div>
					<div class="chart-container card">
						<h3>RAM Nutzung</h3>
						<canvas id="ramChart"></canvas>
					</div>
				</div>
			</div>

			<div class="other-content">
				<div class="card">
					<h3 data-lang="storage">Speicherplatz</h3>
					<p>
						<?php 
							echo nl2br(shell_exec("df -h --output=size,used,pcent,avail / | tail -1 | awk '{print \"Festplattengröße: \" $1 \"\\nBenutzter Speicher: \" $2 \" (\" $3 \")\\nVerfügbarer Speicher: \" $4}'")); 
						?>
					</p>
				</div>
				<div class="card" id="weather-card">
					<p id="weather-hint" style="color: red; display: none;">Bitte konfiguriere das Wetter auf der Seite „<a style="text-decoration: none; color: #2489f4;" href="account-system/account.php">Mein Account</a>“, um die Wetterdaten hier anzuzeigen.</p>
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
		</section>
	</main>
	<script src="assets/js/main.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const ctxCpu = document.getElementById('cpuChart').getContext('2d');
			const ctxRam = document.getElementById('ramChart').getContext('2d');
			document.getElementById('cpuChart').style.height = '400px';
			document.getElementById('cpuChart').style.width = '100%';
			document.getElementById('ramChart').style.height = '400px';
			document.getElementById('ramChart').style.width = '100%';

			let cpuChart = new Chart(ctxCpu, {
				type: 'line',
				data: { labels: [], datasets: [{ label: 'CPU (%)', borderColor: '#2894f4', data: [], fill: false }] },
				options: { responsive: true }
			});

			let ramChart = new Chart(ctxRam, {
				type: 'line',
				data: { labels: [], datasets: [{ label: 'RAM (MB)', borderColor: 'green', data: [], fill: false }] },
				options: { responsive: true }
			});

			function updateStats() {
				fetch('assets/php/stats.php')
					.then(response => response.json())
					.then(data => {
						let time = new Date().toLocaleTimeString();
						cpuChart.data.labels.push(time);
						cpuChart.data.datasets[0].data.push(data.cpu);
						if (cpuChart.data.labels.length > 10) {
							cpuChart.data.labels.shift();
							cpuChart.data.datasets[0].data.shift();
						}
						cpuChart.update();
						ramChart.data.labels.push(time);
						ramChart.data.datasets[0].data.push(data.ramUsed);
						if (ramChart.data.labels.length > 10) {
							ramChart.data.labels.shift();
							ramChart.data.datasets[0].data.shift();
						}
						ramChart.update();
					});
			}
			setInterval(updateStats, 2000);
			updateStats();
		});
	</script>
	<script src="assets/js/lang.js"></script>
</body>
</html>