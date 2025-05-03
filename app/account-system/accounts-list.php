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
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My NAS | Benutzerverwaltung</title>
	<link rel="website icon" href="../../Logo/Logo_512px.png">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet" />
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

	<link rel="stylesheet" href="../assets/css/accounts-list.css">
</head>
<body class="account-body">
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
	<main>
		<section class="accounts-list-section">
			<div class="container_accounts-list">
                <?php
                if(isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
                    require("mysql.php");

                    $stmt = $mysql->prepare("SELECT * FROM accounts");
                    $stmt->execute();
                    $rows = $stmt->fetchAll();
                } else {
                    echo "Error: Kein Benutzer angemeldet!";
                }
                ?>
                <?php
                if(isset($rows) && !empty($rows)) { ?>
                <div class="table-container">
                    <table border="1" class="accounts-table-admin">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Benutzername</th>
                                <th>Email</th>
                                <th>Passwort</th>
                                <th>API-Schlüssel</th>
                                <th>Server-Rang</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["ID"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["USERNAME"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["EMAIL"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["PASSWORD"]); ?></td>
                                    <td><?php if(isset($row["api_key"]) && !empty($row["api_key"])) {
                                        echo htmlspecialchars($row["api_key"]);
                                    } else {
                                        echo "NULL";
                                    } ?></td>
                                    <td><?php echo htmlspecialchars($row["server_rank"]); ?></td>
                                    <td>
                                        <?php
                                        if ($_SESSION["id"] == $row["ID"]) {
                                            ?>
                                                <a class="pen-a" href="account.php" 
                                                    title="Bearbeiten"
                                                    onclick="return confirm('Möchten Sie ihren Benutzer wirklich bearbeiten?')">
                                                    <i class='bx bxs-edit-alt'></i>
                                                </a>
                                                <?php
                                            } else {
                                                ?>
                                                <a class="pen-a" href="edit.php?id=<?php echo $row['ID']; ?>" 
                                                    title="Bearbeiten"
                                                    onclick="return confirm('Möchten Sie diesen Benutzer wirklich bearbeiten?')">
                                                    <i class='bx bxs-edit-alt'></i>
                                                </a>
                                                <?php
                                            }
                                            ?>

                                        

                                        <?php
                                        if ($_SESSION["id"] == $row["ID"]) {
                                            // Falls der Benutzer sich selbst löschen will, deaktiviere den Link oder zeige eine Meldung an
                                            echo '<span title="Eigenes Konto kann nicht gelöscht werden" style="color: gray; cursor: not-allowed;">
                                                    <i class="bx bxs-trash"></i>
                                                </span>';
                                        } else {
                                            // Normale Löschoption anzeigen
                                            echo '<a href="delete-account.php?id=' . $row["ID"] . '" 
                                                    title="Löschen" 
                                                    onclick="return confirm(\'Möchten Sie diesen Benutzer wirklich löschen?\')">
                                                    <i class="bx bxs-trash"></i>
                                                </a>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                } else {
                    echo "Error: Die 'rows' Variable ist leer! Fehler bei der Datenbankabfrage!";
                }
                ?>
			</div>
		</section>
	</main>
    <script src="../assets/js/main.js"></script>
	<script src="../assets/js/lang.js"></script>
</body>
</html>