<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $age = $data['age'] ?? null;
    $fitness_level = $data['fitness_level'] ?? 'principiante';
    $goal = $data['goal'] ?? null;
    $notification_time = $data['notification_time'] ?? '08:00:00';
    
    // Aggiorna profilo utente
    $conn->query("UPDATE users SET age=$age, fitness_level='$fitness_level', goal='$goal', notification_time='$notification_time' WHERE id=$user_id");
    
    // Crea piano personalizzato
    $difficulty = ($fitness_level === 'principiante') ? 'facile' : (($fitness_level === 'intermedio') ? 'medio' : 'difficile');
    $duration = 28;
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+$duration days"));
    
    $result = $conn->query("INSERT INTO workout_plans (user_id, difficulty, duration_days, start_date, end_date) VALUES ($user_id, '$difficulty', $duration, '$start_date', '$end_date')");
    
    $plan_id = $conn->insert_id;
    
    // ...crea esercizi in base al livello...
    create_exercises($conn, $plan_id, $fitness_level);
    
    echo json_encode(['success' => true, 'plan_id' => $plan_id]);
}

function create_exercises($conn, $plan_id, $fitness_level) {
    $exercises = [
        'principiante' => [
            ['Flessioni', 'Flessioni a muro', 10, 3],
            ['Push-up', 'Push-up da terra', 5, 3],
            ['Squat', 'Squat a corpo libero', 15, 3],
            ['Plancia', 'Plancia frontale', 20, 3],
        ],
        'intermedio' => [
            ['Flessioni', 'Flessioni standard', 15, 4],
            ['Push-up diamante', 'Push-up diamante', 8, 4],
            ['Squat pistol', 'Squat pistol assist', 8, 3],
            ['Plancia', 'Plancia frontale', 45, 3],
        ],
        'avanzato' => [
            ['Flessioni archer', 'Flessioni archer', 10, 4],
            ['Handstand', 'Handstand hold', 30, 4],
            ['Squat pistol', 'Squat pistol completo', 10, 4],
            ['Plancia diamante', 'Plancia diamante', 60, 3],
        ],
    ];
    
    $exs = $exercises[$fitness_level] ?? $exercises['principiante'];
    
    for ($day = 1; $day <= 28; $day++) {
        $ex_index = ($day - 1) % count($exs);
        $ex = $exs[$ex_index];
        
        $difficulty_increase = 1.0 + (floor($day / 7) * 0.1);
        
        $conn->query("INSERT INTO exercises (plan_id, exercise_name, description, reps, sets, day, difficulty_increase) 
                     VALUES ($plan_id, '{$ex[0]}', '{$ex[1]}', {$ex[2]}, {$ex[3]}, $day, $difficulty_increase)");
    }
}
?>
