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

if ($tenant_id === null) {
    echo json_encode(['error' => 'Tenant non disponibile']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $eta = $data['eta'] ?? null;
    $livello = $data['livello_fitness'] ?? 'principiante';
    $obiettivo = $data['obiettivo'] ?? '';
    $orario = $data['orario_notifica'] ?? '08:00';
    
    try {
        // Salva risposte quiz
        $sql = "INSERT INTO quiz_risposte (tenant_id, utente_id, eta, livello_fitness, obiettivo, orario_notifica, completato) 
            VALUES (:tenant_id, :utente_id, :eta, :livello, :obiettivo, :orario, 1)
                ON DUPLICATE KEY UPDATE eta=:eta, livello_fitness=:livello, obiettivo=:obiettivo, orario_notifica=:orario";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'tenant_id' => $tenant_id,
            'utente_id' => $utente_id,
            'eta' => $eta,
            'livello' => $livello,
            'obiettivo' => $obiettivo,
            'orario' => $orario
        ]);
        
        // Crea piano personalizzato
        $data_inizio = date('Y-m-d');
        $data_fine = date('Y-m-d', strtotime('+28 days'));
        $difficolta = ($livello === 'principiante') ? 2 : (($livello === 'intermedio') ? 3 : 4);
        
        $sql_piano = "INSERT INTO piani_allenamento (tenant_id, utente_id, data_inizio, data_fine, difficolta, stato) 
                      VALUES (:tenant_id, :utente_id, :inizio, :fine, :diff, 'attivo')";
        $stmt_piano = $pdo->prepare($sql_piano);
        $stmt_piano->execute([
            'tenant_id' => $tenant_id,
            'utente_id' => $utente_id,
            'inizio' => $data_inizio,
            'fine' => $data_fine,
            'diff' => $difficolta
        ]);
        
        $piano_id = $pdo->lastInsertId();
        
        // Genera esercizi per ogni giorno
        $esercizi_base = [
            'principiante' => [
                ['Push-up a muro', 'A muro per principianti', 10, 3],
                ['Squat', 'Squat a corpo libero', 15, 3],
                ['Plancia', 'Plancia frontale', 20, 2],
                ['Flessioni', 'Flessioni da terra', 5, 3],
                ['Affondi', 'Affondi alternati', 10, 3],
                ['Mountain climber', 'Mountain climber', 15, 2],
                ['Salti', 'Salti sul posto', 20, 2],
            ],
            'intermedio' => [
                ['Push-up', 'Push-up standard', 15, 4],
                ['Pistol squat assist', 'Squat pistol con supporto', 8, 3],
                ['Plancia diamante', 'Plancia diamante', 30, 3],
                ['Flessioni larghe', 'Flessioni a mani larghe', 10, 4],
                ['Affondi bulgari', 'Affondi bulgari', 12, 3],
                ['Burpees', 'Burpees completi', 10, 3],
                ['Dip su sedia', 'Dip su sedia', 12, 3],
            ],
            'avanzato' => [
                ['Flessioni archer', 'Flessioni archer', 10, 4],
                ['Pistol squat', 'Squat pistol completo', 10, 4],
                ['Handstand hold', 'Handstand hold', 30, 3],
                ['Flessioni planche', 'Flessioni planche', 8, 4],
                ['Human flag', 'Human flag hold', 15, 3],
                ['Muscle up', 'Muscle up', 5, 3],
                ['L-sit', 'L-sit hold', 30, 3],
            ]
        ];
        
        $esercizi = $esercizi_base[$livello] ?? $esercizi_base['principiante'];
        
        for ($giorno = 1; $giorno <= 28; $giorno++) {
            $ex_idx = ($giorno - 1) % count($esercizi);
            $ex = $esercizi[$ex_idx];
            $moltiplicatore = 1.0 + (floor(($giorno - 1) / 7) * 0.15);
            
            $sql_ex = "INSERT INTO esercizi_piano (tenant_id, piano_id, nome_esercizio, descrizione, ripetizioni, serie, giorno, difficolta_moltiplicatore) 
                       VALUES (:tenant_id, :piano_id, :nome, :desc, :rip, :serie, :giorno, :molt)";
            $stmt_ex = $pdo->prepare($sql_ex);
            $stmt_ex->execute([
                'tenant_id' => $tenant_id,
                'piano_id' => $piano_id,
                'nome' => $ex[0],
                'desc' => $ex[1],
                'rip' => $ex[2],
                'serie' => $ex[3],
                'giorno' => $giorno,
                'molt' => $moltiplicatore
            ]);
        }
        
        echo json_encode(['success' => true, 'piano_id' => $piano_id]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
