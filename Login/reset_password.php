<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort zurücksetzen | My NAS</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../Main_Website/assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  </head>

    <?php
    // Fehlerbehandlung
    $error = '';
    if(isset($_POST['submit'])){
        require("mysql.php");
        // Eingegebenen Code und die E-Mail prüfen
        $stmt = $mysql->prepare("SELECT * FROM accounts WHERE reset_code = :code");
        $stmt->bindParam(":code", $_POST['code']);
        $stmt->execute();
        $count = $stmt->rowCount();
        if($count == 1){
            // Code korrekt, Passwort zurücksetzen
            header("Location: new_password.php?email=" . $_POST['email']);
        } else {
            $error = "Der Code ist ungültig!";
        }
    }
    ?>

  <body>
    <header>
        <div class="container transparancy">
          <h2><a class="link-no-decoration" href="../Main_Website/index.php"><span>MY </span>NAS</a></h2>
            <nav>
                <a href="../Main_Website/index.php">Startseite</a>
                <a href="#">Dateien</a>
                <a href="#">Bilder</a>
                <a href="#">Kontakt</a>
            </nav>
            <button class="login_button">
                <a href="../Login/Login.php">Anmelden</a>
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
      <form action="reset_password.php" method="post">
        <div class="input-box">
          <input type="text" name="code" placeholder="6-stelliger Code" required>
          <i class='bx bx-key'></i>
        </div>
        <?php if($error): ?>
        <div class="error-message">
          <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        <button type="submit" name="submit" class="btn">Code überprüfen</button>
        <div class="register-link">
          <p>Kein Code erhalten? <a href="forgot_password.php">Code anfordern</a></p>
        </div>
      </form>
    </div>

    <script src="script.js"></script>
    <script src="../Main_Website/assets/js/main.js"></script>
  </body>
</html>
