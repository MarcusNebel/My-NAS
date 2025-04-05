<?php
session_start();
if (!isset($_SESSION["id"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"];
    header("Location: ../account-system/Login.php");
    exit();
}

if(isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
    require ("../account-system/mysql.php");

    $stmt = $mysql->prepare("SELECT EMAIL, USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $useremail = $user['EMAIL'];
    $username = $user['USERNAME'];
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';      
require '../PHPMailer/src/Exception.php'; 

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // SMTP Server
        $mail->SMTPAuth = true;
        $mail->Username   = 'youremail@gmail.com'; // Deine E-Mail-Adresse
        $mail->Password   = 'your_App_Password'; // Dein App-Passwort (nicht das account passwort)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom($email, $name);
        $mail->addAddress('mynas-support@nebel-home.de', 'My NAS Support');
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "
            <h3>Nachricht von $name</h3>
            <p><strong>E-Mail:</strong> $email</p>
            <p><strong>Nachricht:</strong></p>
            <p>$message</p>
        ";
        
        $mail->send();
        $success = "Ihre Nachricht wurde erfolgreich gesendet!";
    } catch (Exception $e) {
        $error = "Fehler beim Senden der E-Mail: " . $mail->ErrorInfo;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NAS | Kontakt</title>
    <link rel="website icon" href="../Logo/Logo_512px.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./style.css">
</head>
<body>
    <header>
		<div class="container transparancy">
      		<h2><a class="link-no-decoration" href="index.php"><span>MY </span>NAS</a></h2>
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
    <main>
        <section class="uf-contact-form-01 mx-auto">
            <div class="container">
                <div class="row justify-content-center bg-white rounded-4 shadow py-5 gx-5 px-lg-5">
                    <div class="col-md-6 text-center">
                        <h2 class="uf-ct-01-text-primary text-uppercase fw-bold">Kontaktiere uns</h2>
                        <p>Oder erreiche uns unter: <a href="mailto:mynas-support@nebel-home.de" class="text-decoration-none"><br>mynas-support@nebel-home.de</a></p>
                        <img src="./images/plane.png" alt="" class="uf-img-contact-form-01 pt-4 d-md-block d-none">
                    </div>
                    <div class="col-md-6">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="uf-imail" class="form-label">E-Mail Addresse</label>
                                <input type="email" class="form-control" id="uf-imail" name="email" aria-describedby="emailHelp" value="<?php echo $useremail ?>" required>
                                <div id="emailHelp" class="form-text uf-ct-01-text-primary"><strong>Wir teilen Ihre E-Mail Adresse mit keinen anderen Personen.</strong></div>
                            </div>
                            <div class="mb-3">
                                <label for="uf-iname" class="form-label">Dein Name</label>
                                <input type="text" class="form-control" id="uf-iname" name="name" value="<?php echo $username ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="uf-isubject" class="form-label">Betreff</label>
                                <input type="text" class="form-control" id="uf-isubject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="uf-itextarea" class="form-label">Deine Nachricht</label>
                                <textarea class="form-control message" id="uf-itextarea" name="message" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="submit" class="btn btn-lg uf-ct-01-btn-primary">Nachricht senden</button>
                        </form>
                        <?php if($success): ?>
                            <div class="alert alert-success mt-3"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if($error): ?>
                            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="./assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/lang.js"></script>
</body>
</html>
