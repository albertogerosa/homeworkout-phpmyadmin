<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manuale Utente - Homeworkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
        }

        .hero {
            background: linear-gradient(120deg, #1f4aa8, #2f6bdb);
            color: #fff;
            border-radius: 14px;
            padding: 26px;
        }

        .card-section {
            border: 1px solid #e3e8f2;
            border-radius: 12px;
        }

        .step-badge {
            display: inline-block;
            min-width: 30px;
            text-align: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: #0d6efd;
            color: #fff;
            font-weight: 700;
            margin-right: 8px;
        }

        .hint {
            border-left: 4px solid #198754;
            background: #f2fbf6;
            padding: 12px;
            border-radius: 8px;
        }

        .warn {
            border-left: 4px solid #dc3545;
            background: #fff4f4;
            padding: 12px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <main class="container py-4 py-md-5">
        <section class="hero mb-4">
            <h1 class="h3 mb-2">Manuale Utente di Homeworkout</h1>
            <p class="mb-0">Guida pratica per orientarti nel sito: registrazione, login, dashboard e funzioni principali.</p>
        </section>

        <section class="card card-section mb-3">
            <div class="card-body">
                <h2 class="h5">1. Accesso al sito</h2>
                <p>Per iniziare vai alla home del progetto: <a href="/homeworkout/">Apri Homeworkout</a>.</p>
                <ul class="mb-0">
                    <li>Se non hai un account, clicca <strong>Registrati</strong>.</li>
                    <li>Se hai gia un account, clicca <strong>Login</strong>.</li>
                </ul>
            </div>
        </section>

        <section class="card card-section mb-3">
            <div class="card-body">
                <h2 class="h5">2. Registrazione</h2>
                <p>Compila i campi richiesti:</p>
                <ul>
                    <li>Nome</li>
                    <li>Cognome</li>
                    <li>Email valida</li>
                    <li>Password (almeno 6 caratteri)</li>
                    <li>Conferma password</li>
                </ul>
                <p class="mb-0">Alla registrazione completata apparira un messaggio di conferma e potrai accedere dalla pagina di login.</p>
            </div>
        </section>

        <section class="card card-section mb-3">
            <div class="card-body">
                <h2 class="h5">3. Login e accesso dashboard</h2>
                <p>Dopo il login corretto verrai portato nella dashboard personale.</p>
                <div class="hint mt-2">
                    La dashboard cambia in base al ruolo: <strong>utente</strong>, <strong>allenatore</strong> o <strong>amministratore</strong>.
                </div>
            </div>
        </section>

        <section class="card card-section mb-3">
            <div class="card-body">
                <h2 class="h5">4. Come usare la dashboard (ruolo utente)</h2>

                <p><span class="step-badge">4.1</span><strong>Tab Home</strong></p>
                <ul>
                    <li>Se e il primo accesso, premi <strong>Inizia Quiz</strong>.</li>
                    <li>Compila il quiz con eta, livello fitness, obiettivo e orario allenamento.</li>
                    <li>Il sistema crea un piano personalizzato di 28 giorni.</li>
                </ul>

                <p><span class="step-badge">4.2</span><strong>Tab Oggi</strong></p>
                <ul>
                    <li>Vedi l'esercizio assegnato per il giorno corrente.</li>
                    <li>Clicca <strong>Completa Esercizio</strong> per salvare ripetizioni, serie e feedback.</li>
                </ul>

                <p><span class="step-badge">4.3</span><strong>Tab Progressi</strong></p>
                <ul>
                    <li>Controlli i dati degli ultimi allenamenti.</li>
                    <li>Visualizzi statistiche per esercizio (volte completato e ripetizioni totali).</li>
                </ul>

                <p><span class="step-badge">4.4</span><strong>Tab Amici</strong></p>
                <ul>
                    <li>Vedi la lista amici e puoi cercarne di nuovi.</li>
                    <li>Da qui puoi inviare richieste amicizia.</li>
                </ul>

                <p><span class="step-badge">4.5</span><strong>Tab Classifica</strong></p>
                <ul class="mb-0">
                    <li>Classifica amici: confronto con i tuoi amici.</li>
                    <li>Classifica mondiale: confronto con tutti gli utenti.</li>
                </ul>
            </div>
        </section>

        <section class="card card-section mb-3">
            <div class="card-body">
                <h2 class="h5">5. Altri ruoli</h2>
                <ul class="mb-0">
                    <li><strong>Allenatore:</strong> puo consultare progressi e classifiche.</li>
                    <li><strong>Amministratore:</strong> vede la sezione admin con riepilogo ruoli e supervisione.</li>
                </ul>
            </div>
        </section>

        <section class="card card-section mb-4">
            <div class="card-body">
                <h2 class="h5">6. Problemi comuni</h2>
                <div class="warn mb-2">
                    Se non riesci a entrare, verifica email e password e riprova il login.
                </div>
                <ul class="mb-0">
                    <li>Se non vedi esercizi in <strong>Oggi</strong>, completa prima il quiz.</li>
                    <li>Se la sessione scade, rifai il login.</li>
                    <li>Per uscire in sicurezza usa sempre il pulsante <strong>Esci</strong>.</li>
                </ul>
            </div>
        </section>

        <div class="d-flex flex-wrap gap-2">
            <a href="/homeworkout/" class="btn btn-primary">Apri Homeworkout</a>
            <a href="../informatica/homeworkout_mockup.php" class="btn btn-outline-primary">Documentazione tecnica</a>
            <a href="diagramma classi/index.php" class="btn btn-outline-info">Diagramma classi</a>
            <a href="home.php" class="btn btn-outline-secondary">Torna ai Progetti GPO</a>
        </div>
    </main>
</body>
</html>
