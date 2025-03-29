<?php
if(isset($_POST['submit'])){
    require("mysql.php");

    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Überprüfen, ob der Reset-Code gesetzt ist
    if (!isset($_POST['reset_code']) || empty($_POST['reset_code'])) {
        die("Fehler: Kein Reset-Code angegeben!");
    }

    // Den Reset-Code aus dem Formular holen
    $reset_code = $_POST['reset_code'];

    // Prüfen, ob der Reset-Code in der Datenbank existiert
    $stmt = $mysql->prepare("SELECT * FROM accounts WHERE reset_code = :reset_code");
    $stmt->bindParam(":reset_code", $reset_code);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        die("Fehler: Ungültiger Reset-Code.");
    }

    // Überprüfen, ob die Passwörter übereinstimmen
    if($_POST['pw'] == $_POST['pw2']){
        $hashed_pw = password_hash($_POST['pw'], PASSWORD_BCRYPT);

        // Passwort mit dem Reset-Code in der Datenbank ändern
        $stmt = $mysql->prepare("UPDATE accounts SET PASSWORD = :pw, reset_code = NULL WHERE reset_code = :reset_code");
        $stmt->bindParam(":pw", $hashed_pw);
        $stmt->bindParam(":reset_code", $reset_code);

        // Überprüfen, ob die Passwortänderung erfolgreich war
        if ($stmt->execute()) {
            if ($stmt->rowCount() === 0) {
                die("Fehler: Das Passwort wurde nicht geändert. Der Reset-Code ist möglicherweise ungültig oder wurde bereits verwendet.");
            }
            echo "Passwort erfolgreich geändert!";
            header("Location: Login.php");
            exit;
        } else {
            print_r($stmt->errorInfo());
            die("Fehler beim Ändern des Passworts.");
        }
    } else {
        echo "Die Passwörter stimmen nicht überein!";
    }
}
?>

<!DOCTYPE html>
<html lang="de" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Passwort zurücksetzen</title>
    <link rel="website icon" href="../Logo/Logo_512px.png">
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
                <a class="hover-underline-animation left" href="../index.php">Startseite</a>
				<a class="hover-underline-animation left" href="../User_Files.php">Meine Dateien</a>
				<a class="hover-underline-animation left" href="../File_upload.php">Dateien hochladen</a>
                <?php if(isset($_SESSION["id"])): ?>
		        	<a class="hover-underline-animation left" href="account.php">Mein Account</a>
	        	<?php else: ?>
	        		<a class="hover-underline-animation left" href="Login.php">Mein Account</a>
	        	<?php endif; ?>
				<a class="hover-underline-animation left" href="../Contact_Page/Contact_Page.php">Kontakt</a>
            </nav>
                <a class="login_button" href="Login.php">Anmelden</a>
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
		<a href="../Contact_Page/Contact_Page.php">Kontakt</a>
    </nav>

    <div class="wrapper">
        <form action="" method="post">
            <div class="input-box">
                <input type="text" name="reset_code" placeholder="Reset-Code" required> <!-- Reset-Code hier als normales Eingabefeld -->
                <i class='bx bx-key'></i>
            </div>
            <div class="input-box">
                <input type="password" name="pw" placeholder="Neues Passwort" required>
                <i class='bx bx-lock'></i>
            </div>
            <div class="input-box">
                <input type="password" name="pw2" placeholder="Passwort bestätigen" required>
                <i class='bx bx-lock'></i>
            </div>

            <button type="submit" name="submit" class="btn">Passwort ändern</button>
        </form>
    </div>

    <script src="script.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
