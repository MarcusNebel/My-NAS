<?php
require("mysql.php");

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $stmt = $mysql->prepare("DELETE FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $id));

    header("Location: accounts-list.php"); // Weiterleitung nach dem Löschen
    exit();
} else {
    echo "Kein Benutzer gefunden!";
}
?>