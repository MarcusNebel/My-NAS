<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="../assets/css/error_pages.css">
    <title>Seite nicht gefunden</title>
</head>
<body>
    <header>
		<div class="container transparancy">
      		<h2><a class="link-no-decoration" href="../index.php"><span>MY </span>NAS</a></h2>
			<nav>
				<a class="hover-underline-animation left" href="../index.php" data-lang="home">Startseite</a>
				<a class="hover-underline-animation left" href="../User_Files.php" data-lang="files">Meine Dateien</a>
				<a class="hover-underline-animation left" href="../messenger.php" data-lang="messenger">Messenger</a>
				<?php if(isset($_SESSION["id"])): ?>
					<a class="hover-underline-animation left" href="../account-system/account.php" data-lang="account">Mein Account</a>
				<?php else: ?>
					<a class="hover-underline-animation left" href="../account-system/Login.php" data-lang="account">Mein Account</a>
				<?php endif; ?>
				<a class="hover-underline-animation left" href="../Contact_Page/Contact_Page.php" data-lang="contact">Kontakt</a>
			</nav>
			<?php if (isset($_SESSION["id"])): ?>
                <a class="login_button" href="../account-system/logout.php" data-lang="logout">Abmelden</a>
            <?php else: ?>
                <a class="login_button" href="../account-system/Login.php" data-lang="login">Anmelden</a>
            <?php endif; ?>
			<button class="hamburger">
				<div class="bar"></div>
			</button>
		</div>
	</header>
	<nav class="mobile-nav">
		<a href="../index.php" data-lang="home">Startseite</a>
		<a href="../User_Files.php" data-lang="files">Meine Dateien</a>
		<a href="../messenger.php" data-lang="messenger">Messenger</a>
		<?php if(isset($_SESSION["id"])): ?>
			<a href="../account-system/account.php" data-lang="account">Mein Account</a>
		<?php else: ?>
			<a href="../account-system/Login.php" data-lang="account">Mein Account</a>
		<?php endif; ?>
		<a href="../Contact_Page/Contact_Page.php" data-lang="contact">Kontakt</a>
	</nav>
	
	<div class="message">
		<h1>404</h1>
		<h2>Page not found</h2>
		<p>The requested page could not be found. <a href="../index.php">Zurück zur Startseite</a></p>
	</div>
	<script src="../assets/js/main.js"></script>
	<script src="assets/js/lang.js"></script>
</body>
</html>
