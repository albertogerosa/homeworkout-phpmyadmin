<?php
header("Content-Type: application/json");
require_once '../database.php'; // Usa la config che punta a 'allenamenti'
require_once '../tenant_helper.php';
require_once 'jwt_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Cerchiamo l'utente nella tabella 'utenti'
    $stmt = $pdo->prepare("SELECT id, password, tenant_id FROM utenti WHERE email = :e LIMIT 1");
    $stmt->execute(['e' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $stmtRole = $pdo->prepare("SELECT ruolo_id FROM utente_ruolo WHERE utente_id = :uid LIMIT 1");
        $stmtRole->execute(['uid' => $user['id']]);
        $ruoloId = (int)($stmtRole->fetchColumn() ?: 1);
        $tenantId = isset($user['tenant_id']) ? (int)$user['tenant_id'] : null;
        $tenantName = null;

        if ($tenantId !== null) {
            $stmtTenant = $pdo->prepare("SELECT nome FROM tenants WHERE id = :tenant_id LIMIT 1");
            $stmtTenant->execute(['tenant_id' => $tenantId]);
            $tenantName = $stmtTenant->fetchColumn() ?: null;
        }

        $accessToken = generateJWT($user['id'], 5, $ruoloId, $tenantId, $tenantName); // Scadenza 5 min come da consegna
        $refreshToken = generateJWT($user['id'], 10, $ruoloId, $tenantId, $tenantName); // Refresh token 10 min

        echo json_encode([
            "status" => "success",
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken,
            "tenant_id" => $tenantId,
            "tenant_name" => $tenantName,
            "role_id" => $ruoloId,
            "role_name" => homeworkoutRoleNameFromId($ruoloId)
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Credenziali non valide"]);
    }
}
?>