<?php
session_start();
require_once 'database.php';
require_once __DIR__ . '/JWT/jwt_helper.php';

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit;
}

$utente_id = $_SESSION['utente_id'];
$ruolo_id = 0;
$ruolo_nome = 'utente';

$token = $_SESSION['access_token'] ?? '';
if (!empty($token)) {
    $tokenData = validateJWT($token);
    if ($tokenData && !empty($tokenData['role_id'])) {
        $ruolo_id = (int)$tokenData['role_id'];
    }
}

try {
    if ($ruolo_id <= 0) {
        $stmtRuolo = $pdo->prepare("SELECT ruolo_id FROM utente_ruolo WHERE utente_id = :utente_id LIMIT 1");
        $stmtRuolo->execute(['utente_id' => $utente_id]);
        $ruolo_id = (int)($stmtRuolo->fetchColumn() ?: 1);
    }

    $stmtNomeRuolo = $pdo->prepare("SELECT nome_ruolo FROM ruoli WHERE id = :rid LIMIT 1");
    $stmtNomeRuolo->execute(['rid' => $ruolo_id]);
    $ruolo_nome = $stmtNomeRuolo->fetchColumn() ?: 'utente';

    $_SESSION['ruolo_id'] = $ruolo_id;
    $_SESSION['ruolo_nome'] = $ruolo_nome;

    $sql_quiz = "SELECT * FROM quiz_risposte WHERE utente_id = :utente_id LIMIT 1";
    $stmt_quiz = $pdo->prepare($sql_quiz);
    $stmt_quiz->execute(['utente_id' => $utente_id]);
    $quiz_completato = $stmt_quiz->fetch();
    
    $sql_piano = "SELECT * FROM piani_allenamento WHERE utente_id = :utente_id AND stato = 'attivo' LIMIT 1";
    $stmt_piano = $pdo->prepare($sql_piano);
    $stmt_piano->execute(['utente_id' => $utente_id]);
    $piano_attivo = $stmt_piano->fetch();

    $stats_admin = [
        'utenti' => 0,
        'allenatori' => 0,
        'amministratori' => 0
    ];

    if ($ruolo_nome === 'amministratore' || $ruolo_nome === 'allenatore') {
        $stmtCount = $pdo->query("SELECT ur.ruolo_id, COUNT(*) AS totale FROM utente_ruolo ur GROUP BY ur.ruolo_id");
        while ($row = $stmtCount->fetch()) {
            if ((int)$row['ruolo_id'] === 1) $stats_admin['utenti'] = (int)$row['totale'];
            if ((int)$row['ruolo_id'] === 2) $stats_admin['allenatori'] = (int)$row['totale'];
            if ((int)$row['ruolo_id'] === 3) $stats_admin['amministratori'] = (int)$row['totale'];
        }
    }
    
} catch(PDOException $e) {
    die("Errore: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HomeWorkout</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        
        header { background: rgba(255,255,255,0.95); padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .header-flex { max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        header h1 { color: #667eea; font-size: 1.8em; }
        .user-section { display: flex; gap: 20px; align-items: center; }
        .logout-btn { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; }
        .logout-btn:hover { background: #764ba2; }
        
        .container { max-width: 1300px; margin: 0 auto; padding: 20px; }
        
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .tab { padding: 10px 20px; background: #f0f0f0; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; transition: all 0.3s; }
        .tab.active { background: #667eea; color: white; }
        .tab:hover { background: #ddd; }
        .tab.active:hover { background: #667eea; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { color: #667eea; margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .card h3 { color: #555; margin: 15px 0 10px 0; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-box .numero { font-size: 2.5em; font-weight: bold; }
        .stat-box .label { font-size: 0.9em; opacity: 0.9; }
        
        .quiz-form, .exercise-form { display: grid; gap: 15px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1em; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102, 126, 234, 0.3); }
        
        .btn { background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 10px; transition: all 0.3s; }
        .btn:hover { background: #764ba2; transform: translateY(-2px); }
        .btn-secondary { background: #999; }
        .btn-secondary:hover { background: #777; }
        
        .leaderboard { list-style: none; }
        .leaderboard li { padding: 12px; background: #f5f5f5; margin: 8px 0; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid #667eea; }
        .leaderboard li strong { color: #667eea; }
        
        .progress-bar { height: 10px; background: #e0e0e0; border-radius: 5px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); width: 50%; transition: width 0.3s; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 200; justify-content: center; align-items: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-close { position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 28px; cursor: pointer; color: #666; }
        
        .esercizio-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; }
        .esercizio-card .nome { font-size: 1.5em; font-weight: bold; margin-bottom: 10px; }
        .esercizio-card .info { opacity: 0.95; margin: 5px 0; }
        
        .badge { display: inline-block; padding: 5px 12px; background: #667eea; color: white; border-radius: 20px; font-size: 0.85em; }
        .badge.completato { background: #28a745; }
        .badge.attivo { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <header>
        <div class="header-flex">
            <h1>🏋️ HomeWorkout</h1>
            <div class="user-section">
                <span><strong><?php echo htmlspecialchars(trim(($_SESSION['nome'] ?? '') . ' ' . ($_SESSION['cognome'] ?? ''))); ?></strong></span>
                <span class="badge"><?php echo htmlspecialchars(ucfirst($ruolo_nome)); ?> (<?php echo (int)$ruolo_id; ?>)</span>
                <a href="logout.php" class="logout-btn">Esci</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="tabs">
            <button class="tab active" onclick="switchTab('home')">🏠 Home</button>
            <?php if ($ruolo_nome === 'utente'): ?>
                <button class="tab" onclick="switchTab('oggi')">💪 Oggi</button>
                <button class="tab" onclick="switchTab('progressi')">📊 Progressi</button>
                <button class="tab" onclick="switchTab('amici')">👥 Amici</button>
                <button class="tab" onclick="switchTab('classifica')">🏆 Classifica</button>
            <?php elseif ($ruolo_nome === 'allenatore'): ?>
                <button class="tab" onclick="switchTab('progressi')">📊 Progressi</button>
                <button class="tab" onclick="switchTab('classifica')">🏆 Classifica</button>
            <?php elseif ($ruolo_nome === 'amministratore'): ?>
                <button class="tab" onclick="switchTab('admin')">🛠️ Admin</button>
            <?php endif; ?>
        </div>
        
        <!-- TAB: HOME -->
        <div id="home" class="tab-content active">
            <?php if ($ruolo_nome === 'utente'): ?>
                <div class="card">
                    <h2>Benvenuto, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
                    <?php if (!$quiz_completato): ?>
                        <p style="margin-bottom: 15px;">Rispondi al quiz per ricevere un piano allenamento personalizzato!</p>
                        <button class="btn" onclick="openModal('quizModal')">📋 Inizia Quiz</button>
                    <?php else: ?>
                        <p><strong>Livello:</strong> <?php echo ucfirst($quiz_completato['livello_fitness']); ?></p>
                        <p><strong>Obiettivo:</strong> <?php echo htmlspecialchars($quiz_completato['obiettivo']); ?></p>
                        <p><strong>Orario allenamento:</strong> <?php echo $quiz_completato['orario_notifica']; ?></p>
                        <button class="btn btn-secondary" onclick="openModal('quizModal')" style="margin-top: 15px;">Modifica Quiz</button>
                    <?php endif; ?>
                </div>
                
                <?php if ($piano_attivo): ?>
                <div class="card">
                    <h2>📅 Piano Attuale</h2>
                    <div class="grid">
                        <div class="stat-box">
                            <div class="numero" id="giorni_rimanenti">-</div>
                            <div class="label">Giorni Rimanenti</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero"><?php echo $piano_attivo['difficolta']; ?></div>
                            <div class="label">Difficoltà</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero" id="esercizi_completati">0</div>
                            <div class="label">Esercizi Completati</div>
                        </div>
                    </div>
                    <div style="margin-top: 15px;">
                        <p><strong>Dal:</strong> <?php echo date('d/m/Y', strtotime($piano_attivo['data_inizio'])); ?> <strong>Al:</strong> <?php echo date('d/m/Y', strtotime($piano_attivo['data_fine'])); ?></p>
                        <div class="progress-bar">
                            <div class="progress-fill" id="plan_progress"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <h2>📈 Statistiche Generali</h2>
                    <div class="grid">
                        <div class="stat-box">
                            <div class="numero" id="giorni_allenamento">0</div>
                            <div class="label">Giorni Allenamento</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero" id="ripetizioni_totali">0</div>
                            <div class="label">Ripetizioni Totali</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero" id="difficolta_media">1.0x</div>
                            <div class="label">Difficoltà Media</div>
                        </div>
                    </div>
                </div>
            <?php elseif ($ruolo_nome === 'allenatore'): ?>
                <div class="card">
                    <h2>Dashboard Allenatore</h2>
                    <p>Visualizzi una dashboard dedicata al monitoraggio degli utenti e delle classifiche.</p>
                </div>
                <div class="card">
                    <h2>📌 Riepilogo Ruoli</h2>
                    <div class="grid">
                        <div class="stat-box">
                            <div class="numero"><?php echo (int)$stats_admin['utenti']; ?></div>
                            <div class="label">Utenti</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero"><?php echo (int)$stats_admin['allenatori']; ?></div>
                            <div class="label">Allenatori</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero"><?php echo (int)$stats_admin['amministratori']; ?></div>
                            <div class="label">Amministratori</div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>Dashboard Amministratore</h2>
                    <p>Interfaccia amministrativa attiva: gestione ruoli numerici e supervisione piattaforma.</p>
                </div>
                <div class="card">
                    <h2>📌 Totale per Ruolo</h2>
                    <div class="grid">
                        <div class="stat-box">
                            <div class="numero"><?php echo (int)$stats_admin['utenti']; ?></div>
                            <div class="label">Ruolo 1 - Utente</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero"><?php echo (int)$stats_admin['allenatori']; ?></div>
                            <div class="label">Ruolo 2 - Allenatore</div>
                        </div>
                        <div class="stat-box">
                            <div class="numero"><?php echo (int)$stats_admin['amministratori']; ?></div>
                            <div class="label">Ruolo 3 - Amministratore</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($ruolo_nome === 'utente'): ?>
        <!-- TAB: ESERCIZIO DI OGGI -->
        <div id="oggi" class="tab-content">
            <div class="card">
                <h2>💪 Esercizio di Oggi</h2>
                <div id="esercizio-oggi-container">Caricamento...</div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($ruolo_nome === 'utente' || $ruolo_nome === 'allenatore'): ?>
        <!-- TAB: PROGRESSI -->
        <div id="progressi" class="tab-content">
            <div class="card">
                <h2>📊 Progressi Ultimi 7 Giorni</h2>
                <div id="progressChart">Caricamento grafico...</div>
            </div>
            
            <div class="card">
                <h2>💡 Statistiche per Esercizio</h2>
                <div id="stats-container">Caricamento...</div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($ruolo_nome === 'utente'): ?>
        <!-- TAB: AMICI -->
        <div id="amici" class="tab-content">
            <div class="card">
                <h2>👥 I Tuoi Amici</h2>
                <ul class="leaderboard" id="amici-list">
                    <li>Nessun amico ancora</li>
                </ul>
                <button class="btn" onclick="openModal('cercaAmicoModal')" style="margin-top: 15px;">➕ Cerca Amico</button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($ruolo_nome === 'utente' || $ruolo_nome === 'allenatore'): ?>
        <!-- TAB: CLASSIFICA -->
        <div id="classifica" class="tab-content">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="card">
                    <h2>🏆 Classifica Amici</h2>
                    <ol class="leaderboard" id="classifica-amici"></ol>
                </div>
                <div class="card">
                    <h2>🌍 Classifica Mondiale</h2>
                    <ol class="leaderboard" id="classifica-mondiale"></ol>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($ruolo_nome === 'amministratore'): ?>
        <div id="admin" class="tab-content">
            <div class="card">
                <h2>🛠️ Gestione Ruoli</h2>
                <p>I ruoli nel database sono salvati solo come numeri:</p>
                <ul style="margin-top: 10px; padding-left: 20px;">
                    <li>1 = utente</li>
                    <li>2 = allenatore</li>
                    <li>3 = amministratore</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- MODAL: QUIZ -->
    <div id="quizModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('quizModal')">✕</button>
            <h2>📋 Quiz Personalizzazione</h2>
            <form class="quiz-form" onsubmit="submitQuiz(event)">
                <div>
                    <label><strong>Quanti anni hai?</strong></label>
                    <input type="number" id="quiz_eta" min="16" max="100" required>
                </div>
                <div>
                    <label><strong>Livello di fitness</strong></label>
                    <select id="quiz_livello" required>
                        <option value="principiante">🟢 Principiante - Sono nuovo all'allenamento</option>
                        <option value="intermedio">🟡 Intermedio - Alleno 2-3 volte a settimana</option>
                        <option value="avanzato">🔴 Avanzato - Mi alleno 4+ volte a settimana</option>
                    </select>
                </div>
                <div>
                    <label><strong>Qual è il tuo obiettivo?</strong></label>
                    <input type="text" id="quiz_obiettivo" placeholder="es: Dimagrire, Tonificare, Aumentare massa" required>
                </div>
                <div>
                    <label><strong>A che ora vuoi allenarti? (riceverai notifica)</strong></label>
                    <input type="time" id="quiz_ora" value="08:00" required>
                </div>
                <button type="submit" class="btn">✅ Crea Piano Personalizzato</button>
            </form>
        </div>
    </div>
    
    <!-- MODAL: COMPLETA ESERCIZIO -->
    <div id="completaEsercizioModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('completaEsercizioModal')">✕</button>
            <h2>✅ Completa Esercizio</h2>
            <form class="exercise-form" onsubmit="completeExercise(event)">
                <div>
                    <input type="hidden" id="esercizio_id">
                    <label><strong>Ripetizioni eseguite</strong></label>
                    <input type="number" id="ripetizioni_fatte" min="0" required>
                </div>
                <div>
                    <label><strong>Serie eseguite</strong></label>
                    <input type="number" id="serie_fatte" min="0" required>
                </div>
                <div>
                    <label><strong>Feedback (opzionale)</strong></label>
                    <textarea id="feedback_esercizio" rows="3" placeholder="Come è andato? Difficile? Facile?"></textarea>
                </div>
                <button type="submit" class="btn">💾 Salva Progresso</button>
            </form>
        </div>
    </div>
    
    <!-- MODAL: CERCA AMICO -->
    <div id="cercaAmicoModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('cercaAmicoModal')">✕</button>
            <h2>🔍 Cerca Amico</h2>
            <input type="text" id="cerca_username" placeholder="Inserisci username" style="margin-bottom: 15px;">
            <div id="risultati-ricerca"></div>
        </div>
    </div>
    
    <script>
        const ruoloCorrente = '<?php echo addslashes($ruolo_nome); ?>';

        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            if (tabName === 'oggi') loadEsercizioOggi();
            if (tabName === 'progressi') loadProgressi();
            if (tabName === 'amici') loadAmici();
            if (tabName === 'classifica') loadClassifiche();
        }
        
        function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
        function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
        
        function submitQuiz(e) {
            e.preventDefault();
            const data = {
                eta: document.getElementById('quiz_eta').value,
                livello_fitness: document.getElementById('quiz_livello').value,
                obiettivo: document.getElementById('quiz_obiettivo').value,
                orario_notifica: document.getElementById('quiz_ora').value
            };
            
            fetch('api/quiz.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    alert('✅ Piano creato! Ricarico la pagina...');
                    location.reload();
                } else alert('❌ Errore: ' + (d.error || 'sconosciuto'));
            });
        }
        
        function loadEsercizioOggi() {
            fetch('api/esercizi.php?action=oggi')
                .then(r => r.json())
                .then(d => {
                    if (d.success && d.esercizio) {
                        const html = `
                            <div class="esercizio-card">
                                <div class="nome">${d.esercizio.nome_esercizio}</div>
                                <div class="info">📝 ${d.esercizio.descrizione}</div>
                                <div class="info">🎯 ${d.esercizio.ripetizioni} ripetizioni × ${d.esercizio.serie} serie</div>
                                <div class="info">📈 Difficoltà: ${d.esercizio.difficolta_moltiplicatore.toFixed(2)}x</div>
                                <div class="info">📅 Giorno ${d.giorno_piano} / 28</div>
                            </div>
                            ${d.gia_completato ? '<span class="badge completato">✅ Completato oggi</span>' : `<button class="btn" onclick="prepareCompleteExercise(${d.esercizio.id}, '${d.esercizio.ripetizioni}', '${d.esercizio.serie}')">Completa Esercizio</button>`}
                        `;
                        document.getElementById('esercizio-oggi-container').innerHTML = html;
                    } else {
                        document.getElementById('esercizio-oggi-container').innerHTML = '<p>Nessun esercizio disponibile. Completa il quiz!</p>';
                    }
                });
        }
        
        function prepareCompleteExercise(id, rip, serie) {
            document.getElementById('esercizio_id').value = id;
            document.getElementById('ripetizioni_fatte').value = rip;
            document.getElementById('serie_fatte').value = serie;
            openModal('completaEsercizioModal');
        }
        
        function completeExercise(e) {
            e.preventDefault();
            const data = {
                esercizio_id: document.getElementById('esercizio_id').value,
                ripetizioni: document.getElementById('ripetizioni_fatte').value,
                serie: document.getElementById('serie_fatte').value,
                feedback: document.getElementById('feedback_esercizio').value,
                difficolta: 1.0
            };
            
            fetch('api/esercizi.php?action=completa', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    alert('✅ Esercizio completato!');
                    closeModal('completaEsercizioModal');
                    loadEsercizioOggi();
                }
            });
        }
        
        function loadProgressi() {
            fetch('api/progressi.php?action=statistiche')
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        let html = '';
                        d.stats.forEach(s => {
                            html += `<div style="padding:12px;background:#f5f5f5;margin:8px 0;border-radius:5px;">
                                <strong>${s.nome_esercizio}</strong><br>
                                Completato ${s.volte_completato}× | ${s.ripetizioni_totali} tot ripetizioni | Difficoltà: ${s.difficolta_media.toFixed(2)}x
                            </div>`;
                        });
                        document.getElementById('stats-container').innerHTML = html || '<p>Nessun dato ancora</p>';
                    }
                });
        }
        
        function loadAmici() {
            fetch('api/amicizie.php?action=classifica_amici')
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        let html = '';
                        d.amici.forEach((a, i) => {
                            html += `<li><strong>#${i+1} ${a.nome} ${a.cognome}</strong> <span>${a.ripetizioni_totali || 0} reps</span></li>`;
                        });
                        document.getElementById('amici-list').innerHTML = html || '<li>Nessun amico</li>';
                    }
                });
        }
        
        function loadClassifiche() {
            fetch('api/amicizie.php?action=classifica_amici')
                .then(r => r.json())
                .then(d => {
                    let html = '';
                    d.amici.forEach((a, i) => {
                        html += `<li><strong>#${i+1} ${a.nome}</strong> <span>${a.ripetizioni_totali || 0} reps</span></li>`;
                    });
                    document.getElementById('classifica-amici').innerHTML = html;
                });
            
            fetch('api/amicizie.php?action=classifica_mondiale')
                .then(r => r.json())
                .then(d => {
                    let html = '';
                    d.utenti.forEach((u, i) => {
                        html += `<li><strong>#${i+1} ${u.nome}</strong> <span>${u.ripetizioni_totali || 0} reps</span></li>`;
                    });
                    document.getElementById('classifica-mondiale').innerHTML = html;
                });
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            if (ruoloCorrente !== 'utente') return;

            fetch('api/progressi.php?action=totali')
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        const t = d.totali;
                        const giorniEl = document.getElementById('giorni_allenamento');
                        const repsEl = document.getElementById('ripetizioni_totali');
                        const diffEl = document.getElementById('difficolta_media');

                        if (giorniEl) giorniEl.textContent = t.giorni_allenamento || 0;
                        if (repsEl) repsEl.textContent = t.ripetizioni_totali || 0;
                        if (diffEl) diffEl.textContent = (t.difficolta_media || 1).toFixed(1) + 'x';
                    }
                });
        });
    </script>
</body>
</html>
