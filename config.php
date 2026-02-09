<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'utente_phpmyadmin');
define('DB_PASS', 'Password1!');
define('DB_NAME', 'home_workout');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
