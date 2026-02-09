<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$plan = $conn->query("SELECT * FROM workout_plans WHERE user_id=$user_id AND status='attivo' LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Workout - Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { color: #667eea; }
        .user-info { display: flex; gap: 15px; align-items: center; }
        .user-info button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card h2 { color: #667eea; margin-bottom: 15px; }
        .card p { margin: 10px 0; line-height: 1.6; }
        .btn { padding: 12px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        .btn:hover { background: #764ba2; }
        .progress-bar { height: 10px; background: #e0e0e0; border-radius: 5px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); width: 0%; transition: width 0.3s; }
        .leaderboard { list-style: none; }
        .leaderboard li { padding: 10px; background: #f5f5f5; margin: 5px 0; border-radius: 5px; display: flex; justify-content: space-between; }
        .stats-grid { display: grid; gap: 10px; }
        .stat-item { padding: 15px; background: #f0f4ff; border-left: 4px solid #667eea; border-radius: 5px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; }
        .modal-content h3 { margin-bottom: 15px; color: #667eea; }
        .modal-content input, .modal-content select, .modal-content textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #e0e0e0; cursor: pointer; border-radius: 5px; }
        .tab.active { background: #667eea; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ Home Workout</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($user['username']); ?></span>
                <button onclick="showProfile()">Profilo</button>
                <button onclick="logout()">Esci</button>
            </div>
        </header>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('home')">Home</div>
            <div class="tab" onclick="switchTab('esercizi')">Oggi</div>
            <div class="tab" onclick="switchTab('progressi')">Progressi</div>
            <div class="tab" onclick="switchTab('amici')">Amici</div>
            <div class="tab" onclick="switchTab('classifica')">Classifica</div>
        </div>

        <!-- TAB: HOME -->
        <div id="home" class="tab-content active">
            <div class="grid">
                <div class="card">
                    <h2>📋 Piano Attuale</h2>
                    <?php if ($plan): ?>
                        <p><strong>Difficoltà:</strong> <?php echo ucfirst($plan['difficulty']); ?></p>
                        <p><strong>Inizio:</strong> <?php echo $plan['start_date']; ?></p>
                        <p><strong>Fine:</strong> <?php echo $plan['end_date']; ?></p>
                        <p><strong>Giorni rimanenti:</strong> <span id="days_left">-</span></p>
                        <div class="progress-bar">
                            <div class="progress-fill" id="plan_progress" style="width: 50%;"></div>
                        </div>
                    <?php else: ?>
                        <p>Nessun piano attivo. Completa il quiz per iniziare!</p>
                        <button class="btn" onclick="showQuiz()">Inizia Quiz</button>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2>🎯 Obiettivo</h2>
                    <p><?php echo htmlspecialchars($user['goal'] ?? 'Non impostato'); ?></p>
                    <p><strong>Livello:</strong> <?php echo ucfirst($user['fitness_level']); ?></p>
                    <p><strong>Orario allenamento:</strong> <?php echo $user['notification_time']; ?></p>
                </div>

                <div class="card">
                    <h2>📊 Riassunto</h2>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong>Allenamenti completati</strong><br>
                            <span id="total_workouts">0</span>
                        </div>
                        <div class="stat-item">
                            <strong>Ripetizioni totali</strong><br>
                            <span id="total_reps">0</span>
                        </div>
                        <div class="stat-item">
                            <strong>Piani completati</strong><br>
                            <span><?php echo $user['plan_completed_count']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: ESERCIZI DI OGGI -->
        <div id="esercizi" class="tab-content">
            <div class="card">
                <h2>💪 Esercizio di Oggi</h2>
                <div id="today_exercise">Caricamento...</div>
                <div id="exercise_form" style="display:none; margin-top: 20px;">
                    <input type="hidden" id="exercise_id">
                    <label>Ripetizioni eseguite:</label>
                    <input type="number" id="reps_done" placeholder="0">
                    <label>Serie eseguite:</label>
                    <input type="number" id="sets_done" placeholder="0">
                    <label>Feedback:</label>
                    <textarea id="feedback" placeholder="Come è andato?"></textarea>
                    <button class="btn" onclick="completeExercise()">Completa Esercizio</button>
                </div>
            </div>
        </div>

        <!-- TAB: PROGRESSI -->
        <div id="progressi" class="tab-content">
            <div class="grid">
                <div class="card">
                    <h2>📈 Progressi Giornalieri</h2>
                    <canvas id="progressChart"></canvas>
                </div>
                <div class="card">
                    <h2>💪 Statistiche Esercizi</h2>
                    <div id="exercise_stats"></div>
                </div>
            </div>
        </div>

        <!-- TAB: AMICI -->
        <div id="amici" class="tab-content">
            <div class="card">
                <h2>👥 I Tuoi Amici</h2>
                <ul class="leaderboard" id="friends_list">
                    <li>Caricamento...</li>
                </ul>
                <button class="btn" onclick="searchFriends()">Aggiungi Amico</button>
            </div>
        </div>

        <!-- TAB: CLASSIFICA -->
        <div id="classifica" class="tab-content">
            <div class="grid">
                <div class="card">
                    <h2>🏆 Classifica Amici</h2>
                    <ol class="leaderboard" id="friends_leaderboard"></ol>
                </div>
                <div class="card">
                    <h2>🌍 Classifica Mondiale</h2>
                    <ol class="leaderboard" id="global_leaderboard"></ol>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL QUIZ -->
    <div id="quizModal" class="modal">
        <div class="modal-content">
            <h3>Rispondi al Quiz</h3>
            <input type="number" id="quiz_age" placeholder="Età" min="18" max="100">
            <select id="quiz_level">
                <option value="principiante">Principiante</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzato">Avanzato</option>
            </select>
            <input type="text" id="quiz_goal" placeholder="Obiettivo (es: dimagrire, tonificare)">
            <label>Orario allenamento:</label>
            <input type="time" id="quiz_time" value="08:00">
            <button class="btn" onclick="submitQuiz()">Inizia Allenamento</button>
            <button class="btn" style="background: #999;" onclick="closeModal('quizModal')">Annulla</button>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            if (tabName === 'progressi') loadProgressData();
            if (tabName === 'amici') loadFriends();
            if (tabName === 'classifica') loadLeaderboards();
        }

        function showQuiz() {
            document.getElementById('quizModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function submitQuiz() {
            const data = {
                age: document.getElementById('quiz_age').value,
                fitness_level: document.getElementById('quiz_level').value,
                goal: document.getElementById('quiz_goal').value,
                notification_time: document.getElementById('quiz_time').value
            };
            
            fetch('api/quiz.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    alert('Piano creato! Ricarichiamo la pagina...');
                    location.reload();
                }
            });
        }

        function loadTodayExercise() {
            fetch('api/exercises.php?action=today')
                .then(r => r.json())
                .then(data => {
                    if (data && data.id) {
                        document.getElementById('today_exercise').innerHTML = `
                            <p><strong>${data.exercise_name}</strong></p>
                            <p>${data.description}</p>
                            <p>Ripetizioni: ${data.reps} | Serie: ${data.sets}</p>
                            <p>Difficoltà: ${data.difficulty_increase.toFixed(1)}x</p>
                        `;
                        document.getElementById('exercise_id').value = data.id;
                        document.getElementById('exercise_form').style.display = 'block';
                    }
                });
        }

        function completeExercise() {
            const data = {
                exercise_id: document.getElementById('exercise_id').value,
                reps_done: document.getElementById('reps_done').value,
                sets_done: document.getElementById('sets_done').value,
                feedback: document.getElementById('feedback').value
            };
            
            fetch('api/exercises.php?action=complete', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).then(r => r.json()).then(d => {
                if (d.success) alert('Esercizio completato!');
            });
        }

        function loadProgressData() {
            fetch('api/stats.php?action=daily_progress')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('total_workouts').textContent = data.length;
                    document.getElementById('total_reps').textContent = data.reduce((s, d) => s + (d.total_reps || 0), 0);
                });
                
            fetch('api/stats.php?action=exercise_stats')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    data.forEach(ex => {
                        html += `<div class="stat-item"><strong>${ex.exercise_name}</strong><br>Completato ${ex.times_completed}x | ${ex.total_reps} ripetizioni</div>`;
                    });
                    document.getElementById('exercise_stats').innerHTML = html;
                });
        }

        function loadFriends() {
            fetch('api/friends.php?action=leaderboard_friends')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    data.forEach((f, i) => {
                        html += `<li><strong>#${i+1} ${f.username}</strong> <span>${f.total_reps || 0} reps</span></li>`;
                    });
                    document.getElementById('friends_list').innerHTML = html || '<li>Nessun amico ancora</li>';
                });
        }

        function loadLeaderboards() {
            fetch('api/friends.php?action=leaderboard_friends')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    data.forEach((f, i) => {
                        html += `<li><strong>#${i+1} ${f.username}</strong> <span>${f.total_reps || 0} reps</span></li>`;
                    });
                    document.getElementById('friends_leaderboard').innerHTML = html;
                });
            
            fetch('api/friends.php?action=leaderboard_global')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    data.forEach((u, i) => {
                        html += `<li><strong>#${i+1} ${u.username}</strong> <span>${u.total_reps || 0} reps</span></li>`;
                    });
                    document.getElementById('global_leaderboard').innerHTML = html;
                });
        }

        function logout() {
            if (confirm('Sei sicuro?')) {
                window.location.href = 'logout.php';
            }
        }

        // Carica all'apertura
        document.addEventListener('DOMContentLoaded', loadTodayExercise);
    </script>
</body>
</html>
