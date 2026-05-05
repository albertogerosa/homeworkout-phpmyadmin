<?php
// Script CLI per inviare notifiche programmate agli utenti (inserisce righe in `notifiche` e marca come inviato)
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../tenant_helper.php';

// Usa il formato HH:MM per confrontare
$ora_now = date('H:i');

try {
    $sql = "SELECT q.utente_id, q.tenant_id, q.orario_notifica, q.notifiche_attive, u.email, u.nome, u.cognome
            FROM quiz_risposte q
            JOIN utenti u ON u.id = q.utente_id
            WHERE q.notifiche_attive = 1 AND q.orario_notifica IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $inviate = 0;
    foreach ($rows as $r) {
        $orario = substr($r['orario_notifica'], 0, 5);
        if ($orario === $ora_now) {
            $testo = "È ora del tuo allenamento: inizia la sessione!";
            $sqlIns = "INSERT INTO notifiche (tenant_id, utente_id, tipo, testo, inviato, data_programmata) VALUES (:tenant_id, :utente_id, 'allenamento', :testo, 0, NOW())";
            $s2 = $pdo->prepare($sqlIns);
            $s2->execute(['tenant_id' => $r['tenant_id'], 'utente_id' => $r['utente_id'], 'testo' => $testo]);
            $inviate++;
            // Qui potresti integrare l'invio reale via email/SMS/push
        }
    }

    echo "Notifiche programmate inserite: " . $inviate . "\n";
} catch (PDOException $e) {
    echo "Errore DB: " . $e->getMessage() . "\n";
}
