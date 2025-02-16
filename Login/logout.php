<?php
session_start();
setcookie("login_cookie", "", time() -1);
session_destroy();
header("Location: nas-website/Main_Website/index.php");
 ?>
