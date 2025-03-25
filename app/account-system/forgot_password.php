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
                 // Server-Einstellungen
                 $mail->isSMTP();
                 $mail->Host       = 'smtp.gmail.com'; // SMTP-Server (z. B. Gmail, Outlook, GMX)
                 $mail->SMTPAuth   = true;
                 $mail->Username   = 'youremail@gmail.com'; // Deine E-Mail-Adresse
                 $mail->Password   = 'your_App_Password'; // Dein App-Passwort (nicht das account passwort)
                 $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                 $mail->Port       = 587;
                  // Absender und Empfänger
                 $mail->setFrom('mynas-support@gmail.com', 'My NAS Support');
                 $mail->addAddress($_POST['email'], $username);
                  // Inhalt der E-Mail
                 $mail->isHTML(true);
                 $mail->Subject = 'Neues Passwort festlegen';
                 $mail->Body    = "
                     <h3>Hallo $username,</h3>
                     <p>Um Ihr Passwort zurückzusetzen, verwenden Sie bitte den folgenden Code:</p>
                     <h2>$code</h2>
                     <p>Geben Sie diesen Code auf der Website ein, um fortzufahren.</p>
                     <br>
                     <p>Falls Sie diese Anfrage nicht gestellt haben, ignorieren Sie bitte diese E-Mail.</p>
                 ";
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
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Passwort vergessen</title>
    <link rel="website icon" href="../Logo/Logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <button class="login_button">
          <a href="Login.php">Anmelden</a>
        </button>
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

    <div class="wrapper">
      <form action="forgot_password.php" method="post">
        <h1>Passwort zurücksetzen</h1>
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

    <script src="script.js"></script>
    <script src="../assets/js/main.js"></script>
  </body>
</html>
