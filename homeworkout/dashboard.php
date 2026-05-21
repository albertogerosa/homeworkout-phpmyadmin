<?php
session_start();
require_once 'database.php';
require_once __DIR__ . '/JWT/jwt_helper.php';
require_once __DIR__ . '/tenant_helper.php';

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit;
}

$utente_id = $_SESSION['utente_id'];
$ruolo_id = 0;
$ruolo_nome = 'utente';
$tenant_id = homeworkoutCurrentTenantId();
$is_super_admin = false;

function homeworkoutBuildSevenDaySeries(array $rows): array {
    $series = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime('-' . $i . ' day'));
        $series[$date] = [
            'giorno' => $date,
            'sessioni' => 0,
            'ripetizioni_totali' => 0,
        ];
    }

    foreach ($rows as $row) {
        $giorno = $row['giorno'] ?? null;
        if ($giorno && isset($series[$giorno])) {
            $series[$giorno]['sessioni'] = (int)($row['sessioni'] ?? 0);
            $series[$giorno]['ripetizioni_totali'] = (int)($row['ripetizioni_totali'] ?? 0);
        }
    }

    return array_values($series);
}

function homeworkoutWorkoutStreak(array $dates): int {
    $lookup = [];
    foreach ($dates as $date) {
        $lookup[$date] = true;
    }

    $streak = 0;
    
    $cursor = date('Y-m-d');
    while (isset($lookup[$cursor])) {
        $streak++;
        $cursor = date('Y-m-d', strtotime($cursor . ' -1 day'));
    }

    return $streak;
}

