<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descrizione Applicazione HomeWorkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1 class="mb-4">Descrizione Applicazione HomeWorkout</h1>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-0">
                HomeWorkout e una web app PHP/MySQL per allenamento a casa con accesso multi-tenant (piu palestre/organizzazioni),
                autenticazione utente, generazione automatica di piani da quiz e monitoraggio dei progressi nel tempo.
                Include anche componenti social (amicizie/classifiche) e funzioni amministrative (ruoli, gestione tenant).
            </p>
        </div>
    </div>

    <h2 class="h4">Requisiti principali (funzionali)</h2>
    <ol class="mb-4">
        <li>Registrazione e login utenti con password hashate.</li>
        <li>Gestione sessione autenticata con token JWT (access + refresh).</li>
        <li>Supporto ruoli: utente, amministratore, super admin.</li>
        <li>Isolamento dati per tenant e selezione tenant attivo per super admin.</li>
        <li>Quiz iniziale fitness con salvataggio risposte e preferenze notifiche.</li>
        <li>Creazione automatica piano allenamento di 28 giorni in base al livello.</li>
        <li>Gestione piani: chiusura piano attivo e creazione nuovo piano.</li>
        <li>Tracciamento progressi e statistiche (ultimi 7 giorni, totali, streak).</li>
        <li>Dashboard con riepilogo attivita, piano attivo e KPI.</li>
        <li>Funzioni social: ricerca utenti, richieste amicizia, classifiche.</li>
        <li>Funzioni admin: cambio ruolo, assegnazione tenant, gestione tenant.</li>
    </ol>

    <h2 class="h4">Requisiti tecnici (non funzionali/minimi)</h2>
    <ol class="mb-4">
        <li>PHP 8+ con PDO per accesso database MySQL.</li>
        <li>Sessioni server-side attive e gestione sicura dello stato utente.</li>
        <li>API JSON con controlli di autenticazione/autorizzazione.</li>
        <li>Validazioni input lato server e gestione errori DB.</li>
        <li>Vincoli database coerenti per relazioni utenti-ruoli-tenant-piani-progressi.</li>
    </ol>

    <a href="../../gpo.php" class="btn btn-secondary">Torna a Progetti GPO</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
