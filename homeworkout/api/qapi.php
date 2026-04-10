<?php
header("Content-Type: application/json");
require_once '../database.php';
require_once '../JWT/jwt_helper.php';

function roleNameFromId($roleId) {
    $map = [
        1 => 'utente',
        2 => 'allenatore',
        3 => 'amministratore'
    ];

    return $map[(int)$roleId] ?? 'utente';
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token) && !empty($_GET['token'])) {
    $token = $_GET['token'];
}

if (empty($token)) {
    session_start();
    $token = $_SESSION['access_token'] ?? '';
}

$userData = validateJWT($token);

if (!$userData || empty($userData['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Token non valido o scaduto'
    ]);
    exit;
}

$userId = (int)$userData['user_id'];
$roleId = isset($userData['role_id']) ? (int)$userData['role_id'] : null;
$roleName = null;

try {
    if ($roleId === null || $roleId <= 0) {
        $stmtRole = $pdo->prepare("SELECT ruolo_id FROM utente_ruolo WHERE utente_id = :uid LIMIT 1");
        $stmtRole->execute(['uid' => $userId]);
        $roleId = (int)($stmtRole->fetchColumn() ?: 1);
    }

    $stmtRoleName = $pdo->prepare("SELECT nome_ruolo FROM ruoli WHERE id = :rid LIMIT 1");
    $stmtRoleName->execute(['rid' => $roleId]);
    $roleName = $stmtRoleName->fetchColumn() ?: roleNameFromId($roleId);

    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'role_id' => $roleId,
        'role_name' => $roleName,
        'dashboard' => $roleName
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore interno nel recupero ruolo'
    ]);
}
?>