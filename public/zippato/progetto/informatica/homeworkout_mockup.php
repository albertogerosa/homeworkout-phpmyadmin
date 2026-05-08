<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mockup Rotte - HomeWorkout</title>
    <style>
        :root {
            --bg-1: #f5f7fb;
            --bg-2: #eaf0ff;
            --card: #ffffff;
            --ink: #182033;
            --muted: #5f6880;
            --line: #dce3f3;
            --accent: #165dff;
            --accent-2: #00a6a6;
            --warn: #f59f00;
            --ok: #2b8a3e;
            --danger: #d9480f;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at 15% 15%, #ffffff 0%, var(--bg-1) 45%, var(--bg-2) 100%);
            min-height: 100vh;
        }

        .wrap {
            width: min(1200px, 94%);
            margin: 28px auto 56px;
        }

        .hero {
            background: linear-gradient(120deg, #0f3f9c, #165dff 55%, #00a6a6);
            color: #fff;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 14px 30px rgba(22, 93, 255, 0.25);
            margin-bottom: 18px;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: clamp(1.6rem, 2.8vw, 2.2rem);
            letter-spacing: 0.2px;
        }

        .hero p {
            margin: 0;
            opacity: 0.94;
        }

        .note {
            background: #fff;
            border: 1px dashed #9cb5ff;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
            color: #21305c;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 14px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 7px 16px rgba(24, 32, 51, 0.07);
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 1.05rem;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .tag {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            border-radius: 999px;
            padding: 4px 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .tag.public { background: #e7f5ff; color: #1864ab; }
        .tag.auth { background: #fff3bf; color: #7a4e00; }
        .tag.api { background: #e6fcf5; color: #0b7285; }
        .tag.jwt { background: #fff0f6; color: #a61e4d; }

        .route-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 9px;
        }

        .route-item {
            border: 1px solid #e9edf7;
            background: #fbfcff;
            border-radius: 10px;
            padding: 10px;
        }

        .path {
            font-family: "Courier New", Courier, monospace;
            font-size: 0.87rem;
            color: #1c2f66;
            word-break: break-all;
            display: block;
            margin-bottom: 4px;
        }

        .desc {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.35;
        }

        .method {
            display: inline-block;
            min-width: 58px;
            text-align: center;
            border-radius: 7px;
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-right: 6px;
            border: 1px solid transparent;
        }

        .m-get { background: #e7f5ff; color: #1c7ed6; border-color: #a5d8ff; }
        .m-post { background: #fff4e6; color: #d9480f; border-color: #ffd8a8; }

        .tabs {
            display: grid;
            gap: 8px;
            margin-top: 10px;
        }

        .tab {
            padding: 8px 10px;
            border-left: 4px solid var(--accent);
            background: #f7f9ff;
            border-radius: 8px;
            color: #2e3d6d;
            font-size: 0.9rem;
        }

        .footer-actions {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            text-decoration: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 700;
            border: 1px solid transparent;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(120deg, var(--accent), var(--accent-2));
            box-shadow: 0 6px 14px rgba(22, 93, 255, 0.25);
        }

        .btn-secondary {
            color: #1f2f5c;
            background: #fff;
            border-color: #cbd5ef;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        @media (max-width: 740px) {
            .hero, .card { border-radius: 12px; }
            .wrap { width: min(1200px, 96%); }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="hero">
            <h1>Mockup completo rotte e viste - HomeWorkout</h1>
            <p>Panoramica funzionale delle pagine utente e degli endpoint backend presenti nel progetto.</p>
        </section>

        <section class="note">
            Questa pagina e un mockup di navigazione: raccoglie le rotte principali trovate in HomeWorkout e le divide per area (Web, API applicative, API JWT), includendo anche le funzionalita piu recenti del dashboard e della gestione multi-palestra.
        </section>

        <div class="legend">
            <span class="tag public">Pubblica</span>
            <span class="tag auth">Richiede sessione</span>
            <span class="tag api">API JSON</span>
            <span class="tag jwt">JWT / Sicurezza</span>
        </div>

        <section class="grid">
            <article class="card">
                <h2>Viste Web (frontend PHP)</h2>
                <ul class="route-list">
                    <li class="route-item">
                        <span class="path">/homeworkout/index.php</span>
                        <div class="desc">Landing iniziale con scelta Login / Registrazione <span class="tag public">Pubblica</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/login.php</span>
                        <div class="desc">Form login e creazione sessione + token <span class="tag public">Pubblica</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/register.php</span>
                        <div class="desc">Form registrazione utente <span class="tag public">Pubblica</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/dashboard.php</span>
                        <div class="desc">Dashboard principale con tab dinamiche in base al ruolo <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/esercizi.php</span>
                        <div class="desc">Vista gestione e consultazione esercizi <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/logout.php</span>
                        <div class="desc">Chiude sessione e reindirizza alla home <span class="tag auth">Sessione</span></div>
                    </li>
                </ul>
            </article>

            <article class="card">
                <h2>Viste interne Dashboard</h2>
                <div class="tabs">
                    <div class="tab">Home con stato quiz, streak, piano attivo, progressi rapidi e riepilogo ultimi workout</div>
                    <div class="tab">Oggi con esercizio del giorno, completamento allenamento e messaggio esplicito se manca l'esercizio</div>
                    <div class="tab">Progressi con statistiche esercizi e andamento degli ultimi 7 giorni</div>
                    <div class="tab">Amici con richieste, ricerca e gestione relazioni</div>
                    <div class="tab">Classifica amici e classifica mondiale</div>
                    <div class="tab">Admin con panoramica utenti, piani e richieste recenti del tenant</div>
                    <div class="tab">Super admin con creazione, attivazione e assegnazione delle palestre</div>
                </div>
            </article>

            <article class="card">
                <h2>API applicative HomeWorkout</h2>
                <ul class="route-list">
                    <li class="route-item">
                        <span class="path">/homeworkout/api/quiz.php</span>
                        <div class="desc"><span class="method m-post">POST</span>Salva quiz utente, aggiorna le impostazioni notifiche e genera un nuovo piano da 28 giorni chiudendo i piani attivi precedenti <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/esercizi.php?action=oggi</span>
                        <div class="desc"><span class="method m-get">GET</span>Recupera l'esercizio del giorno dal piano attivo e segnala un errore esplicito se non esiste un esercizio per oggi <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/esercizi.php?action=completa</span>
                        <div class="desc"><span class="method m-post">POST</span>Salva completamento allenamento e aggiorna statistiche <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/piani.php?action=create_new</span>
                        <div class="desc"><span class="method m-post">POST</span>Crea un nuovo piano partendo dal quiz, chiude il piano attivo corrente e rigenera gli esercizi <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/piani.php?action=set_rest</span>
                        <div class="desc"><span class="method m-post">POST</span>Salva i giorni di riposo consigliati per l'utente <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/progressi.php?action=statistiche</span>
                        <div class="desc"><span class="method m-get">GET</span>Statistiche per esercizio <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/progressi.php?action=progressi_ultimi_7</span>
                        <div class="desc"><span class="method m-get">GET</span>Serie temporale ultimi 7 giorni <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/progressi.php?action=totali</span>
                        <div class="desc"><span class="method m-get">GET</span>KPI complessivi utente <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/amicizie.php?action=classifica_amici</span>
                        <div class="desc"><span class="method m-get">GET</span>Classifica tra amici accettati <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/amicizie.php?action=classifica_mondiale</span>
                        <div class="desc"><span class="method m-get">GET</span>Classifica globale utenti <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/amicizie.php?action=add_amico</span>
                        <div class="desc"><span class="method m-post">POST</span>Invia richiesta di amicizia <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/qapi.php</span>
                        <div class="desc"><span class="method m-get">GET</span>Ritorna info utente/ruolo da token (header Bearer, query o sessione) <span class="tag api">JSON</span> <span class="tag jwt">JWT</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/api/tenants.php?action=list|create|activate|assign_user</span>
                        <div class="desc"><span class="method m-get">GET</span><span class="method m-post">POST</span>Gestione palestre per il super admin: elenco, creazione, attivazione e assegnazione utenti <span class="tag api">JSON</span> <span class="tag auth">Sessione</span></div>
                    </li>
                </ul>
            </article>

            <article class="card">
                <h2>API JWT e autorizzazioni</h2>
                <ul class="route-list">
                    <li class="route-item">
                        <span class="path">/homeworkout/JWT/auth_api.php</span>
                        <div class="desc"><span class="method m-post">POST</span>Login API: emette access token (5 min) + refresh token (10 min) <span class="tag jwt">JWT</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/JWT/refresh_api.php</span>
                        <div class="desc"><span class="method m-post">POST</span>Rinnova access token tramite refresh token salvato su DB <span class="tag jwt">JWT</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/JWT/refresh_token.php</span>
                        <div class="desc"><span class="method m-post">POST</span>Versione alternativa di refresh via token JWT nel body <span class="tag jwt">JWT</span></div>
                    </li>
                    <li class="route-item">
                        <span class="path">/homeworkout/JWT/get_permissions.php</span>
                        <div class="desc"><span class="method m-get">GET</span>Estrae permessi dal ruolo utente tramite Authorization: Bearer token <span class="tag jwt">JWT</span></div>
                    </li>
                </ul>
            </article>
        </section>

        <div class="footer-actions">
            <a class="btn btn-primary" href="progetti.html">Torna ai Progetti di Informatica</a>
            <a class="btn btn-secondary" href="/homeworkout/">Apri HomeWorkout</a>
        </div>
    </main>
</body>
</html>
