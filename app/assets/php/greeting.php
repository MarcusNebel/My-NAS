<?php
date_default_timezone_set('Europe/Berlin');

if(isset($_SESSION["id"])) {
    require("mysql.php");
    $stmt = $mysql->prepare("SELECT USERNAME FROM accounts WHERE ID = :id");
    $stmt->execute([":id" => $_SESSION["id"]]);
    $username = $stmt->fetchColumn();

    $hour = date("H");

    if ($hour >= 5 && $hour < 11) {
        $greeting = "Guten Morgen, " . $username . "!";
    } elseif ($hour >= 11 && $hour < 17) {
        $greeting = "Guten Tag, " . $username . "!";
    } elseif ($hour >= 17 && $hour < 22) {
        $greeting = "Guten Abend, " . $username . "!";
    } else {
        $greeting = "Guten Abend, " . $username . "!";
    }

    echo $greeting;
} else {
    $hour = date("H");

    if ($hour >= 5 && $hour < 11) {
        $greeting = "Willkommen bei My NAS";
    } elseif ($hour >= 11 && $hour < 17) {
        $greeting = "Willkommen bei My NAS";
    } elseif ($hour >= 17 && $hour < 22) {
        $greeting = "Willkommen bei My NAS";
    } else {
        $greeting = "Willkommen bei My NAS";
    }    

    echo $greeting;
}
?>
