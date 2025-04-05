<?php
session_start();

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION["id"])) {
    echo "Du musst angemeldet sein, um deinen API-Schlüssel zu löschen.";
    exit();
}

require("mysql.php");

// Überprüfen, ob der API-Schlüssel über die URL übergeben wurde
if (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
    $api_key = $_GET['api_key'];
    
    // Überprüfen, ob der API-Schlüssel zu dem aktuell angemeldeten Benutzer gehört
    $stmt = $mysql->prepare("SELECT api_key FROM accounts WHERE ID = :id");
    $stmt->execute(array(":id" => $_SESSION["id"]));
    $row = $stmt->fetch();

    if ($row && $row['api_key'] === $api_key) {
        // Löschen des API-Schlüssels aus der Datenbank
        $stmt = $mysql->prepare("UPDATE accounts SET api_key = NULL WHERE ID = :id");
        $stmt->execute(array(":id" => $_SESSION["id"]));

        header("Location: ../account-system/account.php");
    } else {
        echo "Ungültiger API-Schlüssel oder du bist nicht autorisiert, diesen zu löschen.";
    }
} else {
    echo "Kein API-Schlüssel angegeben.";
}
?>
