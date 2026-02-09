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

// Funzione per creare tabelle mancanti
function createTables($pdo) {
    $tables = [
        "CREATE TABLE IF NOT EXISTS quiz_risposte (
            id INT PRIMARY KEY AUTO_INCREMENT,
            utente_id INT NOT NULL,
            eta INT,
            livello_fitness ENUM('principiante', 'intermedio', 'avanzato') DEFAULT 'principiante',
            obiettivo VARCHAR(255),
            orario_notifica TIME,
            completato INT DEFAULT 0,
            data_quiz TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS esercizi_piano (
            id INT PRIMARY KEY AUTO_INCREMENT,
            piano_id INT NOT NULL,
            nome_esercizio VARCHAR(255),
            descrizione TEXT,
            ripetizioni INT,
            serie INT,
            giorno INT,
            difficolta_moltiplicatore FLOAT DEFAULT 1.0,
            FOREIGN KEY (piano_id) REFERENCES piani_allenamento(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS progressi_dettaglio (
            id INT PRIMARY KEY AUTO_INCREMENT,
            utente_id INT NOT NULL,
            esercizio_id INT NOT NULL,
            data_allenamento DATE,
            ripetizioni_fatte INT,
            serie_fatte INT,
            feedback TEXT,
            difficolta_eseguita FLOAT DEFAULT 1.0,
            completato INT DEFAULT 0,
            data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (esercizio_id) REFERENCES esercizi_piano(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS statistiche_esercizio (
            id INT PRIMARY KEY AUTO_INCREMENT,
            utente_id INT NOT NULL,
            nome_esercizio VARCHAR(255),
            volte_completato INT DEFAULT 0,
            ripetizioni_totali INT DEFAULT 0,
            difficolta_media FLOAT DEFAULT 1.0,
            FOREIGN KEY (utente_id) REFERENCES utenti(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS amicizie (
            id INT PRIMARY KEY AUTO_INCREMENT,
            utente_id INT NOT NULL,
            amico_id INT NOT NULL,
            stato ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
            data_richiesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (amico_id) REFERENCES utenti(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS periodi_riposo (
            id INT PRIMARY KEY AUTO_INCREMENT,
            utente_id INT NOT NULL,
            giorni_consecutivi INT DEFAULT 0,
            giorni_riposo_consigliati INT DEFAULT 1,
            ultimo_allenamento DATE,
            data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS feedback_allenamento (
            id INT PRIMARY KEY AUTO_INCREMENT,
            utente_id INT NOT NULL,
            piano_id INT NOT NULL,
            valutazione INT,
            commenti TEXT,
            data_feedback TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (piano_id) REFERENCES piani_allenamento(id)
        )"
    ];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Tabella già esiste
        }
    }
}

createTables($pdo);
?>