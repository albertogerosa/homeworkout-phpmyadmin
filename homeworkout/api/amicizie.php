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

if ($action === 'classifica_amici') {
    try {
        $sql = "SELECT u.id, u.nome, u.cognome, 
                COUNT(DISTINCT DATE(p.data_allenamento)) as giorni_allenamento,
                SUM(p.ripetizioni_fatte) as ripetizioni_totali
                FROM utenti u
                LEFT JOIN progressi_dettaglio p ON u.id = p.utente_id AND p.tenant_id = :tenant_id
                INNER JOIN amicizie a ON ((a.utente_id = :utente_id AND a.amico_id = u.id AND a.stato = 'accepted')
                                     OR (a.amico_id = :utente_id AND a.utente_id = u.id AND a.stato = 'accepted')) AND a.tenant_id = :tenant_id
                WHERE u.tenant_id = :tenant_id
                GROUP BY u.id
                ORDER BY ripetizioni_totali DESC
                LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id] + $tenantParams);
        $amici = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'amici' => $amici]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'classifica_mondiale') {
    try {
        $sql = "SELECT u.id, u.nome, u.cognome, 
                COUNT(DISTINCT DATE(p.data_allenamento)) as giorni_allenamento,
                SUM(p.ripetizioni_fatte) as ripetizioni_totali
                FROM utenti u
                LEFT JOIN progressi_dettaglio p ON u.id = p.utente_id AND p.tenant_id = :tenant_id
                WHERE u.tenant_id = :tenant_id
                GROUP BY u.id
                ORDER BY ripetizioni_totali DESC
                LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($tenantParams);
        $utenti = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'utenti' => $utenti]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'cerca_utente') {
    try {
        $query = trim($_GET['q'] ?? '');
        if ($query === '' || mb_strlen($query) < 2) {
            echo json_encode(['success' => true, 'utenti' => []]);
            exit;
        }

        $sql = "SELECT u.id, u.nome, u.cognome, u.email,
                (
                    SELECT a.stato
                    FROM amicizie a
                    WHERE a.tenant_id = :tenant_id
                      AND ((a.utente_id = :utente_id AND a.amico_id = u.id)
                           OR (a.amico_id = :utente_id AND a.utente_id = u.id))
                    ORDER BY a.id DESC
                    LIMIT 1
                ) AS stato_amicizia
                FROM utenti u
                WHERE u.tenant_id = :tenant_id
                  AND u.id <> :utente_id
                  AND (
                      u.nome LIKE :term
                      OR u.cognome LIKE :term
                      OR u.email LIKE :term
                  )
                ORDER BY u.nome ASC, u.cognome ASC
                LIMIT 10";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'tenant_id' => $tenant_id,
            'utente_id' => $utente_id,
            'term' => '%' . $query . '%'
        ]);

        echo json_encode(['success' => true, 'utenti' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'add_amico' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $amico_id = $data['amico_id'] ?? null;
        if (!$amico_id) {
            echo json_encode(['error' => 'Amico non valido']);
            exit;
        }

        $stmtVerify = $pdo->prepare("SELECT id FROM utenti WHERE id = :id AND tenant_id = :tenant_id LIMIT 1");
        $stmtVerify->execute(['id' => $amico_id] + $tenantParams);
        if (!$stmtVerify->fetch()) {
            echo json_encode(['error' => 'Utente non appartenente al tenant corrente']);
            exit;
        }
        
        $stmtExisting = $pdo->prepare("SELECT id, stato FROM amicizie
            WHERE tenant_id = :tenant_id
              AND ((utente_id = :utente_id AND amico_id = :amico_id)
               OR (utente_id = :amico_id AND amico_id = :utente_id))
            ORDER BY id DESC
            LIMIT 1");
        $stmtExisting->execute([
            'tenant_id' => $tenant_id,
            'utente_id' => $utente_id,
            'amico_id' => $amico_id
        ]);
        $existing = $stmtExisting->fetch();

        if ($existing) {
            echo json_encode(['success' => true, 'status' => $existing['stato']]);
            exit;
        }

        $sql = "INSERT INTO amicizie (tenant_id, utente_id, amico_id, stato) VALUES (:tenant_id, :utente_id, :amico_id, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id, 'amico_id' => $amico_id] + $tenantParams);
        
        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
