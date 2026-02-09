<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_GET['action'] === 'today') {
    $today = date('Y-m-d');
    $day_of_plan = date('z') - strtotime($conn->query("SELECT start_date FROM workout_plans WHERE user_id=$user_id AND status='attivo' LIMIT 1")->fetch_assoc()['start_date'], 0) / 86400 + 1;
    
    $result = $conn->query("
        SELECT e.* FROM exercises e
        JOIN workout_plans wp ON e.plan_id = wp.id
        WHERE wp.user_id = $user_id AND wp.status = 'attivo' AND e.day = DAYOFYEAR(NOW()) - DAYOFYEAR(wp.start_date) + 1
        LIMIT 1
    ");
    
    echo json_encode($result->fetch_assoc());
}

if ($_GET['action'] === 'complete' && $_POST) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $exercise_id = $data['exercise_id'];
    $reps_done = $data['reps_done'];
    $sets_done = $data['sets_done'];
    $feedback = $data['feedback'] ?? '';
    
    $conn->query("INSERT INTO user_progress (user_id, exercise_id, date, completed, reps_done, sets_done, feedback) 
                 VALUES ($user_id, $exercise_id, NOW(), 1, $reps_done, $sets_done, '$feedback')");
    
    echo json_encode(['success' => true]);
}
?>
