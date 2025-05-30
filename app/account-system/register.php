<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Anpassen des Pfads zu PHPMailer
require '../PHPMailer/src/PHPMailer.php'; // Pfad zu PHPMailer anpassen
require '../PHPMailer/src/SMTP.php';      // SMTP-Klasse einbinden
require '../PHPMailer/src/Exception.php'; // Fehlerbehandlung

$usernameError = "";
$emailError = "";
$passwordError = "";
$successMessage = "";

if (isset($_POST["submit"])) {
  require("mysql.php");
    
  $username = trim($_POST["username"]);
  $email = trim($_POST["email"]);
  $password = $_POST["pw"];
  $passwordRepeat = $_POST["pw2"];

  // Überprüfen, ob Benutzername oder E-Mail bereits existiert
  $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :user OR EMAIL = :email");
  $stmt->bindParam(":user", $username);
  $stmt->bindParam(":email", $email);
  $stmt->execute();
  $count = $stmt->rowCount();
  if ($count > 0) {
    // Fehler: Benutzername oder E-Mail existiert bereits
    $row = $stmt->fetch();
    if ($row["USERNAME"] == $username) {
        $usernameError = "Dieser Benutzername ist bereits vergeben.";
    }
    if ($row["EMAIL"] == $email) {
        $emailError = "Diese E-Mail-Adresse ist bereits registriert.";
    }
} else {
  // Prüfen, ob dies der erste Benutzer ist
  $stmt = $mysql->prepare("SELECT COUNT(*) AS total FROM accounts");
  $stmt->execute();
  $row = $stmt->fetch();
  $server_rank = ($row["total"] == 0) ? 'Admin' : 'User'; // Erster Nutzer = Admin, sonst User
  // Passwortprüfung
  if ($password === $passwordRepeat) {
    if (strlen($password) >= 6) { // Mindestlänge setzen
      // Benutzer registrieren
      $stmt = $mysql->prepare("INSERT INTO accounts (USERNAME, EMAIL, PASSWORD, server_rank) VALUES (:user, :email, :pw, :rank)");
      $stmt->bindParam(":user", $username);
      $stmt->bindParam(":email", $email);
      $hash = password_hash($password, PASSWORD_BCRYPT);
      $stmt->bindParam(":pw", $hash);
      $stmt->bindParam(":rank", $server_rank);
      $stmt->execute();

      // Benutzerverzeichnis für Uploads erstellen
      $uploadDir = "/home/nas-website-files/user_files/";
      $userDir = $uploadDir . $username . "/";

      // Falls Benutzerverzeichnis nicht existiert, erstelle es
      if (!is_dir($userDir)) {
        mkdir($userDir, 0775, true);
        chmod($userDir, 0775); // ✅ Fix: Keine chown()/chgrp(), sondern chmod()
      }

      // Erfolgreiche Registrierung → Bestätigungs Email
      $mail = new PHPMailer(true);
      try {
        // Konfigurationsdatei laden
        $config = json_decode(file_get_contents('../config.json'), true);
                
        // Server-Einstellungen aus der Konfigurationsdatei
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = $config['smtp']['auth'];
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['smtp']['port'];
                
        // Absender und Empfänger
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($email, $username);
                
        // Inhalt der E-Mail
        $mail->isHTML(true);
        $mail->Subject = $config['email']['registered'];
        $mail->Body    = "
        <h3>Hello $username,</h3>
        <p>Your My NAS account is created succesfully.</p>
        <br>
        <p>You can now use it as your personal My NAS account to save some data on it.</p>
        <p>If you have some issues with My NAS use the contact-page on My NAS.</p>
        <p>Thank you for creating your account.</p>";
        // E-Mail senden
        $mail->send();
        } catch (Exception $e) {
          $error = "Fehler beim Senden der E-Mail: " . $mail->ErrorInfo;
        }

        $successMessage = "Erfolgreich registriert! Weiterleitung zur Anmeldung...";
        header("Location: Login.php");
        exit();
      } else {
        $passwordError = "Das Passwort muss mindestens 6 Zeichen lang sein.";
      }
    } else {
      $passwordError = "Die Passwörter stimmen nicht überein.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Registrieren</title>
    <link rel="website icon" href="../Logo/Logo_512px.png">
    <link rel="stylesheet" href="../assets/css/register.css">
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
				<a class="hover-underline-animation left" href="../index.php">Startseite</a>
				<a class="hover-underline-animation left" href="../User_Files.php">Dateien</a>
				<a class="hover-underline-animation left" href="../messenger.php">Messenger</a>
				<?php if(isset($_SESSION["id"])): ?>
					<a class="hover-underline-animation left" href="../account-system/account.php">Mein Account</a>
				<?php else: ?>
					<a class="hover-underline-animation left" href="../account-system/Login.php">Mein Account</a>
				<?php endif; ?>
				<a class="hover-underline-animation left" href="../Contact_Page/Contact_Page.php">Kontakt</a>
			</nav>
			<?php if (isset($_SESSION["id"])): ?>
                <a class="login_button" href="../account-system/logout.php">Abmelden</a>
            <?php else: ?>
                <a class="login_button" href="../account-system/Login.php">Anmelden</a>
            <?php endif; ?>
			<button class="hamburger">
				<div class="bar"></div>
			</button>
		</div>
	</header>
	<nav class="mobile-nav">
		<a href="../index.php">Startseite</a>
		<a href="../User_Files.php">Dateien</a>
		<a href="../messenger.php">Messenger</a>
		<?php if(isset($_SESSION["id"])): ?>
			<a href="../account-system/account.php">Mein Account</a>
		<?php else: ?>
			<a href="../account-system/Login.php">Mein Account</a>
		<?php endif; ?>
		<a href="../Contact_Page/Contact_Page.php">Kontakt</a>
	</nav>

  <div class="wrapper">
    <form action="register.php" method="post">
      <h1>Registrieren</h1>
      <?php
      require("mysql.php");

      // Überprüfen, ob die Tabelle leer ist
      $stmt = $mysql->prepare("SELECT COUNT(*) FROM accounts");
      $stmt->execute();
      $count = $stmt->fetchColumn();

      if ($count == 0) {
          echo "<p><strong>Dieser Account wird der Admin-Account sein!</strong></p>";
      }
      ?>

      <div class="input-box">
        <input type="text" name="username" placeholder="Benutzername" required>
        <i class='bx bxs-user'></i>
      </div>
      <?php if (!empty($usernameError)): ?>
        <p class="error-message"><?php echo $usernameError; ?></p>
      <?php endif; ?>

      <div class="input-box">
        <input type="email" name="email" placeholder="E-Mail" required>
        <i class='bx bx-envelope'></i>
      </div>
      <?php if (!empty($emailError)): ?>
        <p class="error-message"><?php echo $emailError; ?></p>
      <?php endif; ?>

      <div class="input-box">
        <input type="password" name="pw" placeholder="Passwort" required>
        <i class='bx bxs-lock-alt'></i>
      </div>

      <div class="input-box">
        <input type="password" name="pw2" placeholder="Passwort wiederholen" required>
        <i class='bx bx-refresh'></i>
      </div>
      <?php if (!empty($passwordError)): ?>
        <p class="error-message"><?php echo $passwordError; ?></p>
      <?php endif; ?>

      <button type="submit" name="submit" class="btn">Registrieren</button>

      <?php if (!empty($successMessage)): ?>
        <p class="success-message"><?php echo $successMessage; ?></p>
      <?php endif; ?>

      <div class="register-link">
        <p>Schon einen Account? <a href="Login.php">Anmelden</a></p>
      </div>
    </form>
  </div>

  <script src="../assets/js/main.js"></script>
	<script src="assets/js/lang.js"></script>
</body>
</html>
