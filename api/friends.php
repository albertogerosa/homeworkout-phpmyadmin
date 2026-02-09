<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_GET['action'] === 'leaderboard_friends') {
    $result = $conn->query("
        SELECT u.id, u.username, COUNT(up.id) as workouts_done, SUM(up.reps_done) as total_reps
        FROM users u
        JOIN friendships f ON (f.user_id = $user_id AND f.friend_id = u.id) OR (f.friend_id = $user_id AND f.user_id = u.id)
        LEFT JOIN user_progress up ON u.id = up.user_id
        WHERE f.status = 'accepted'
        GROUP BY u.id
        ORDER BY total_reps DESC
    ");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

if ($_GET['action'] === 'leaderboard_global') {
    $result = $conn->query("
        SELECT u.id, u.username, COUNT(up.id) as workouts_done, SUM(up.reps_done) as total_reps
        FROM users u
        LEFT JOIN user_progress up ON u.id = up.user_id
        GROUP BY u.id
        ORDER BY total_reps DESC
        LIMIT 100
    ");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

if ($_GET['action'] === 'add_friend' && $_POST) {
    $data = json_decode(file_get_contents('php://input'), true);
    $friend_id = $data['friend_id'];
    
    $conn->query("INSERT INTO friendships (user_id, friend_id, status) VALUES ($user_id, $friend_id, 'pending')");
    echo json_encode(['success' => true]);
}
?>
