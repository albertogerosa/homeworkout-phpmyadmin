<?php
session_start();
require_once '../database.php';
require_once '../tenant_helper.php';

if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

$utente_id = $_SESSION['utente_id'];
$tenant_id = homeworkoutCurrentTenantId();
$tenantFilter = ' AND tenant_id = :tenant_id';
$tenantParams = ['tenant_id' => $tenant_id];
$action = $_GET['action'] ?? '';

if ($tenant_id === null) {
    echo json_encode(['error' => 'Tenant non disponibile']);
    exit;
}

if ($action === 'oggi') {
    try {
        $sql = "SELECT id, data_inizio FROM piani_allenamento WHERE utente_id = :utente_id AND stato = 'attivo'" . $tenantFilter . " ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id] + $tenantParams);
        $piano = $stmt->fetch();
        
        if (!$piano) {
            echo json_encode(['error' => 'Nessun piano attivo']);
            exit;
        }
        
        $data_inizio = $piano['data_inizio'];
        
        $giorno_oggi = (int)((time() - strtotime($data_inizio)) / 86400) + 1;
        $giorno_oggi = max(1, min($giorno_oggi, 28));
        
        $sql_ex = "SELECT * FROM esercizi_piano WHERE piano_id = :piano_id AND giorno = :giorno" . $tenantFilter . " LIMIT 1";
        $stmt_ex = $pdo->prepare($sql_ex);
        $stmt_ex->execute(['piano_id' => $piano['id'], 'giorno' => $giorno_oggi] + $tenantParams);
        $esercizio = $stmt_ex->fetch();

        if (!$esercizio) {
            echo json_encode(['error' => 'Nessun esercizio disponibile per oggi']);
            exit;
        }
        
        $sql_check = "SELECT id FROM progressi_dettaglio WHERE utente_id = :utente_id AND esercizio_id = :esercizio_id AND DATE(data_allenamento) = CURDATE()" . $tenantFilter;
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['utente_id' => $utente_id, 'esercizio_id' => $esercizio['id']] + $tenantParams);
        $gia_completato = $stmt_check->fetch() ? true : false;
        
        echo json_encode([
            'success' => true,
            'esercizio' => $esercizio,
            'giorno_piano' => $giorno_oggi,
            'gia_completato' => $gia_completato
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'completa' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $esercizio_id = $data['esercizio_id'] ?? null;
        $ripetizioni = $data['ripetizioni'] ?? 0;
        $serie = $data['serie'] ?? 0;
        $feedback = $data['feedback'] ?? '';
        $difficolta = $data['difficolta'] ?? 1.0;

        if (empty($esercizio_id)) {
            echo json_encode(['error' => 'Esercizio non valido']);
            exit;
        }

        $stmtVerify = $pdo->prepare("SELECT id, nome_esercizio FROM esercizi_piano WHERE id = :id AND tenant_id = :tenant_id LIMIT 1");
        $stmtVerify->execute(['id' => $esercizio_id, 'tenant_id' => $tenant_id]);
        $esercizioInfo = $stmtVerify->fetch();

        if (!$esercizioInfo) {
            echo json_encode(['error' => 'Esercizio non trovato nel tenant corrente']);
            exit;
        }
        
        $sql = "INSERT INTO progressi_dettaglio (tenant_id, utente_id, esercizio_id, data_allenamento, ripetizioni_fatte, serie_fatte, feedback, difficolta_eseguita, completato) 
                VALUES (:tenant_id, :utente_id, :esercizio_id, CURDATE(), :ripetizioni, :serie, :feedback, :difficolta, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'tenant_id' => $tenant_id,
            'utente_id' => $utente_id,
            'esercizio_id' => $esercizio_id,
            'ripetizioni' => $ripetizioni,
            'serie' => $serie,
            'feedback' => $feedback,
            'difficolta' => $difficolta
        ]);
        
        $sql_stat = "SELECT nome_esercizio FROM esercizi_piano WHERE id = :id" . $tenantFilter;
        $stmt_stat = $pdo->prepare($sql_stat);
        $stmt_stat->execute(['id' => $esercizio_id] + $tenantParams);
        $statRow = $stmt_stat->fetch();
        $nome_ex = $statRow['nome_esercizio'] ?? $esercizioInfo['nome_esercizio'];
        
        $sql_update_stat = "INSERT INTO statistiche_esercizio (tenant_id, utente_id, nome_esercizio, volte_completato, ripetizioni_totali, difficolta_media) 
                            VALUES (:tenant_id, :utente_id, :nome, 1, :ripetizioni, :difficolta)
                            ON DUPLICATE KEY UPDATE 
                            volte_completato = volte_completato + 1,
                            ripetizioni_totali = ripetizioni_totali + :ripetizioni,
                            difficolta_media = (difficolta_media + :difficolta) / 2";
        $stmt_update_stat = $pdo->prepare($sql_update_stat);
        $stmt_update_stat->execute([
            'tenant_id' => $tenant_id,
            'utente_id' => $utente_id,
            'nome' => $nome_ex,
            'ripetizioni' => $ripetizioni,
            'difficolta' => $difficolta
        ]);
        
        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
