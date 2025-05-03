<?php    
   
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;
    // Anpassen des Pfads zu PHPMailer
   require '../PHPMailer/src/PHPMailer.php'; // Pfad zu PHPMailer anpassen
   require '../PHPMailer/src/SMTP.php';      // SMTP-Klasse einbinden
   require '../PHPMailer/src/Exception.php'; // Fehlerbehandlung
     
     $error = '';
      if (isset($_POST['submit'])) {
         require("mysql.php");
          // Überprüfen, ob die E-Mail existiert
         $stmt = $mysql->prepare("SELECT * FROM accounts WHERE EMAIL = :email");
         $stmt->bindParam(":email", $_POST['email']);
         $stmt->execute();
         $count = $stmt->rowCount();
         
         if ($count == 1) {
             // Benutzerinformationen abrufen
             $user = $stmt->fetch();
             $username = $user['USERNAME'];
              // Generiere einen 6-stelligen Code
             $code = rand(100000, 999999);
              // Speichern des Codes in der Datenbank
             $stmt = $mysql->prepare("UPDATE accounts SET reset_code = :code WHERE EMAIL = :email");
             $stmt->bindParam(":code", $code);
             $stmt->bindParam(":email", $_POST['email']);
             $stmt->execute();
              // E-Mail mit PHPMailer versenden
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
                  $mail->addAddress($_POST['email'], $username);
              
                  // Inhalt der E-Mail
                  $mail->isHTML(true);
                  $mail->Subject = $config['email']['reset_password'];
                  $mail->Body    = "
                      <h3>Hello $username,</h3>
                      <p>To reset your My NAS password use the following code:</p>
                      <h2>$code</h2>
                      <p>Paste the code on the My NAS website to continue.</p>
                      <br>
                      <p>If you did not make this request, please ignore this email.</p>
                      <br>
                      <p>The code will be useless in 15 minutes.";
              
                  // E-Mail senden
                  $mail->send();
                  header("Location: reset_password.php"); // Weiterleitung zur Seite für den Code
                  exit();
              } catch (Exception $e) {
                  $error = "Fehler beim Senden der E-Mail: " . $mail->ErrorInfo;
              }
         } else {
             $error = "Diese E-Mail-Adresse ist nicht registriert!";
         }
     }
   ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Passwort vergessen</title>
    <link rel="website icon" href="../Logo/Logo.png">
    <link rel="stylesheet" href="../assets/css/forgot_password.css">
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
      <form action="forgot_password.php" method="post">
        <h1>Bestätigung senden</h1>
        <div class="input-box">
          <input type="email" name="email" placeholder="E-Mail-Adresse" required>
          <i class='bx bx-envelope'></i>
        </div>
        
        <!-- Fehlerausgabe -->
        <?php if ($error): ?>
          <div class="error-message">
            <p><?php echo $error; ?></p>
          </div>
        <?php endif; ?>

        <button type="submit" name="submit" class="btn">Code anfordern</button>
        <div class="register-link">
          <p>Schon ein Konto? <a href="index.php">Anmelden</a></p>
        </div>
      </form>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="assets/js/lang.js"></script>
  </body>
</html>
