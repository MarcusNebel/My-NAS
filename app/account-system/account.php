<?php
session_start();
if (!isset($_SESSION["id"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"]; // Speichert die aktuelle Seite
    header("Location: Login.php"); // Weiterleitung zur Login-Seite
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My NAS | Mein Account</title>
	<link rel="website icon" href="../Logo/Logo_512px.png">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body class="account-body">
	<header>
		<div class="container transparancy">
      		<h2><a class="link-no-decoration" href="../index.php"><span>MY </span>NAS</a></h2>
			<nav>
				<a href="../index.php">Startseite</a>
				<a href="../User_Files.php">Meine Dateien</a>
				<a href="../File_upload.php">Dateien hochladen</a>
				<?php if(isset($_SESSION["id"])): ?>
					<a href="account.php">Mein Account</a>
				<?php else: ?>
					<a href="Login.php">Mein Account</a>
				<?php endif; ?>
				<a href="#">Kontakt</a>
			</nav>
			<?php if (isset($_SESSION["id"])): ?>
                <button class="login_button">
                    <a href="logout.php">Abmelden</a>
                </button>
            <?php else: ?>
                <button class="login_button">
                    <a href="Login.php">Anmelden</a>
                </button>
            <?php endif; ?>
			<button class="hamburger">
				<div class="bar"></div>
			</button>
		</div>
	</header>
	<nav class="mobile-nav">
		<a href="../index.php">Startseite</a>
		<a href="../User_Files.php">Meine Dateien</a>
		<a href="../File_upload.php">Dateien hochladen</a>
				<?php if(isset($_SESSION["id"])): ?>
			<a href="account.php">Mein Account</a>
		<?php else: ?>
			<a href="Login.php">Mein Account</a>
		<?php endif; ?>
		<a href="#">Kontakt</a>
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
						$api_key = bin2hex(random_bytes(32));
						$stmt = $mysql->prepare("UPDATE accounts SET api_key = :api_key WHERE ID = :id");
						$stmt->execute(array(":api_key" => $api_key, ":id" => $_SESSION["id"]));
					}					

					$stmt = $mysql->prepare("SELECT api_key FROM accounts WHERE ID = :id");
					$stmt->execute(array(":id" => $_SESSION["id"]));
					$row = $stmt->fetch();
					$api_key = $row["api_key"] ?? "Kein API-Schlüssel vorhanden";
					?>

					<form class="api-form" method="get">
						<button class="api-btn-submit" name="api-submit" type="submit">Neuen API-Schlüssel generieren</button>
						<a href="../api/delete.php?api_key=<?php echo urlencode($api_key); ?>" 
							class="api-btn-delete" 
							onclick="return confirm('Möchten Sie den API Key wirklich löschen?')">
							<i class="bx bxs-trash"></i>
						</a>
					</form>

					<ul class="api-list">
						<p>Dein API-Schlüssel: <strong><?php echo htmlspecialchars($api_key); ?></strong></p>
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
					<h5>Account löschen</h5>
					<form class="delete-account-form" method="get">
						<button class="delete-account-btn" name="delete-account" type="submit">Account löschen</button>
					</form>
					<?php
					if(isset($server_rank, $_SESSION["id"]) && !empty($server_rank)) if($server_rank === "Admin") {
						if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["delete-account"])) {
							require("mysql.php");

							$stmt = $mysql->prepare("DELETE FROM accounts WHERE ID = :id");
							$stmt->execute(array(":id" => $_SESSION["id"]));
							$stmt->execute();

							session_destroy();
						}
						?>
						<hr style="border: 1px solid #ccc; margin: 20px 0;">
						<h4>Admin Einstellungen:</h4>
						<a class="all-accounts" href="accounts-list.php">Alle Accounts</a>
						<?php
					}
					?>
				</div>
			</div>
		</section>
	</main>
	<script src="../assets/js/main.js"></script>
</body>
</html>