<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden | My NAS</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../Main_Website/assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  </head>
  <body>
    <?php
    if(isset($_POST["submit"])){
      require("mysql.php");

      // Eingabe holen und prüfen, ob es eine E-Mail oder ein Benutzername ist
      $input = $_POST["username"];
      if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        // Eingabe ist eine E-Mail-Adresse
        $stmt = $mysql->prepare("SELECT * FROM accounts WHERE EMAIL = :input");
      } else {
        // Eingabe ist ein Benutzername
        $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :input");
      }

      $stmt->bindParam(":input", $input);
      $stmt->execute();
      $count = $stmt->rowCount();

      if($count == 1){
        // Benutzer gefunden
        $row = $stmt->fetch();
        if(password_verify($_POST["pw"], $row["PASSWORD"])){
          session_start();
          $_SESSION["username"] = $row["USERNAME"];
          header("Location: ../Main_Website/geheim.php");
        } else {
          echo "Der Login ist fehlgeschlagen";
        }
      } else {
        echo "Der Login ist fehlgeschlagen";
      }
    }
    ?>
    
  <header>
		<div class="container transparancy">
			<h2><span>MY </span>NAS</h2>
			<nav>
				<a href="../Main_Website/index.php">Startseite</a>
				<a href="#">Dateien</a>
				<a href="#">Bilder</a>
				<a href="#">Kontakt</a>
			</nav>
			<button class="login_button">
				<a href="../Login/register.php">Registrieren</a>
			</button>
			<button class="hamburger">
				<div class="bar"></div>
			</button>
		</div>
	</header>
	<nav class="mobile-nav">
		<a href="../Main_Website/index.php">Startseite</a>
    <a href="../Login/index.php">Anmelden</a>
		<a href="../Login/register.php">Registrieren</a>
		<a href="#">Dateien</a>
		<a href="#">Bilder</a>
		<a href="#">Kontakt</a>
	</nav>

    <div class="wrapper">
      <form action="index.php" method="post">
        <h1>Anmelden</h1>
        <div class="input-box">
          <input type="text" name="username" placeholder="Benutzername" required>
          <i class='bx bxs-user'></i>
        </div>
        <div class="input-box">
          <input type="password" name="pw" placeholder="Passwort" required>
          <i class='bx bxs-lock-alt' ></i>
        </div>

        <!-- Fehlernachricht unter dem letzten Eingabefeld anzeigen -->
        <?php if(!empty($error)): ?>
          <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="remember-forgot">
          <label><input type="checkbox">Angemeldet bleiben</label>
          <a href="forgot_password.php">Passwort vergessen?</a>
        </div>
        <button type="submit" name="submit" class="btn">Anmelden</button>
        <div class="register-link">
          <p>Noch keinen Account? <a href="register.php">Registrieren</a></p>
        </div>
      </form>
    </div>

    <script src="script.js"></script>
    <script src="../Main_Website/assets/js/main.js"></script>
  </body>
</html>