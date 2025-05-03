<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Anpassen des Pfads zu PHPMailer
require '../PHPMailer/src/PHPMailer.php'; // Pfad zu PHPMailer anpassen
require '../PHPMailer/src/SMTP.php';      // SMTP-Klasse einbinden
require '../PHPMailer/src/Exception.php'; // Fehlerbehandlung


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

      // Benutzerordner prüfen und erstellen
      $username = $row["USERNAME"]; // Benutzername aus der Datenbank
      $email = $row["EMAIL"]; // Email aus der Datenbank
      $user_folder = "/home/nas-website-files/user_files/" . $username;
      if (!is_dir($user_folder)) {
        mkdir($user_folder, 0755, true); // Ordner erstellen, falls er nicht existiert
        chmod($user_folder, 0755);
      }

      // Email für neuen Login
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
      $mail->Subject = $config['email']['signed_in'];
      $mail->Body    = "
      <h3>Hello $username,</h3>
      <br>
      <p>You logged in to your My NAS account.</p>
      <p>You're now logged in to your account.</p>
      <p>If that were not you you can change your password or contact the admin of your My NAS server.</p>
      <p>If that were you, you can ignore this email.</p>";
                
      // E-Mail senden
      $mail->send();
      } catch (Exception $e) {
        $error = "Fehler beim Senden der E-Mail: " . $mail->ErrorInfo;
      }

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