$token = $_SESSION['access_token'] ?? '';
if (!empty($token)) {
    $tokenData = validateJWT($token);
    if ($tokenData && !empty($tokenData['role_id'])) {
        $ruolo_id = (int)$tokenData['role_id'];
        $is_super_admin = homeworkoutIsSuperAdmin($ruolo_id, $_SESSION['ruolo_nome'] ?? null);
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
    $is_super_admin = homeworkoutIsSuperAdmin($ruolo_id, $ruolo_nome);

    $_SESSION['ruolo_id'] = $ruolo_id;
    $_SESSION['ruolo_nome'] = $ruolo_nome;
    if (!$is_super_admin && $tenant_id === null) {
        $stmtTenant = $pdo->prepare("SELECT tenant_id FROM utenti WHERE id = :uid LIMIT 1");
        $stmtTenant->execute(['uid' => $utente_id]);
        $tenantValue = $stmtTenant->fetchColumn();
        $tenant_id = $tenantValue !== false ? (int)$tenantValue : null;
    }
    if ($tenant_id !== null) {
        $_SESSION['tenant_id'] = $tenant_id;
    }

    $sql_quiz = "SELECT * FROM quiz_risposte WHERE utente_id = :utente_id AND tenant_id = :tenant_id LIMIT 1";
    $stmt_quiz = $pdo->prepare($sql_quiz);
    $stmt_quiz->execute(['utente_id' => $utente_id, 'tenant_id' => $tenant_id]);
    $quiz_completato = $stmt_quiz->fetch();
    
    $sql_piano = "SELECT * FROM piani_allenamento WHERE utente_id = :utente_id AND tenant_id = :tenant_id AND stato = 'attivo' ORDER BY id DESC LIMIT 1";
    $stmt_piano = $pdo->prepare($sql_piano);
    $stmt_piano->execute(['utente_id' => $utente_id, 'tenant_id' => $tenant_id]);
    $piano_attivo = $stmt_piano->fetch();

    $weekly_summary = homeworkoutBuildSevenDaySeries([]);
    $recent_workouts = [];
    $plan_exercises = [];
    $today_exercise_preview = null;
    $training_streak = 0;
    $weekly_sessions = 0;
    $weekly_reps = 0;
    $plan_days_total = 0;
    $plan_days_elapsed = 0;
    $plan_days_remaining = 0;
    $plan_completion_percent = 0;
    $tenant_users = [];
    $tenant_plans = [];
    $tenant_friend_requests = [];
    $tenant_stats = [
        'utenti' => 0,
        'amministratori' => 0,
        'piani_attivi' => 0,
        'allenamenti_7g' => 0,
        'richieste_amicizia' => 0,
    ];

    if ($tenant_id !== null) {
        $stmtRecent = $pdo->prepare("SELECT p.data_allenamento, p.ripetizioni_fatte, p.serie_fatte, p.feedback, e.nome_esercizio, e.descrizione
            FROM progressi_dettaglio p
            LEFT JOIN esercizi_piano e ON e.id = p.esercizio_id AND e.tenant_id = p.tenant_id
            WHERE p.utente_id = :utente_id AND p.tenant_id = :tenant_id
            ORDER BY p.data_allenamento DESC, p.id DESC
            LIMIT 5");
        $stmtRecent->execute(['utente_id' => $utente_id, 'tenant_id' => $tenant_id]);
        $recent_workouts = $stmtRecent->fetchAll();

        $stmtWeekly = $pdo->prepare("SELECT DATE(data_allenamento) AS giorno, COUNT(*) AS sessioni, COALESCE(SUM(ripetizioni_fatte), 0) AS ripetizioni_totali
            FROM progressi_dettaglio
            WHERE utente_id = :utente_id AND tenant_id = :tenant_id AND data_allenamento >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(data_allenamento)
            ORDER BY giorno ASC");
        $stmtWeekly->execute(['utente_id' => $utente_id, 'tenant_id' => $tenant_id]);
        $weekly_summary = homeworkoutBuildSevenDaySeries($stmtWeekly->fetchAll());

        $stmtDates = $pdo->prepare("SELECT DISTINCT DATE(data_allenamento) AS giorno
            FROM progressi_dettaglio
            WHERE utente_id = :utente_id AND tenant_id = :tenant_id
            ORDER BY giorno DESC
            LIMIT 30");
        $stmtDates->execute(['utente_id' => $utente_id, 'tenant_id' => $tenant_id]);
        $workoutDates = [];
        while ($row = $stmtDates->fetch()) {
            $workoutDates[] = $row['giorno'];
        }
        $training_streak = homeworkoutWorkoutStreak($workoutDates);

        $stmtStats = $pdo->prepare("SELECT ur.ruolo_id, COUNT(*) AS totale
            FROM utenti u
            LEFT JOIN utente_ruolo ur ON ur.utente_id = u.id
            WHERE u.tenant_id = :tenant_id
            GROUP BY ur.ruolo_id");
        $stmtStats->execute(['tenant_id' => $tenant_id]);
        while ($row = $stmtStats->fetch()) {
            if ((int)($row['ruolo_id'] ?? 1) === 1) $tenant_stats['utenti'] = (int)$row['totale'];
            if ((int)($row['ruolo_id'] ?? 0) === 3) $tenant_stats['amministratori'] = (int)$row['totale'];
        }

        $stmtActivePlans = $pdo->prepare("SELECT COUNT(*) FROM piani_allenamento WHERE tenant_id = :tenant_id AND stato = 'attivo'");
        $stmtActivePlans->execute(['tenant_id' => $tenant_id]);
        $tenant_stats['piani_attivi'] = (int)($stmtActivePlans->fetchColumn() ?: 0);

        $stmtRecentWorkoutsCount = $pdo->prepare("SELECT COUNT(*) FROM progressi_dettaglio WHERE tenant_id = :tenant_id AND data_allenamento >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmtRecentWorkoutsCount->execute(['tenant_id' => $tenant_id]);
        $tenant_stats['allenamenti_7g'] = (int)($stmtRecentWorkoutsCount->fetchColumn() ?: 0);

        $stmtFriendRequests = $pdo->prepare("SELECT COUNT(*) FROM amicizie WHERE tenant_id = :tenant_id AND stato = 'pending'");
        $stmtFriendRequests->execute(['tenant_id' => $tenant_id]);
        $tenant_stats['richieste_amicizia'] = (int)($stmtFriendRequests->fetchColumn() ?: 0);

        $stmtTenantUsers = $pdo->prepare("SELECT u.id, u.nome, u.cognome, u.email, u.livello, u.creato_il, COALESCE(r.nome_ruolo, 'utente') AS ruolo_nome,
                (
                    SELECT COUNT(*)
                    FROM piani_allenamento p
                    WHERE p.utente_id = u.id AND p.tenant_id = u.tenant_id
                ) AS piani_totali,
                (
                    SELECT MAX(p.data_allenamento)
                    FROM progressi_dettaglio p
                    WHERE p.utente_id = u.id AND p.tenant_id = u.tenant_id
                ) AS ultimo_allenamento
            FROM utenti u
            LEFT JOIN utente_ruolo ur ON ur.utente_id = u.id
            LEFT JOIN ruoli r ON r.id = ur.ruolo_id
            WHERE u.tenant_id = :tenant_id
            ORDER BY u.id DESC
            LIMIT 20");
        $stmtTenantUsers->execute(['tenant_id' => $tenant_id]);
        $tenant_users = $stmtTenantUsers->fetchAll();

        $stmtTenantPlans = $pdo->prepare("SELECT p.id, p.utente_id, p.data_inizio, p.data_fine, p.difficolta, p.stato, u.nome, u.cognome
            FROM piani_allenamento p
            LEFT JOIN utenti u ON u.id = p.utente_id
            WHERE p.tenant_id = :tenant_id
            ORDER BY p.id DESC
            LIMIT 10");
        $stmtTenantPlans->execute(['tenant_id' => $tenant_id]);
        $tenant_plans = $stmtTenantPlans->fetchAll();

        $stmtTenantFriendRequests = $pdo->prepare("SELECT a.id, a.stato, u1.nome AS nome_richiedente, u1.cognome AS cognome_richiedente, u2.nome AS nome_destinatario, u2.cognome AS cognome_destinatario
            FROM amicizie a
            LEFT JOIN utenti u1 ON u1.id = a.utente_id
            LEFT JOIN utenti u2 ON u2.id = a.amico_id
            WHERE a.tenant_id = :tenant_id
            ORDER BY a.id DESC
            LIMIT 10");
        $stmtTenantFriendRequests->execute(['tenant_id' => $tenant_id]);
        $tenant_friend_requests = $stmtTenantFriendRequests->fetchAll();
    }

    if ($piano_attivo) {
        $stmtPlanExercises = $pdo->prepare("SELECT id, nome_esercizio, descrizione, ripetizioni, serie, giorno, difficolta_moltiplicatore
            FROM esercizi_piano
            WHERE piano_id = :piano_id AND tenant_id = :tenant_id
            ORDER BY giorno ASC
            LIMIT 7");
        $stmtPlanExercises->execute(['piano_id' => $piano_attivo['id'], 'tenant_id' => $tenant_id]);
        $plan_exercises = $stmtPlanExercises->fetchAll();

        $planDaysTotalCalc = (int)floor((strtotime($piano_attivo['data_fine']) - strtotime($piano_attivo['data_inizio'])) / 86400) + 1;
        $plan_days_total = max(1, $planDaysTotalCalc);
        $plan_days_elapsed = max(1, min($plan_days_total, (int)floor((time() - strtotime($piano_attivo['data_inizio'])) / 86400) + 1));
        $plan_days_remaining = max(0, $plan_days_total - $plan_days_elapsed);
        $plan_completion_percent = (int)round(($plan_days_elapsed / $plan_days_total) * 100);

        $giorno_oggi = max(1, min(28, (int)floor((time() - strtotime($piano_attivo['data_inizio'])) / 86400) + 1));
        $stmtTodayExercise = $pdo->prepare("SELECT id, nome_esercizio, descrizione, ripetizioni, serie, giorno, difficolta_moltiplicatore
            FROM esercizi_piano
            WHERE piano_id = :piano_id AND tenant_id = :tenant_id AND giorno = :giorno
            LIMIT 1");
        $stmtTodayExercise->execute(['piano_id' => $piano_attivo['id'], 'tenant_id' => $tenant_id, 'giorno' => $giorno_oggi]);
        $today_exercise_preview = $stmtTodayExercise->fetch() ?: null;
    }

    $max_weekly_reps = 0;
    $weekly_sessions = 0;
    $weekly_reps = 0;
    foreach ($weekly_summary as $daySummary) {
        $sessionCount = (int)($daySummary['sessioni'] ?? 0);
        $repsCount = (int)($daySummary['ripetizioni_totali'] ?? 0);
        $max_weekly_reps = max($max_weekly_reps, $repsCount);
        $weekly_sessions += $sessionCount;
        $weekly_reps += $repsCount;
    }

    $stats_admin = [
        'utenti' => 0,
        'amministratori' => 0
    ];

    if ($ruolo_nome === 'amministratore' || $ruolo_nome === 'super_admin') {
        $stmtCount = $pdo->prepare("SELECT ur.ruolo_id, COUNT(*) AS totale FROM utente_ruolo ur INNER JOIN utenti u ON u.id = ur.utente_id WHERE u.tenant_id = :tenant_id GROUP BY ur.ruolo_id");
        $stmtCount->execute(['tenant_id' => $tenant_id]);
        while ($row = $stmtCount->fetch()) {
            if ((int)$row['ruolo_id'] === 1) $stats_admin['utenti'] = (int)$row['totale'];
            if ((int)$row['ruolo_id'] === 3) $stats_admin['amministratori'] = (int)$row['totale'];
        }
    }

    $tenant_nome = null;
    if ($tenant_id !== null) {
        $stmtTenantName = $pdo->prepare("SELECT nome FROM tenants WHERE id = :tenant_id LIMIT 1");
        $stmtTenantName->execute(['tenant_id' => $tenant_id]);
        $tenant_nome = $stmtTenantName->fetchColumn() ?: null;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action']) && ($ruolo_nome === 'amministratore' || $ruolo_nome === 'super_admin')) {
        $adminAction = $_POST['admin_action'];

        if ($adminAction === 'change_role') {
            $targetUserId = (int)($_POST['target_user_id'] ?? 0);
            $targetRoleId = (int)($_POST['target_role_id'] ?? 1);
            if ($targetUserId > 0 && $targetRoleId >= 1 && $targetRoleId <= 4) {
                $stmtRoleUpdate = $pdo->prepare("INSERT INTO utente_ruolo (utente_id, ruolo_id) VALUES (:utente_id, :ruolo_id)
                    ON DUPLICATE KEY UPDATE ruolo_id = VALUES(ruolo_id)");
                $stmtRoleUpdate->execute(['utente_id' => $targetUserId, 'ruolo_id' => $targetRoleId]);
                header('Location: dashboard.php?admin=1');
                exit;
            }
        }

        if ($adminAction === 'assign_tenant') {
            $targetUserId = (int)($_POST['target_user_id'] ?? 0);
            $targetTenantId = (int)($_POST['target_tenant_id'] ?? 0);
            if ($targetUserId > 0 && $targetTenantId > 0) {
                $stmtTenantUpdate = $pdo->prepare("UPDATE utenti SET tenant_id = :tenant_id WHERE id = :user_id");
                $stmtTenantUpdate->execute(['tenant_id' => $targetTenantId, 'user_id' => $targetUserId]);
                header('Location: dashboard.php?admin=1');
                exit;
            }
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
        .hero-panel { background: linear-gradient(135deg, #101828 0%, #1f3b73 48%, #7c3aed 100%); color: white; overflow: hidden; }
        .hero-panel h2 { color: white; border-bottom: 0; padding-bottom: 0; }
        .hero-panel p { color: rgba(255,255,255,0.86); }
        .hero-top { display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; flex-wrap: wrap; }
        .hero-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-top: 18px; }
        .hero-stat { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.16); padding: 16px; border-radius: 14px; }
        .hero-stat .label { font-size: 0.82em; opacity: 0.8; margin-bottom: 6px; }
        .hero-stat .numero { font-size: 1.8em; font-weight: 700; }
        .quick-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .quick-actions .btn { margin-top: 0; }
        .quick-actions .btn-secondary { background: rgba(255,255,255,0.16); color: white; border: 1px solid rgba(255,255,255,0.2); }
        .quick-actions .btn-secondary:hover { background: rgba(255,255,255,0.24); }
        .plan-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; }
        .plan-card, .timeline-item { background: #f7f7fb; border: 1px solid #ececf4; border-radius: 14px; padding: 14px; }
        .plan-card strong, .timeline-item strong { color: #222; display: block; margin: 6px 0 4px; }
        .plan-card small, .timeline-item small { color: #666; }
        .timeline { display: grid; gap: 10px; }
        .timeline-item { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; }
        .timeline-item .meta { min-width: 110px; text-align: right; color: #666; font-size: 0.92em; }
        .weekly-bars { display: grid; gap: 12px; }
        .weekly-bar-row { display: grid; grid-template-columns: 72px 1fr 54px; gap: 10px; align-items: center; }
        .weekly-bar-label { color: #666; font-weight: 600; }
        .empty-state { padding: 18px; border: 1px dashed #d8d8e5; border-radius: 14px; background: #fafafe; color: #666; }
        .admin-shell { display: grid; gap: 18px; }
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 10px 8px; border-bottom: 1px solid #ececf4; text-align: left; vertical-align: top; }
        .admin-table th { color: #555; font-size: 0.9em; }
        .admin-table td small { color: #666; display: block; }
        .inline-form { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .inline-form select, .inline-form input { width: auto; min-width: 140px; }
    </style>
</head>
<body>
    <header>
        <div class="header-flex">
            <h1>🏋️ HomeWorkout</h1>
            <div class="user-section">
                <span><strong><?php echo htmlspecialchars(trim(($_SESSION['nome'] ?? '') . ' ' . ($_SESSION['cognome'] ?? ''))); ?></strong></span>
                <span class="badge"><?php echo htmlspecialchars(ucfirst($ruolo_nome)); ?> (<?php echo (int)$ruolo_id; ?>)</span>
                <?php if ($tenant_nome): ?>
                    <span class="badge">Tenant: <?php echo htmlspecialchars($tenant_nome); ?></span>
                <?php endif; ?>
                <a href="logout.php" class="logout-btn">Esci</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="tabs">
            <button class="tab active" onclick="switchTab(event, 'home')">🏠 Home</button>
            <?php if ($ruolo_nome === 'utente'): ?>
                <button class="tab" onclick="switchTab(event, 'oggi')">💪 Oggi</button>
                <button class="tab" onclick="switchTab(event, 'progressi')">📊 Progressi</button>
                <button class="tab" onclick="switchTab(event, 'amici')">👥 Amici</button>
                <button class="tab" onclick="switchTab(event, 'classifica')">🏆 Classifica</button>
            <?php elseif ($ruolo_nome === 'amministratore'): ?>
                <button class="tab" onclick="switchTab(event, 'admin')">🛠️ Admin</button>
            <?php elseif ($ruolo_nome === 'super_admin'): ?>
                <button class="tab" onclick="switchTab(event, 'superadmin')">🏢 Palestre</button>
            <?php endif; ?>
        </div>
        
        <!-- TAB: HOME -->
        <div id="home" class="tab-content active">
            <?php if ($ruolo_nome === 'utente'): ?>
                <div class="card">
                    <h2>Benvenuto, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
                    <p style="margin-bottom: 10px;"><span class="badge attivo">Dashboard utente interattiva</span></p>
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

                <div class="card hero-panel">
                    <div class="hero-top">
                        <div>
                            <span class="badge completato">Allenamento da casa</span>
                            <h2 style="margin-top: 12px;">Il tuo percorso di oggi</h2>
                            <?php if ($piano_attivo && $today_exercise_preview): ?>
                                <p>Hai un piano attivo: riparti dal giorno <?php echo (int)$today_exercise_preview['giorno']; ?> e tieni la continuità con <?php echo (int)$training_streak; ?> giorni di fila.</p>
                            <?php elseif ($piano_attivo): ?>
                                <p>Il piano è attivo, ma l'esercizio di oggi non è ancora disponibile.</p>
                            <?php else: ?>
                                <p>Completa il quiz per generare una scheda allenamento su misura e iniziare subito.</p>
                            <?php endif; ?>
                        </div>
                        <div style="min-width: 220px;">
                            <div class="hero-stat">
                                <div class="label">Streak attuale</div>
                                <div class="numero"><?php echo (int)$training_streak; ?> giorni</div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="label">Sessioni ultime 7 giorni</div>
                            <div class="numero"><?php echo (int)$weekly_sessions; ?></div>
                        </div>
                        <div class="hero-stat">
                            <div class="label">Ripetizioni ultime 7 giorni</div>
                            <div class="numero"><?php echo (int)$weekly_reps; ?></div>
                        </div>
                        <div class="hero-stat">
                            <div class="label">Giorni rimanenti piano</div>
                            <div class="numero"><?php echo (int)$plan_days_remaining; ?></div>
                        </div>
                        <div class="hero-stat">
                            <div class="label">Completamento piano</div>
                            <div class="numero"><?php echo (int)$plan_completion_percent; ?>%</div>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <button class="btn" onclick="switchTab(event, 'oggi')">Avvia workout</button>
                        <button class="btn btn-secondary" onclick="switchTab(event, 'progressi')">Apri progressi</button>
                        <button class="btn btn-secondary" onclick="switchTab(event, 'amici')">Gestisci amici</button>
                        <a href="esercizi.php" class="btn btn-secondary" style="text-decoration:none; display:inline-block;">Catalogo esercizi</a>
                    </div>

                    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                        <button class="btn" onclick="openModal('impostazioniAllenamentoModal')">🕒 Impostazioni orario / notifiche</button>
                        <button class="btn btn-secondary" onclick="openModal('riposoModal')">😴 Imposta periodo di riposo</button>
                        <button class="btn btn-secondary" onclick="openModal('feedbackFinaleModal')">✍️ Feedback finale piano</button>
                        <button class="btn" onclick="openModal('creaPianoModal')">➕ Crea nuovo piano</button>
                    </div>
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
                    <h2>🗓️ Programma della settimana</h2>
                    <?php if ($plan_exercises): ?>
                        <div class="plan-grid">
                            <?php foreach ($plan_exercises as $exercise): ?>
                                <div class="plan-card">
                                    <span class="badge">Giorno <?php echo (int)$exercise['giorno']; ?></span>
                                    <strong><?php echo htmlspecialchars($exercise['nome_esercizio']); ?></strong>
                                    <small><?php echo htmlspecialchars($exercise['descrizione']); ?></small>
                                    <div style="margin-top: 10px; color: #444;">
                                        <?php echo (int)$exercise['ripetizioni']; ?> ripetizioni × <?php echo (int)$exercise['serie']; ?> serie
                                    </div>
                                    <div style="margin-top: 6px; color: #666; font-size: 0.9em;">
                                        Intensità <?php echo number_format((float)$exercise['difficolta_moltiplicatore'], 2); ?>x
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            Nessun programma generato ancora. Completa il quiz per costruire la tua scheda personalizzata.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2>🕒 Attività recente</h2>
                    <?php if ($recent_workouts): ?>
                        <div class="timeline">
                            <?php foreach ($recent_workouts as $workout): ?>
                                <div class="timeline-item">
                                    <div>
                                        <strong><?php echo htmlspecialchars($workout['nome_esercizio'] ?: 'Allenamento'); ?></strong>
                                        <small><?php echo htmlspecialchars($workout['descrizione'] ?: 'Sessione completata'); ?></small>
                                    </div>
                                    <div class="meta">
                                        <?php echo htmlspecialchars(date('d/m/Y', strtotime($workout['data_allenamento']))); ?><br>
                                        <?php echo (int)$workout['ripetizioni_fatte']; ?> reps · <?php echo (int)$workout['serie_fatte']; ?> serie
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            Nessuna sessione registrata ancora. Avvia il primo workout dalla scheda di oggi.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2>📈 Andamento ultimi 7 giorni</h2>
                    <div class="weekly-bars">
                        <?php foreach ($weekly_summary as $daySummary): ?>
                            <?php
                                $reps = (int)$daySummary['ripetizioni_totali'];
                                $width = $max_weekly_reps > 0 ? max(8, (int)round(($reps / $max_weekly_reps) * 100)) : 8;
                            ?>
                            <div class="weekly-bar-row">
                                <div class="weekly-bar-label"><?php echo htmlspecialchars(date('d/m', strtotime($daySummary['giorno']))); ?></div>
                                <div class="progress-bar"><div class="progress-fill" style="width: <?php echo (int)$width; ?>%;"></div></div>
                                <strong style="text-align:right; color:#333;"><?php echo $reps; ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
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
                            <div class="numero"><?php echo (int)$stats_admin['amministratori']; ?></div>
                            <div class="label">Ruolo 3 - Amministratore</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($ruolo_nome === 'super_admin'): ?>
                <div class="card">
                    <h2>Super Admin</h2>
                    <p>Da qui puoi creare, attivare e assegnare palestre senza toccare i dati di un altro tenant.</p>
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
        
        <?php if ($ruolo_nome === 'utente'): ?>
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
        
        <?php if ($ruolo_nome === 'utente'): ?>
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
            <div class="admin-shell">
                <div class="card">
                    <h2>🛠️ Console amministrativa</h2>
                    <p>Da qui puoi vedere gli utenti del tenant, cambiare ruoli e assegnare la palestra corretta ai nuovi iscritti.</p>
                    <div class="admin-grid" style="margin-top: 15px;">
                        <div class="stat-box"><div class="numero"><?php echo (int)$tenant_stats['utenti']; ?></div><div class="label">Utenti</div></div>
                        <div class="stat-box"><div class="numero"><?php echo (int)$tenant_stats['amministratori']; ?></div><div class="label">Amministratori</div></div>
                        <div class="stat-box"><div class="numero"><?php echo (int)$tenant_stats['piani_attivi']; ?></div><div class="label">Piani attivi</div></div>
                        <div class="stat-box"><div class="numero"><?php echo (int)$tenant_stats['allenamenti_7g']; ?></div><div class="label">Allenamenti 7g</div></div>
                        <div class="stat-box"><div class="numero"><?php echo (int)$tenant_stats['richieste_amicizia']; ?></div><div class="label">Richieste amico</div></div>
                    </div>
                </div>

                <div class="card">
                    <h2>👤 Utenti del tenant</h2>
                    <?php if ($tenant_users): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Utente</th>
                                    <th>Ruolo</th>
                                    <th>Piano / Attività</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tenant_users as $user): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars(trim(($user['nome'] ?? '') . ' ' . ($user['cognome'] ?? ''))); ?></strong>
                                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                                            <small>Livello: <?php echo htmlspecialchars($user['livello'] ?? 'principiante'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['ruolo_nome']); ?></td>
                                        <td>
                                            <small>Piani: <?php echo (int)$user['piani_totali']; ?></small>
                                            <small>Ultimo allenamento: <?php echo $user['ultimo_allenamento'] ? htmlspecialchars(date('d/m/Y', strtotime($user['ultimo_allenamento']))) : 'Mai'; ?></small>
                                        </td>
                                        <td>
                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="admin_action" value="change_role">
                                                <input type="hidden" name="target_user_id" value="<?php echo (int)$user['id']; ?>">
                                                <select name="target_role_id">
                                                    <option value="1" <?php echo ($user['ruolo_nome'] === 'utente') ? 'selected' : ''; ?>>Utente</option>
                                                    <option value="3" <?php echo ($user['ruolo_nome'] === 'amministratore') ? 'selected' : ''; ?>>Amministratore</option>
                                                    <option value="4" <?php echo ($user['ruolo_nome'] === 'super_admin') ? 'selected' : ''; ?>>Super admin</option>
                                                </select>
                                                <button class="btn" type="submit">Aggiorna ruolo</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">Nessun utente trovato in questo tenant.</div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2>📅 Piani allenamento recenti</h2>
                    <?php if ($tenant_plans): ?>
                        <div class="timeline">
                            <?php foreach ($tenant_plans as $plan): ?>
                                <div class="timeline-item">
                                    <div>
                                        <strong><?php echo htmlspecialchars(trim(($plan['nome'] ?? '') . ' ' . ($plan['cognome'] ?? '')) ?: 'Utente'); ?></strong>
                                        <small>Dal <?php echo htmlspecialchars(date('d/m/Y', strtotime($plan['data_inizio']))); ?> al <?php echo htmlspecialchars(date('d/m/Y', strtotime($plan['data_fine']))); ?></small>
                                    </div>
                                    <div class="meta">
                                        <?php echo htmlspecialchars(ucfirst($plan['stato'])); ?><br>
                                        Difficoltà <?php echo (int)$plan['difficolta']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">Nessun piano recente.</div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2>🤝 Richieste amicizia recenti</h2>
                    <?php if ($tenant_friend_requests): ?>
                        <div class="timeline">
                            <?php foreach ($tenant_friend_requests as $request): ?>
                                <div class="timeline-item">
                                    <div>
                                        <strong><?php echo htmlspecialchars(trim(($request['nome_richiedente'] ?? '') . ' ' . ($request['cognome_richiedente'] ?? ''))); ?></strong>
                                        <small>verso <?php echo htmlspecialchars(trim(($request['nome_destinatario'] ?? '') . ' ' . ($request['cognome_destinatario'] ?? ''))); ?></small>
                                    </div>
                                    <div class="meta">
                                        <?php echo htmlspecialchars(ucfirst($request['stato'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">Nessuna richiesta recente.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($ruolo_nome === 'super_admin'): ?>
        <div id="superadmin" class="tab-content">
            <div class="card">
                <h2>🏢 Gestione Palestre</h2>
                <form class="quiz-form" onsubmit="createTenant(event)">
                    <div>
                        <label><strong>Nome palestra</strong></label>
                        <input type="text" id="tenant_nome" placeholder="Es. Palestra Roma Centro" required>
                    </div>
                    <div>
                        <label><strong>Slug</strong></label>
                        <input type="text" id="tenant_slug" placeholder="es. palestra-roma-centro">
                    </div>
                    <button class="btn" type="submit">➕ Crea palestra</button>
                </form>
            </div>

            <div class="card">
                <h2>Palestre registrate</h2>
                <div id="tenants-container">Caricamento...</div>
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
            <div id="feedback-amico" style="display:none; padding:12px; margin-top:15px; border-radius:5px; background:#e8f5e9; color:#2e7d32; text-align:center;"></div>
        </div>
    </div>

    <!-- MODAL: IMPOSTAZIONI ALLENAMENTO -->
    <div id="impostazioniAllenamentoModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('impostazioniAllenamentoModal')">✕</button>
            <h2>🕒 Impostazioni Allenamento</h2>
            <form onsubmit="submitTrainingSettings(event)">
                <div>
                    <label><strong>Orario notifiche</strong></label>
                    <input type="time" id="impostazione_orario" value="<?php echo htmlspecialchars($quiz_completato['orario_notifica'] ?? '08:00'); ?>" required>
                </div>
                <div>
                    <label><strong>Abilita notifiche</strong></label>
                    <select id="impostazione_notifiche">
                        <option value="1">Attive</option>
                        <option value="0">Disattive</option>
                    </select>
                </div>
                <button type="submit" class="btn">Salva impostazioni</button>
            </form>
        </div>
    </div>

    <!-- MODAL: RIPOSO -->
    <div id="riposoModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('riposoModal')">✕</button>
            <h2>😴 Periodo di Riposo</h2>
            <form onsubmit="submitRestSettings(event)">
                <div>
                    <label><strong>Giorni di riposo consigliati</strong></label>
                    <input type="number" id="riposo_giorni" min="0" max="14" value="1" required>
                </div>
                <div>
                    <small>Verrà usato per calcolare pause automatiche in base alla continuità.</small>
                </div>
                <button type="submit" class="btn">Salva riposo</button>
            </form>
        </div>
    </div>

    <!-- MODAL: FEEDBACK FINALE PIANO -->
    <div id="feedbackFinaleModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('feedbackFinaleModal')">✕</button>
            <h2>✍️ Feedback Finale</h2>
            <form onsubmit="submitFinalFeedback(event)">
                <div>
                    <label><strong>Come è andato il piano?</strong></label>
                    <textarea id="feedback_finale" rows="4" placeholder="Scrivi un feedback finale..."></textarea>
                </div>
                <button type="submit" class="btn">Invia feedback</button>
            </form>
        </div>
    </div>

    <!-- MODAL: CREA NUOVO PIANO -->
    <div id="creaPianoModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('creaPianoModal')">✕</button>
            <h2>➕ Crea Nuovo Piano</h2>
            <p>Creare un nuovo piano sostituirà il piano attivo (se presente). Confermi?</p>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:10px;">
                <button class="btn btn-secondary" onclick="closeModal('creaPianoModal')">Annulla</button>
                <button class="btn" onclick="createNewPlan()">Conferma e crea</button>
            </div>
        </div>
    </div>
    
    <script>
        const ruoloCorrente = '<?php echo addslashes($ruolo_nome); ?>';
        const pianoAttivoDataFine = '<?php echo $piano_attivo ? addslashes($piano_attivo['data_fine']) : ''; ?>';

        function slugify(value) {
            return value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        function esc(text) {
            const div = document.createElement('div');
            div.textContent = String(text ?? '');
            return div.innerHTML;
        }

        async function fetchJson(url, options = {}) {
            const response = await fetch(url, options);
            const data = await response.json();
            if (!response.ok || data.error) {
                throw new Error(data.error || 'Errore API');
            }
            return data;
        }

        function switchTab(evt, tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            if (evt && evt.currentTarget) {
                evt.currentTarget.classList.add('active');
            }
            
            if (tabName === 'oggi') loadEsercizioOggi();
            if (tabName === 'progressi') loadProgressi();
            if (tabName === 'amici') loadAmici();
            if (tabName === 'classifica') loadClassifiche();
            if (tabName === 'superadmin') loadTenants();
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
            
            fetchJson('api/quiz.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).then(d => {
                if (d.success) {
                    alert('✅ Piano creato! Ricarico la pagina...');
                    location.reload();
                } else alert('❌ Errore: ' + (d.error || 'sconosciuto'));
            }).catch(err => alert('❌ ' + err.message));
        }
        
        function loadEsercizioOggi() {
            fetchJson('api/esercizi.php?action=oggi')
                .then(d => {
                    if (d.success && d.esercizio) {
                        const html = `
                            <div class="esercizio-card">
                                <div class="nome">${esc(d.esercizio.nome_esercizio)}</div>
                                <div class="info">📝 ${esc(d.esercizio.descrizione)}</div>
                                <div class="info">🎯 ${d.esercizio.ripetizioni} ripetizioni × ${d.esercizio.serie} serie</div>
                                <div class="info">📈 Difficoltà: ${Number(d.esercizio.difficolta_moltiplicatore || 1).toFixed(2)}x</div>
                                <div class="info">📅 Giorno ${d.giorno_piano} / 28</div>
                            </div>
                            ${d.gia_completato ? '<span class="badge completato">✅ Completato oggi</span>' : `<button class="btn" onclick="prepareCompleteExercise(${d.esercizio.id}, '${d.esercizio.ripetizioni}', '${d.esercizio.serie}')">Completa Esercizio</button>`}
                        `;
                        document.getElementById('esercizio-oggi-container').innerHTML = html;
                    } else {
                        document.getElementById('esercizio-oggi-container').innerHTML = '<p>Nessun esercizio disponibile. Completa il quiz!</p>';
                    }
                })
                .catch(() => {
                    document.getElementById('esercizio-oggi-container').innerHTML = '<p>Nessun esercizio disponibile. Completa il quiz!</p>';
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
            
            fetchJson('api/esercizi.php?action=completa', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).then(d => {
                if (d.success) {
                    alert('✅ Esercizio completato!');
                    closeModal('completaEsercizioModal');
                    loadEsercizioOggi();
                    loadHomeStats();
                }
            }).catch(err => alert('❌ ' + err.message));
        }
        
        function loadProgressi() {
            Promise.all([
                fetchJson('api/progressi.php?action=statistiche'),
                fetchJson('api/progressi.php?action=progressi_ultimi_7')
            ]).then(([statsRes, progressiRes]) => {
                let statsHtml = '';
                (statsRes.stats || []).forEach(s => {
                    const diff = Number(s.difficolta_media || 1).toFixed(2);
                    statsHtml += `<div style="padding:12px;background:#f5f5f5;margin:8px 0;border-radius:5px;">
                        <strong>${esc(s.nome_esercizio)}</strong><br>
                        Completato ${Number(s.volte_completato || 0)}× | ${Number(s.ripetizioni_totali || 0)} tot ripetizioni | Difficoltà: ${diff}x
                    </div>`;
                });
                document.getElementById('stats-container').innerHTML = statsHtml || '<p>Nessun dato ancora</p>';

                const data = progressiRes.data || [];
                const maxReps = Math.max(1, ...data.map(p => Number(p.ripetizioni_totali || 0)));
                let chartHtml = '';
                data.forEach(p => {
                    const reps = Number(p.ripetizioni_totali || 0);
                    const width = Math.max(5, Math.round((reps / maxReps) * 100));
                    chartHtml += `<div style="margin:10px 0;">
                        <div style="display:flex;justify-content:space-between;font-size:0.92em;margin-bottom:4px;">
                            <span>${esc(p.data)}</span>
                            <strong>${reps} reps</strong>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:${width}%;"></div></div>
                    </div>`;
                });
                document.getElementById('progressChart').innerHTML = chartHtml || '<p>Nessun allenamento negli ultimi 7 giorni.</p>';
            }).catch(() => {
                document.getElementById('stats-container').innerHTML = '<p>Nessun dato ancora</p>';
                document.getElementById('progressChart').innerHTML = '<p>Dati non disponibili al momento.</p>';
            });
        }
        
        function loadAmici() {
            fetchJson('api/amicizie.php?action=classifica_amici')
                .then(d => {
                    if (d.success) {
                        let html = '';
                        d.amici.forEach((a, i) => {
                            html += `<li><strong>#${i + 1} ${esc(a.nome)} ${esc(a.cognome)}</strong> <span>${a.ripetizioni_totali || 0} reps</span></li>`;
                        });
                        document.getElementById('amici-list').innerHTML = html || '<li>Nessun amico</li>';
                    }
                })
                .catch(() => {
                    document.getElementById('amici-list').innerHTML = '<li>Nessun amico</li>';
                });
        }
        
        function loadClassifiche() {
            fetchJson('api/amicizie.php?action=classifica_amici')
                .then(d => {
                    let html = '';
                    (d.amici || []).forEach((a, i) => {
                        html += `<li><strong>#${i + 1} ${esc(a.nome)}</strong> <span>${a.ripetizioni_totali || 0} reps</span></li>`;
                    });
                    document.getElementById('classifica-amici').innerHTML = html || '<li>Nessun dato</li>';
                });
            
            fetchJson('api/amicizie.php?action=classifica_mondiale')
                .then(d => {
                    let html = '';
                    (d.utenti || []).forEach((u, i) => {
                        html += `<li><strong>#${i + 1} ${esc(u.nome)}</strong> <span>${u.ripetizioni_totali || 0} reps</span></li>`;
                    });
                    document.getElementById('classifica-mondiale').innerHTML = html || '<li>Nessun dato</li>';
                })
                .catch(() => {
                    document.getElementById('classifica-amici').innerHTML = '<li>Nessun dato</li>';
                    document.getElementById('classifica-mondiale').innerHTML = '<li>Nessun dato</li>';
                });
        }

        function updateGiorniRimanenti() {
            const giorniEl = document.getElementById('giorni_rimanenti');
            if (!giorniEl || !pianoAttivoDataFine) return;

            const oggi = new Date();
            const fine = new Date(pianoAttivoDataFine + 'T23:59:59');
            const diffDays = Math.max(0, Math.ceil((fine - oggi) / (1000 * 60 * 60 * 24)));
            giorniEl.textContent = String(diffDays);
        }

        function wireFriendSearch() {
            const input = document.getElementById('cerca_username');
            const risultati = document.getElementById('risultati-ricerca');
            if (!input || !risultati) return;

            let timer = null;
            input.addEventListener('input', () => {
                clearTimeout(timer);
                const q = input.value.trim();
                if (q.length < 2) {
                    risultati.innerHTML = '<p>Scrivi almeno 2 caratteri.</p>';
                    return;
                }

                timer = setTimeout(() => {
                    fetchJson('api/amicizie.php?action=cerca_utente&q=' + encodeURIComponent(q))
                        .then(d => {
                            let html = '';
                            (d.utenti || []).forEach(u => {
                                const stato = u.stato_amicizia || 'nessuno';
                                let cta = `<button class="btn" style="margin:0" onclick="addFriend(${u.id})">Aggiungi</button>`;
                                if (stato === 'pending') cta = '<span class="badge attivo">Richiesta inviata</span>';
                                if (stato === 'accepted') cta = '<span class="badge completato">Già amico</span>';

                                html += `<div style="padding:10px;background:#f5f5f5;margin:8px 0;border-radius:5px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                                    <div>
                                        <strong>${esc(u.nome)} ${esc(u.cognome)}</strong><br>
                                        <small>${esc(u.email)}</small>
                                    </div>
                                    ${cta}
                                </div>`;
                            });

                            risultati.innerHTML = html || '<p>Nessun utente trovato.</p>';
                        })
                        .catch(() => {
                            risultati.innerHTML = '<p>Ricerca non disponibile.</p>';
                        });
                }, 250);
            });
        }

        function addFriend(amicoId) {
            fetchJson('api/amicizie.php?action=add_amico', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({amico_id: amicoId})
            }).then(d => {
                if (!d.success) {
                    console.error('Errore: ' + (d.error || 'Impossibile inviare la richiesta'));
                    return;
                }
                
                // Mostra feedback positivo
                const feedbackDiv = document.getElementById('feedback-amico');
                feedbackDiv.textContent = '✅ Richiesta amicizia inviata!';
                feedbackDiv.style.display = 'block';
                
                // Aggiorna la ricerca per mostrare lo stato aggiornato
                const input = document.getElementById('cerca_username');
                if (input && input.value.trim().length >= 2) {
                    const q = input.value.trim();
                    fetchJson('api/amicizie.php?action=cerca_utente&q=' + encodeURIComponent(q))
                        .then(d => {
                            let html = '';
                            (d.utenti || []).forEach(u => {
                                const stato = u.stato_amicizia || 'nessuno';
                                let cta = `<button class="btn" style="margin:0" onclick="addFriend(${u.id})">Aggiungi</button>`;
                                if (stato === 'pending') cta = '<span class="badge attivo">✓ Richiesta inviata</span>';
                                if (stato === 'accepted') cta = '<span class="badge completato">✓ Già amico</span>';

                                html += `<div style="padding:10px;background:#f5f5f5;margin:8px 0;border-radius:5px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                                    <div>
                                        <strong>${esc(u.nome)} ${esc(u.cognome)}</strong><br>
                                        <small>${esc(u.email)}</small>
                                    </div>
                                    ${cta}
                                </div>`;
                            });

                            document.getElementById('risultati-ricerca').innerHTML = html || '<p>Nessun utente trovato.</p>';
                        })
                        .catch(() => {});
                }
                loadAmici();
                
                // Chiudi il modal dopo 2 secondi
                setTimeout(() => {
                    closeModal('cercaAmicoModal');
                    feedbackDiv.style.display = 'none';
                    document.getElementById('cerca_username').value = '';
                    document.getElementById('risultati-ricerca').innerHTML = '';
                }, 2000);
                
            }).catch(err => {
                console.error('Errore: ' + err.message);
            });
        }

        function loadHomeStats() {
            fetchJson('api/progressi.php?action=totali')
                .then(d => {
                    if (!d.success) return;
                    const t = d.totali || {};
                    const giorniEl = document.getElementById('giorni_allenamento');
                    const repsEl = document.getElementById('ripetizioni_totali');
                    const diffEl = document.getElementById('difficolta_media');

                    if (giorniEl) giorniEl.textContent = t.giorni_allenamento || 0;
                    if (repsEl) repsEl.textContent = t.ripetizioni_totali || 0;
                    if (diffEl) diffEl.textContent = Number(t.difficolta_media || 1).toFixed(1) + 'x';

                    const esCompletatiEl = document.getElementById('esercizi_completati');
                    if (esCompletatiEl) {
                        esCompletatiEl.textContent = t.esercizi_completati || 0;
                    }

                    const progressEl = document.getElementById('plan_progress');
                    if (progressEl && pianoAttivoDataFine) {
                        const giorniTotali = 28;
                        const rimanenti = Number(document.getElementById('giorni_rimanenti')?.textContent || 0);
                        const completamento = Math.max(0, Math.min(100, Math.round(((giorniTotali - rimanenti) / giorniTotali) * 100)));
                        progressEl.style.width = completamento + '%';
                    }
                })
                .catch(() => {});
        }

        function loadTenants() {
            fetch('api/tenants.php?action=list')
                .then(r => r.json())
                .then(d => {
                    if (!d.success) {
                        document.getElementById('tenants-container').innerHTML = '<p>Errore nel caricamento delle palestre.</p>';
                        return;
                    }

                    let html = '';
                    d.tenants.forEach(t => {
                        html += `<div style="padding:12px;background:#f5f5f5;margin:8px 0;border-radius:5px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                            <div>
                                <strong>${t.nome}</strong><br>
                                <small>${t.slug} | utenti: ${t.utenti}</small>
                            </div>
                            <button class="btn btn-secondary" onclick="activateTenant(${t.id})">Attiva</button>
                        </div>`;
                    });

                    document.getElementById('tenants-container').innerHTML = html || '<p>Nessuna palestra creata.</p>';
                });
        }

        function createTenant(e) {
            e.preventDefault();
            const nome = document.getElementById('tenant_nome').value.trim();
            const slugInput = document.getElementById('tenant_slug').value.trim();
            const payload = {
                nome,
                slug: slugInput || slugify(nome)
            };

            fetch('api/tenants.php?action=create', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('tenant_nome').value = '';
                    document.getElementById('tenant_slug').value = '';
                    loadTenants();
                } else {
                    alert('❌ ' + (d.error || 'Errore creazione palestra'));
                }
            });
        }

        function activateTenant(tenantId) {
            fetch('api/tenants.php?action=activate', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({tenant_id: tenantId})
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    location.reload();
                } else {
                    alert('❌ ' + (d.error || 'Impossibile attivare la palestra'));
                }
            });
        }
        
        async function submitTrainingSettings(e) {
            e.preventDefault();
            const payload = {
                orario_notifica: document.getElementById('impostazione_orario').value,
                notifiche_attive: Number(document.getElementById('impostazione_notifiche').value)
            };

            try {
                const res = await fetchJson('api/quiz.php?action=update_settings', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                if (res.success) {
                    alert('✅ Impostazioni salvate');
                    closeModal('impostazioniAllenamentoModal');
                    location.reload();
                }
            } catch (err) {
                alert('❌ ' + err.message);
            }
        }

        async function submitRestSettings(e) {
            e.preventDefault();
            const payload = { riposo_giorni: Number(document.getElementById('riposo_giorni').value) };
            try {
                const res = await fetchJson('api/piani.php?action=set_rest', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                if (res.success) {
                    alert('✅ Riposo salvato');
                    closeModal('riposoModal');
                }
            } catch (err) {
                alert('❌ ' + err.message);
            }
        }

        async function submitFinalFeedback(e) {
            e.preventDefault();
            const feedback = document.getElementById('feedback_finale').value.trim();
            try {
                const res = await fetchJson('api/feedback.php?action=finale', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({feedback})
                });
                if (res.success) {
                    alert('✅ Feedback inviato, grazie!');
                    closeModal('feedbackFinaleModal');
                }
            } catch (err) {
                alert('❌ ' + err.message);
            }
        }

        async function createNewPlan() {
            if (!confirm('Sei sicuro di voler creare un nuovo piano? Questo potrebbe sovrascrivere il piano attivo.')) return;
            try {
                const res = await fetchJson('api/piani.php?action=create_new', { method: 'POST' });
                if (res.success) {
                    alert('✅ Nuovo piano creato');
                    closeModal('creaPianoModal');
                    location.reload();
                }
            } catch (err) {
                alert('❌ ' + err.message);
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            if (ruoloCorrente === 'utente') {
                updateGiorniRimanenti();
                loadHomeStats();
                loadEsercizioOggi();
                loadProgressi();
                wireFriendSearch();
                return;
            }

            if (ruoloCorrente === 'super_admin') {
                loadTenants();
            }
        });
    </script>
</body>
</html>
