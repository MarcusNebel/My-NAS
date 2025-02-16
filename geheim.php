<?php
session_start();
if(!isset($_SESSION["username"])){
  header("Location: index.php");
  exit;
}
 ?>
<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="website icon" href="Logo.png">
  </head>
  <body>
    <h1>Top Secret</h1>
    <a href="/Login/logout.php">Abmelden</a>
  </body>
</html>
