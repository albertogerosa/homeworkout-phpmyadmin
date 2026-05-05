<?php
// Script CLI per calcolare la continuità di allenamento per ogni utente e aggiornare periodi_riposo
require_once __DIR__ . '/../database.php';

try {
    // Prendi tutti gli utenti
    $stmtUsers = $pdo->query("SELECT id, tenant_id FROM utenti");
    $users = $stmtUsers->fetchAll();

    foreach ($users as $u) {
        $uid = (int)$u['id'];
        $tenant_id = $u['tenant_id'];

        // prendi le ultime date di allenamento distinte per utente
        $stmtDates = $pdo->prepare("SELECT DISTINCT DATE(data_allenamento) as d FROM progressi_dettaglio WHERE utente_id = :uid AND tenant_id = :tid ORDER BY d DESC LIMIT 30");
        $stmtDates->execute(['uid' => $uid, 'tid' => $tenant_id]);
        $dates = $stmtDates->fetchAll(PDO::FETCH_COLUMN, 0);

        // calcola streak
        $streak = 0;
        $cursor = date('Y-m-d');
        $lookup = array_flip($dates ?: []);
        while (isset($lookup[$cursor])) {
            $streak++;
            $cursor = date('Y-m-d', strtotime($cursor . ' -1 day'));
        }

        // recupera giorni_riposo_consigliati se esiste
        $stmtPR = $pdo->prepare("SELECT id, giorni_riposo_consigliati FROM periodi_riposo WHERE utente_id = :uid AND tenant_id = :tid LIMIT 1");
        $stmtPR->execute(['uid' => $uid, 'tid' => $tenant_id]);
        $pr = $stmtPR->fetch();
        $giorni_consigliati = $pr['giorni_riposo_consigliati'] ?? 1;

        // aggiorna o inserisci record
        if ($pr) {
            $stmtUpd = $pdo->prepare("UPDATE periodi_riposo SET giorni_consecutivi = :g, ultimo_allenamento = (SELECT MAX(data_allenamento) FROM progressi_dettaglio WHERE utente_id = :uid AND tenant_id = :tid) WHERE id = :id");
            $stmtUpd->execute(['g' => $streak, 'uid' => $uid, 'tid' => $tenant_id, 'id' => $pr['id']]);
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO periodi_riposo (tenant_id, utente_id, giorni_consecutivi, giorni_riposo_consigliati, ultimo_allenamento) VALUES (:tid, :uid, :g, :consigliati, (SELECT MAX(data_allenamento) FROM progressi_dettaglio WHERE utente_id = :uid AND tenant_id = :tid))");
            $stmtIns->execute(['tid' => $tenant_id, 'uid' => $uid, 'g' => $streak, 'consigliati' => $giorni_consigliati]);
        }

        // se supera la soglia suggeriamo un riposo
        if ($streak > 0 && $streak % 7 === 0) {
            $testo = "Hai completato $streak giorni di allenamento consecutivi: considera un periodo di riposo di $giorni_consigliati giorni.";
            $stmtNotif = $pdo->prepare("INSERT INTO notifiche (tenant_id, utente_id, tipo, testo, inviato, data_programmata) VALUES (:tid, :uid, 'riposo', :testo, 0, NOW())");
            $stmtNotif->execute(['tid' => $tenant_id, 'uid' => $uid, 'testo' => $testo]);
        }
    }

    echo "Valutazione periodi di riposo completata.\n";
} catch (PDOException $e) {
    echo "Errore DB: " . $e->getMessage() . "\n";
}
