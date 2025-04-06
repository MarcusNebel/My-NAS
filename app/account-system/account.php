<?php
session_start();

// Benutzer nicht eingeloggt? Dann umleiten
if (!isset($_SESSION["id"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"];
    header("Location: Login.php");
    exit();
}

// Datenbankverbindung
require("mysql.php");

// API-Schlüssel generieren
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["api-submit"])) {
    $stmt = $mysql->prepare("SELECT api_key FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $row = $stmt->fetch();

    if (empty($row["api_key"])) {
        $prefix = "myn"; 
        $random_part = bin2hex(random_bytes(30));
        $api_key = $prefix . "-" . $random_part;

        $stmt = $mysql->prepare("UPDATE accounts SET api_key = :api_key WHERE ID = :id");
        $stmt->execute(array(":api_key" => $api_key, ":id" => $_SESSION["id"]));

        // Nach dem Generieren umleiten
        header("Location: account.php");
        exit();
    }
}

// Account löschen
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["delete-account"])) {
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $output = $stmt->fetch();
    $username = $output["USERNAME"] ?? "Error: Benutzer konnte nicht aus der Datenbank gelesen werden!";

    $stmt = $mysql->prepare("DELETE FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));

    $uploadDir = "/home/nas-website-files/user_files/";
    $userDir = $uploadDir . $username . "/";

    if (is_dir($userDir)) {
        rmdir($userDir);
    }

    // Cookie und Session löschen
    setcookie("login_cookie", "", time() - 3600);
    session_destroy();

    // Umleiten zur Startseite, damit "delete-account" nicht in der URL bleibt
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My NAS | Mein Account</title>
	<link rel="website icon" href="../Logo/Logo_512px.png">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

	<link rel="stylesheet" href="../assets/css/account.css">
</head>
<body class="account-body">
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
	<main>
		<section class="account-section">
			<div class="container_account">
				<h4>Persönliche Daten:</h4>
				<?php 
				if(isset($_SESSION["id"])){
					if(!empty($_SESSION["id"])){
						require("mysql.php");
						if(isset($_POST["pd-submit"])){
							$stmt = $mysql->prepare("UPDATE accounts SET USERNAME = :username, EMAIL = :email WHERE ID = :id");
							$stmt->execute(array(":username" => $_POST["username"], ":email" => $_POST["email"], ":id" => $_GET["id"]));
						}
						$stmt = $mysql->prepare("SELECT * FROM accounts WHERE ID = :id");
						$stmt->execute(array(":id" => $_SESSION["id"]));
						$row = $stmt->fetch();
						?>
						<form class="account-form" action="account.php?id=<?php echo $_SESSION["id"] ?>" method="post">
							<div class="input-box-account">
								<input type="text" name="username" value="<?php echo $row["USERNAME"] ?>" placeholder="Benutzername" require><br>
							</div>
							<div class="input-box-account">
								<input type="email" name="email" value="<?php echo $row["EMAIL"] ?>" placeholder="Email" require><br>
							</div>
							<button class="btn-submit" name="pd-submit" type="submit">Speichern</button>
						</form>
						<?php
					} else {
						//account.php?username
						?>
						<p>Es ist kein Benutzer angemeldet</p>
						<?php
					}
				} else {
					//account.php
					?>
					<p>Es ist kein Benutzer angemeldet</p>
					<?php
				}
				?>

				<hr style="border: 1px solid #ccc; margin: 20px 0;">

				<h4>API Einstellungen:</h4>
				<?php 
				if (isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
					require("mysql.php");

					if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["api-submit"]) && empty($row["api_key"])) {
						// Erstellen des API-Schlüssels mit drei festen Buchstaben und dann zufälligen Zeichen
						$prefix = "myn"; // Die festen Buchstaben am Anfang
						$random_part = bin2hex(random_bytes(30)); // Zufällige Zeichen, hexadezimal, 30 Bytes (also 60 Zeichen)
						$api_key = $prefix . "-" . $random_part;

						$stmt = $mysql->prepare("UPDATE accounts SET api_key = :api_key WHERE ID = :id");
						$stmt->execute(array(":api_key" => $api_key, ":id" => $_SESSION["id"]));
					}                    

					$stmt = $mysql->prepare("SELECT api_key FROM accounts WHERE ID = :id");
					$stmt->execute(array(":id" => $_SESSION["id"]));
					$row = $stmt->fetch();
					$api_key = $row["api_key"] ?? "Kein API-Schlüssel vorhanden";
					?>

					<form class="api-form" method="get">
						<button class="api-btn-submit" name="api-submit" type="submit"><i class='bx bxs-plus-circle'></i></button>
						<a href="../api/delete_api-key.php?api_key=<?php echo urlencode($api_key); ?>" 
							class="api-btn-delete" 
							onclick="return confirm('Möchten Sie den API Key wirklich löschen?')">
							<i class="bx bxs-trash"></i>
						</a>
						<p><i id="copyButton" onclick="copyApiKey()" class='bx bxs-copy copy-icon'></i></p>
					</form>

					<ul class="api-list">
						<p>Dein API-Schlüssel: <br> <strong id="apiKey"><?php echo htmlspecialchars($api_key); ?></strong></p>
					</ul>

					<?php
				} else {
					echo "<p>Es ist kein Benutzer angemeldet</p>";
				}
				?>

				<hr style="border: 1px solid #ccc; margin: 20px 0;">

				<h4>Erweiterte Einstellungen:</h4>
				<?php
				if(isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
					require("mysql.php");

					$stmt = $mysql->prepare("SELECT server_rank FROM accounts WHERE ID = :id");
					$stmt->execute(array(":id" => $_SESSION["id"]));
					$row = $stmt->fetch();
					$server_rank = $row["server_rank"] ?? "Error: Du hast keinen Rang auf dem aktuellen Server!";
				}
				?>
				<div class="advanced-settings">
					<p class="server-rank"><?php 
					if(isset($server_rank) && $server_rank === "Error: Du hast keinen Rang auf dem aktuellen Server!") {
						echo $server_rank;
					} else {
						echo "Dein Server Rang ist: ", $server_rank;
					}
					?> </p>
					
					<h4>Sprache:</h4>
					<select id="lang-switcher">
						<option value="en">English</option>
						<option value="de">Deutsch</option>
						<!-- Weitere Sprachen hier -->
					</select>

					<h5>Account löschen</h5>
					<form class="delete-account-form" method="get">
						<button class="delete-account-btn" name="delete-account" type="submit">Account löschen</button>
					</form>
						<hr style="border: 1px solid #ccc; margin: 20px 0;">
						<h4>Admin Einstellungen:</h4>
						<a class="all-accounts" href="accounts-list.php">Alle Accounts</a>
				</div>
			</div>
		</section>
	</main>
	<script src="../assets/js/main.js"></script>
	<script src="../assets/js/lang.js"></script>
</body>
</html>
