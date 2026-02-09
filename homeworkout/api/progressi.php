<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

$utente_id = $_SESSION['utente_id'];
$action = $_GET['action'] ?? '';

if ($action === 'statistiche') {
    try {
        $sql = "SELECT * FROM statistiche_esercizio WHERE utente_id = :utente_id ORDER BY volte_completato DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id]);
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
                WHERE utente_id = :utente_id AND data_allenamento >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(data_allenamento) 
                ORDER BY data ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id]);
        $progressi = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $progressi]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'totali') {
    try {
        $sql = "SELECT COUNT(DISTINCT DATE(data_allenamento)) as giorni_allenamento, 
                SUM(ripetizioni_fatte) as ripetizioni_totali,
                AVG(difficolta_eseguita) as difficolta_media
                FROM progressi_dettaglio WHERE utente_id = :utente_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id]);
        $totali = $stmt->fetch();
        
        echo json_encode(['success' => true, 'totali' => $totali]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
