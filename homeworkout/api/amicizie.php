<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

$utente_id = $_SESSION['utente_id'];
$action = $_GET['action'] ?? '';

if ($action === 'classifica_amici') {
    try {
        $sql = "SELECT u.id, u.nome, u.cognome, 
                COUNT(DISTINCT DATE(p.data_allenamento)) as giorni_allenamento,
                SUM(p.ripetizioni_fatte) as ripetizioni_totali
                FROM utenti u
                LEFT JOIN progressi_dettaglio p ON u.id = p.utente_id
                INNER JOIN amicizie a ON (a.utente_id = :utente_id AND a.amico_id = u.id AND a.stato = 'accepted')
                                     OR (a.amico_id = :utente_id AND a.utente_id = u.id AND a.stato = 'accepted')
                GROUP BY u.id
                ORDER BY ripetizioni_totali DESC
                LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id]);
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
                LEFT JOIN progressi_dettaglio p ON u.id = p.utente_id
                GROUP BY u.id
                ORDER BY ripetizioni_totali DESC
                LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $utenti = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'utenti' => $utenti]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($action === 'add_amico' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $amico_id = $data['amico_id'] ?? null;
        
        $sql = "INSERT INTO amicizie (utente_id, amico_id, stato) VALUES (:utente_id, :amico_id, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['utente_id' => $utente_id, 'amico_id' => $amico_id]);
        
        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
