<?php
// Configurazione parametri database
$host = "127.0.0.1";
$db_name = "allenamenti";
$username_db = "utente_phpmyadmin"; // Sostituisci se necessario
$password_db = "Password1!";

try {
    // Creazione connessione PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username_db, $password_db);
    
    // Impostazione degli attributi per la gestione errori
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // In caso di errore di connessione, lo script si ferma
    die("Errore di connessione al database: " . $e->getMessage());
}
?>