<?php
header("Content-Type: application/json");
require_once '../database.php';
require_once 'jwt_helper.php';

// Estraiamo il token dall'header Authorization (Bearer <token>)
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

$userData = validateJWT($token);

if (!$userData) {
    http_response_code(403);
    echo json_encode(["error" => "Token non valido o scaduto (Max 5 min)"]);
    exit;
}

try {
    // Query per ottenere i permessi associati ai ruoli dell'utente
    $sql = "SELECT p.nome_permesso 
            FROM permessi p
            JOIN ruolo_permesso rp ON p.id = rp.permesso_id
            JOIN utente_ruolo ur ON rp.ruolo_id = ur.ruolo_id
            WHERE ur.utente_id = :uid";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userData['user_id']]);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        "user_id" => $userData['user_id'],
        "permissions" => $permissions,
        "valid_until" => date('H:i:s', $userData['exp'])
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>