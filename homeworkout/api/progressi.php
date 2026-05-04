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

if ($action === 'statistiche') {
    try {
        $sql = "SELECT * FROM statistiche_esercizio WHERE utente_id = :utente_id" . $tenantFilter . " ORDER BY volte_completato DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id] + $tenantParams);
        $stats = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'progressi_ultimi_7') {
    try {
        $sql = "SELECT DATE(data_allenamento) as data, COUNT(*) as esercizi_fatti, SUM(ripetizioni_fatte) as ripetizioni_totali 
                FROM progressi_dettaglio 
            WHERE utente_id = :utente_id AND data_allenamento >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)" . $tenantFilter . "
                GROUP BY DATE(data_allenamento) 
                ORDER BY data ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id] + $tenantParams);
        $progressi = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $progressi]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'totali') {
    try {
        $sql = "SELECT COUNT(DISTINCT DATE(data_allenamento)) as giorni_allenamento, 
                COUNT(*) as esercizi_completati,
                SUM(ripetizioni_fatte) as ripetizioni_totali,
                AVG(difficolta_eseguita) as difficolta_media
            FROM progressi_dettaglio WHERE utente_id = :utente_id" . $tenantFilter;
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id] + $tenantParams);
        $totali = $stmt->fetch();
        
        echo json_encode(['success' => true, 'totali' => $totali]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
