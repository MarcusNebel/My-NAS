<?php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
    require("mysql.php");
    if(isset($_COOKIE["login_cookie"])){
     $stmt = $mysql->prepare("SELECT * FROM accounts WHERE rememberTOKEN = ?");
     $stmt->execute([$_COOKIE["login_cookie"]]);
      if($stmt->rowCount() == 1){
       $row = $stmt->fetch();
        session_start();
       $_SESSION["id"] = $row["ID"];
       header("Location: ../index.php");
       exit();
     } else {
       setcookie("login_cookie", "", time() -1);
     }
   }
    if(isset($_POST["submit"])){
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
          if(isset($_POST["rememberme"])){
           $token = bin2hex(random_bytes(32));
            $stmt = $mysql->prepare("UPDATE accounts SET rememberTOKEN = ? WHERE USERNAME = ?");
           $stmt->execute([$token, $_POST["username"]]);
            setcookie("login_cookie", $token, time() + (3600*24*30));
         }
          session_start();
         $_SESSION["id"] = $row["ID"];
         
         // Prüfen, ob eine Zielseite gespeichert ist
         $redirect_to = isset($_SESSION["redirect_to"]) ? $_SESSION["redirect_to"] : "../index.php";
         unset($_SESSION["redirect_to"]); // Nach dem Login löschen
         
         header("Location: " . $redirect_to);
         exit();
       } else {
         $passwordError = "Falsches Passwort";
       }
     } else {
       $userError = "Benutzer oder Email nich vergeben";
     }
   }
  ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Anmelden</title>
    <link rel="website icon" href="../Logo/Logo_512px.png">
    <link rel="stylesheet" href="../assets/css/Login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
	  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	  <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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

    <div class="wrapper">
      <form action="Login.php" method="post">
        <h1>Anmelden</h1>
        <?php
        require("mysql.php");

        // Überprüfen, ob die Tabelle leer ist
        $stmt = $mysql->prepare("SELECT COUNT(*) FROM accounts");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            echo "<p><strong>Es gibt noch keinen Account! Bitte erstelle den ersten Account auf der Registrieren Seite</strong></p>";
        }
        ?>
      <?php if (!empty($userError)): ?>
        <p class="error-message"><?php echo $userError; ?></p>
      <?php endif; ?>
      <?php if (!empty($passwordError)): ?>
        <p class="error-message"><?php echo $passwordError; ?></p>
      <?php endif; ?>
        <div class="input-box">
          <input type="text" name="username" placeholder="Benutzername / Email" required>
          <i class='bx bxs-user'></i>
        </div>

        <div class="input-box">
          <input type="password" name="pw" placeholder="Passwort" required>
          <i class='bx bxs-lock-alt' ></i>
        </div>

        <div class="remember-forgot">
          <label><input type="checkbox" name="rememberme">Angemeldet bleiben</label>
          <a href="forgot_password.php">Passwort vergessen?</a>
        </div>
        <button type="submit" name="submit" class="btn">Anmelden</button>
        <div class="register-link">
          <p>Noch keinen Account? <a href="register.php">Registrieren</a></p>
        </div>
      </form>
    </div>
    <script src="../assets/js/main.js"></script>
    <script src="assets/js/lang.js"></script>
  </body>
</html>