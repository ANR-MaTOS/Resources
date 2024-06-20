<?php
header("Content-Type: text/html;charset=UTF-8");

$host = 'localhost';
$socket = ''; //'/srv/run/mysqld/mysqld.sock';
$user = 'root';
$db = 'WP4_postedition';
$password = '';

$link = new mysqli($host,$user,$password,$db, NULL, $socket);
mysqli_query($link, "set names 'utf8'");

if ($link->connect_errno) {
  die("ERREUR: impossible de se connecter à la base de données: " . $mysqli->connect_error);
}

//echo '<p>Connexion à la base de données réussie.</p>';
?>