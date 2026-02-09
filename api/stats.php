<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_GET['action'] === 'daily_progress') {
    $result = $conn->query("
        SELECT DATE(date) as day, COUNT(*) as exercises_done, SUM(reps_done) as total_reps
        FROM user_progress
        WHERE user_id = $user_id
        GROUP BY DATE(date)
        ORDER BY date DESC
        LIMIT 30
    ");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

if ($_GET['action'] === 'exercise_stats') {
    $result = $conn->query("
        SELECT exercise_name, COUNT(*) as times_completed, SUM(reps_done) as total_reps, AVG(difficulty_level) as avg_difficulty
        FROM user_progress
        WHERE user_id = $user_id
        GROUP BY exercise_name
    ");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}
?>
