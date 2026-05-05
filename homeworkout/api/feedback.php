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
$action = $_GET['action'] ?? '';

if ($tenant_id === null) {
    echo json_encode(['error' => 'Tenant non disponibile']);
    exit;
}

if ($action === 'finale' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $commenti = $data['feedback'] ?? '';
    $valutazione = isset($data['valutazione']) ? (int)$data['valutazione'] : null;

    try {
        // trova piano attivo
        $stmt = $pdo->prepare("SELECT id FROM piani_allenamento WHERE utente_id = :utente_id AND tenant_id = :tenant_id ORDER BY id DESC LIMIT 1");
        $stmt->execute(['utente_id' => $utente_id, 'tenant_id' => $tenant_id]);
        $piano = $stmt->fetch();
        if (!$piano) {
            echo json_encode(['error' => 'Nessun piano trovato']);
            exit;
        }
        $piano_id = $piano['id'];

        $sql = "INSERT INTO feedback_allenamento (tenant_id, utente_id, piano_id, valutazione, commenti)
                VALUES (:tenant_id, :utente_id, :piano_id, :valutazione, :commenti)";
        $stmtIns = $pdo->prepare($sql);
        $stmtIns->execute([
            'tenant_id' => $tenant_id,
            'utente_id' => $utente_id,
            'piano_id' => $piano_id,
            'valutazione' => $valutazione,
            'commenti' => $commenti
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Azione non riconosciuta']);
