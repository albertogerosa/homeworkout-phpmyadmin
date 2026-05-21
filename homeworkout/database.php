<?php
// Configurazione parametri database
$host = "127.0.0.1";
$db_name = "allenamenti";
$username_db = "utente_phpmyadmin"; // Sostituisci se necessario
$password_db = "Password1!";
//email: superadmin@homeworkout.local
//password: ChangeMe123!

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

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE :table_name");
    $stmt->execute(['table_name' => $table]);

    return (bool)$stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . $pdo->quote($column));
    return (bool)$stmt->fetchColumn();
}

function indexExists(PDO $pdo, string $table, string $indexName): bool {
    $stmt = $pdo->query("SHOW INDEX FROM `{$table}` WHERE Key_name = " . $pdo->quote($indexName));
    return (bool)$stmt->fetchColumn();
}

function addColumnIfMissing(PDO $pdo, string $table, string $column, string $definition): void {
    if (tableExists($pdo, $table) && !columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    }
}

// Funzione per creare tabelle mancanti
function createTables($pdo) {
    $tables = [
        "CREATE TABLE IF NOT EXISTS tenants (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nome VARCHAR(100) NOT NULL,
            slug VARCHAR(120) NOT NULL UNIQUE,
            stato ENUM('active', 'suspended') DEFAULT 'active',
            logo_url VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS ruoli (
            id INT PRIMARY KEY,
            nome_ruolo VARCHAR(50) NOT NULL UNIQUE,
            descrizione TEXT NULL
        )",

        "CREATE TABLE IF NOT EXISTS utenti (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            nome VARCHAR(50) DEFAULT NULL,
            cognome VARCHAR(50) DEFAULT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            livello ENUM('principiante','intermedio','avanzato') DEFAULT 'principiante',
            refresh_token VARCHAR(128) NULL,
            refresh_token_scadenza DATETIME NULL,
            creato_il TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_utenti_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",

        "CREATE TABLE IF NOT EXISTS utente_ruolo (
            utente_id INT PRIMARY KEY,
            ruolo_id INT NOT NULL,
            FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
            FOREIGN KEY (ruolo_id) REFERENCES ruoli(id) ON DELETE RESTRICT
        )",

        "CREATE TABLE IF NOT EXISTS piani_allenamento (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            utente_id INT DEFAULT NULL,
            data_inizio DATE DEFAULT NULL,
            data_fine DATE DEFAULT NULL,
            difficolta INT DEFAULT 1,
            stato ENUM('attivo', 'completato') DEFAULT 'attivo',
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",

        "CREATE TABLE IF NOT EXISTS quiz_risposte (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            utente_id INT NOT NULL,
            eta INT,
            livello_fitness ENUM('principiante', 'intermedio', 'avanzato') DEFAULT 'principiante',
            obiettivo VARCHAR(255),
            orario_notifica TIME,
            completato INT DEFAULT 0,
            data_quiz TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS esercizi_piano (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            piano_id INT NOT NULL,
            nome_esercizio VARCHAR(255),
            descrizione TEXT,
            ripetizioni INT,
            serie INT,
            giorno INT,
            difficolta_moltiplicatore FLOAT DEFAULT 1.0,
            FOREIGN KEY (piano_id) REFERENCES piani_allenamento(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS progressi_dettaglio (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
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
            FOREIGN KEY (esercizio_id) REFERENCES esercizi_piano(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS statistiche_esercizio (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            utente_id INT NOT NULL,
            nome_esercizio VARCHAR(255),
            volte_completato INT DEFAULT 0,
            ripetizioni_totali INT DEFAULT 0,
            difficolta_media FLOAT DEFAULT 1.0,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS amicizie (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            utente_id INT NOT NULL,
            amico_id INT NOT NULL,
            stato ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
            data_richiesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (amico_id) REFERENCES utenti(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS periodi_riposo (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            utente_id INT NOT NULL,
            giorni_consecutivi INT DEFAULT 0,
            giorni_riposo_consigliati INT DEFAULT 1,
            ultimo_allenamento DATE,
            data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS feedback_allenamento (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            utente_id INT NOT NULL,
            piano_id INT NOT NULL,
            valutazione INT,
            commenti TEXT,
            data_feedback TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (piano_id) REFERENCES piani_allenamento(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )"
        ,
        "CREATE TABLE IF NOT EXISTS notifiche (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NULL,
            utente_id INT NOT NULL,
            tipo VARCHAR(80) DEFAULT 'allenamento',
            testo TEXT,
            inviato INT DEFAULT 0,
            data_programmata DATETIME,
            data_invio TIMESTAMP NULL,
            FOREIGN KEY (utente_id) REFERENCES utenti(id),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
        )"
    ];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Tabella già esiste
        }
    }

    try {
        $pdo->exec("INSERT IGNORE INTO ruoli (id, nome_ruolo, descrizione) VALUES
            (1, 'utente', 'Utente standard'),
            (3, 'amministratore', 'Amministratore piattaforma'),
            (4, 'super_admin', 'Super amministratore multi-tenant')");
    } catch (PDOException $e) {
        // Seed ruoli già eseguito
    }

    try {
        $pdo->exec("INSERT IGNORE INTO tenants (id, nome, slug, stato) VALUES
            (1, 'HomeWorkout Demo', 'demo-homeworkout', 'active')");
    } catch (PDOException $e) {
        // Tenant demo già presente
    }
}

createTables($pdo);

function ensureTenantSchema(PDO $pdo): void {
    $tenantTables = [
        'utenti',
        'quiz_risposte',
        'piani_allenamento',
        'esercizi_piano',
        'progressi_dettaglio',
        'statistiche_esercizio',
        'amicizie',
        'periodi_riposo',
        'feedback_allenamento'
    ];

    foreach ($tenantTables as $table) {
        addColumnIfMissing($pdo, $table, 'tenant_id', 'INT NULL');
    }

    if (tableExists($pdo, 'utenti')) {
        $pdo->exec("UPDATE utenti SET tenant_id = 1 WHERE tenant_id IS NULL AND id > 0");
    }

    if (tableExists($pdo, 'quiz_risposte') && !indexExists($pdo, 'quiz_risposte', 'uniq_quiz_tenant_utente')) {
        try {
            $pdo->exec("ALTER TABLE quiz_risposte ADD UNIQUE KEY uniq_quiz_tenant_utente (tenant_id, utente_id)");
        } catch (PDOException $e) {
        }
    }

    if (tableExists($pdo, 'statistiche_esercizio') && !indexExists($pdo, 'statistiche_esercizio', 'uniq_stat_tenant_utente_nome')) {
        try {
            $pdo->exec("ALTER TABLE statistiche_esercizio ADD UNIQUE KEY uniq_stat_tenant_utente_nome (tenant_id, utente_id, nome_esercizio)");
        } catch (PDOException $e) {
        }
    }

    if (tableExists($pdo, 'piani_allenamento') && !indexExists($pdo, 'piani_allenamento', 'idx_piani_tenant_utente')) {
        try {
            $pdo->exec("ALTER TABLE piani_allenamento ADD KEY idx_piani_tenant_utente (tenant_id, utente_id)");
        } catch (PDOException $e) {
        }
    }
}

ensureTenantSchema($pdo);

function seedInitialSuperAdmin(PDO $pdo): void {
    $superAdminEmail = 'superadmin@homeworkout.local';
    $superAdminPassword = 'ChangeMe123!';
    $superAdminTenantId = 1;

    try {
        $stmtCheck = $pdo->prepare("SELECT id FROM utenti WHERE email = :email LIMIT 1");
        $stmtCheck->execute(['email' => $superAdminEmail]);
        $existingUserId = $stmtCheck->fetchColumn();

        if ($existingUserId) {
            $stmtRole = $pdo->prepare("SELECT ruolo_id FROM utente_ruolo WHERE utente_id = :uid LIMIT 1");
            $stmtRole->execute(['uid' => $existingUserId]);
            $currentRole = (int)($stmtRole->fetchColumn() ?: 0);

            if ($currentRole !== 4) {
                $stmtUpsertRole = $pdo->prepare("INSERT INTO utente_ruolo (utente_id, ruolo_id) VALUES (:uid, 4)
                    ON DUPLICATE KEY UPDATE ruolo_id = VALUES(ruolo_id)");
                $stmtUpsertRole->execute(['uid' => $existingUserId]);
            }

            $stmtTenant = $pdo->prepare("UPDATE utenti SET tenant_id = :tenant_id WHERE id = :uid AND tenant_id IS NULL");
            $stmtTenant->execute(['tenant_id' => $superAdminTenantId, 'uid' => $existingUserId]);

            return;
        }

        $passwordHash = password_hash($superAdminPassword, PASSWORD_BCRYPT);
        $stmtInsertUser = $pdo->prepare("INSERT INTO utenti (tenant_id, nome, cognome, email, password, livello)
            VALUES (:tenant_id, :nome, :cognome, :email, :password, 'avanzato')");
        $stmtInsertUser->execute([
            'tenant_id' => $superAdminTenantId,
            'nome' => 'Super',
            'cognome' => 'Admin',
            'email' => $superAdminEmail,
            'password' => $passwordHash
        ]);

        $superAdminUserId = (int)$pdo->lastInsertId();
        $stmtInsertRole = $pdo->prepare("INSERT INTO utente_ruolo (utente_id, ruolo_id) VALUES (:uid, 4)");
        $stmtInsertRole->execute(['uid' => $superAdminUserId]);
    } catch (PDOException $e) {
        // Seed iniziale già presente o non applicabile
    }
}

seedInitialSuperAdmin($pdo);

function ensureRoleSeedConsistency(PDO $pdo): void {
    $roleMap = [
        1 => ['utente', 'Utente standard'],
        3 => ['amministratore', 'Amministratore piattaforma'],
        4 => ['super_admin', 'Super amministratore multi-tenant'],
    ];

    foreach ($roleMap as $id => [$nomeRuolo, $descrizione]) {
        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM ruoli WHERE id = :id LIMIT 1");
            $stmtCheck->execute(['id' => $id]);
            if ($stmtCheck->fetchColumn()) {
                $stmtUpdate = $pdo->prepare("UPDATE ruoli SET nome_ruolo = :nome_ruolo, descrizione = :descrizione WHERE id = :id");
                $stmtUpdate->execute([
                    'id' => $id,
                    'nome_ruolo' => $nomeRuolo,
                    'descrizione' => $descrizione,
                ]);
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO ruoli (id, nome_ruolo, descrizione) VALUES (:id, :nome_ruolo, :descrizione)");
                $stmtInsert->execute([
                    'id' => $id,
                    'nome_ruolo' => $nomeRuolo,
                    'descrizione' => $descrizione,
                ]);
            }
        } catch (PDOException $e) {
            // Se il ruolo non può essere aggiornato, lasciamo il seed attuale
        }
    }
}

ensureRoleSeedConsistency($pdo);

function removeDeprecatedTrainerRole(PDO $pdo): void {
    try {
        if (tableExists($pdo, 'utente_ruolo')) {
            $pdo->exec("UPDATE utente_ruolo SET ruolo_id = 1 WHERE ruolo_id = 2");
        }

        if (tableExists($pdo, 'ruoli')) {
            $pdo->exec("DELETE FROM ruoli WHERE id = 2");
        }
    } catch (PDOException $e) {
        // Migrazione non applicabile, lasciamo il database invariato
    }
}

removeDeprecatedTrainerRole($pdo);
?>