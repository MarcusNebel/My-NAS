<?php
session_start();
require("mysql.php"); // Verbindung zur Datenbank

if (!isset($_SESSION["id"])) {
    $_SESSION["redirect_to"] = $_SERVER["REQUEST_URI"]; // Speichert die aktuelle Seite
    session_destroy();
    header("Location: Login.php"); // Weiterleitung zur Login-Seite
    exit();
}

// Benutzer-ID aus der Session abrufen
$user_id = $_SESSION["id"];

// Datenbankabfrage: Überprüfen, ob der Benutzer Admin ist
$stmt = $mysql->prepare("SELECT server_rank FROM accounts WHERE ID = :id");
$stmt->execute(array(":id" => $user_id));
$user = $stmt->fetch();

if (!$user || $user["server_rank"] !== "Admin") {
    // Kein gültiger Benutzer oder kein Admin -> Weiterleitung zur Login-Seite
    header("Location: Login.php");
    exit();
}

if (isset($_GET["id"])) {
    $stmt = $mysql->prepare("SELECT * FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_GET["id"]));
    $user = $stmt->fetch();
    $userid = $_GET;

    if (!$user) {
        echo "Benutzer nicht gefunden!";
        exit();
    }
} else {
    echo "Keine Benutzer-ID angegeben!";
    exit();
}

if (isset($_POST["edit-submit"])) {
    $stmt = $mysql->prepare("UPDATE accounts SET USERNAME = :username, EMAIL = :email, server_rank = :server_rank WHERE ID = :id");
    $stmt->execute(array(
        ":username" => $_POST["username"],
        ":email" => $_POST["email"],
        ":id" => $_GET["id"],
        "server_rank" => $_POST["server-rank"]
    ));
    header("Location: accounts-list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My NAS | Benutzer bearbeiten</title>
	<link rel="website icon" href="../Logo/Logo_512px.png">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

	<link rel="stylesheet" href="../assets/css/edit.css">
</head>
<body class="account-body">
    <header>
        <div class="container transparancy">
            <h2><a class="link-no-decoration" href="../index.php"><span>MY </span>NAS</a></h2>
            <nav>
                <a class="hover-underline-animation left" href="../index.php">Startseite</a>
                <a class="hover-underline-animation left" href="../User_Files.php">Meine Dateien</a>
                <a class="hover-underline-animation left" href="../messenger.php">Messenger</a>
                <?php if(isset($_SESSION["id"])): ?>
					<a class="hover-underline-animation left" href="account.php">Mein Account</a>
				<?php else: ?>
					<a class="hover-underline-animation left" href="Login.php">Mein Account</a>
				<?php endif; ?>
                <a class="hover-underline-animation left" href="../Contact_Page/Contact_Page.php">Kontakt</a>
            </nav>
            <?php if (isset($_SESSION["id"])): ?>
                <a class="login_button" href="logout.php">Abmelden</a>
            <?php else: ?>
                <a class="login_button" href="Login.php">Anmelden</a>
            <?php endif; ?>
            <button class="hamburger">
				<div class="bar"></div>
			</button>
        </div>
    </header>
    <main>
        <section class="account-section">
            <div class="container_account">
                <a href="accounts-list.php" onclick="return confirm('Möchten Sie wirklich auf die Account-Listen-Seite zurückkehren? Alle ungespeicherten Daten werden gelöscht!')"><i class='bx bx-arrow-back' ></i></a>
                <h4>Benutzer bearbeiten:</h4>
                <form class="account-form" action="edit.php?id=<?php echo $_GET["id"] ?>" method="post">
                    <div class="input-box-account">
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user["USERNAME"]) ?>" placeholder="Benutzername" required><br>
                    </div>
                    <div class="input-box-account">
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user["EMAIL"]) ?>" placeholder="Email" required><br>
                    </div>
                    <div class="input-box-account">
                        <input type="text" name="server-rank" value="<?php echo htmlspecialchars($user["server_rank"]) ?>" placeholder="Admin/Moderator/User" required><br>
                    </div>
                    <button class="btn-submit" name="edit-submit" type="submit">Speichern</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>