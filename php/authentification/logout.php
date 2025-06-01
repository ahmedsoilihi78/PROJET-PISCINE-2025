<?php

session_start();

$_SESSION = [];
session_unset();
session_destroy();
setcookie('PHPSESSID', '', time()-3600);
header("Location: ../../html/accueil.html"); // redigire vers un la page principale
exit();
// si je veux me deconnecter utiliser :
// <a href="../../php/logout.php">Se déconnecter</a>
// ca cree un liens que qui va vers cette page php pour se deconnecter.
?>

