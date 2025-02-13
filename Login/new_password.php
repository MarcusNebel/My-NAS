<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues Passwort setzen | My NAS</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../Main_Website/assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  </head>

    <?php
    if(isset($_POST['submit'])){
        require("mysql.php");
        // E-Mail und neues Passwort holen
        if($_POST['pw'] == $_POST['pw2']){
            $stmt = $mysql->prepare("UPDATE accounts SET PASSWORD = :pw, reset_code = NULL WHERE EMAIL = :email");
            $stmt->bindParam(":pw", password_hash($_POST['pw'], PASSWORD_BCRYPT));
            $stmt->bindParam(":email", $_GET['email']);
            $stmt->execute();
            header("Location: Login.php"); // Weiterleitung zur Anmeldeseite
        } else {
            $error = "Die Passwörter stimmen nicht überein!";
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
      <form action="new_password.php?email=<?php echo $_GET['email']; ?>" method="post">
        <h1>Neues Passwort setzen</h1>
        <div class="input-box">
          <input type="password" name="pw" placeholder="Neues Passwort" required>
          <i class='bx bxs-lock-alt'></i>
        </div>
        <div class="input-box">
          <input type="password" name="pw2" placeholder="Passwort wiederholen" required>
          <i class='bx bx-repeat'></i>
        </div>
        <?php if(isset($error)): ?>
        <div class="error-message">
          <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        <button type="submit" name="submit" class="btn">Passwort setzen</button>
        <div class="register-link">
          <p>Schon ein Konto? <a href="index.php">Anmelden</a></p>
        </div>
      </form>
    </div>

    <script src="script.js"></script>
    <script src="../Main_Website/assets/js/main.js"></script>
  </body>
</html>
