<?php
header("Content-Type: application/json");
require_once '../database.php';
require_once '../tenant_helper.php';
require_once 'jwt_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRicevuto = $_POST['refresh_token'] ?? '';

    if (empty($tokenRicevuto)) {
        http_response_code(400);
        echo json_encode(["error" => "Refresh token mancante"]);
        exit;
    }

    // Cerchiamo l'utente che ha quel refresh token ed è ancora valido
    $stmt = $pdo->prepare("SELECT id, tenant_id FROM utenti WHERE refresh_token = :rt AND refresh_token_scadenza > NOW()");
    $stmt->execute(['rt' => $tokenRicevuto]);
    $utente = $stmt->fetch();

    if ($utente) {
        $stmtRole = $pdo->prepare("SELECT ruolo_id FROM utente_ruolo WHERE utente_id = :uid LIMIT 1");
        $stmtRole->execute(['uid' => $utente['id']]);
        $ruoloId = (int)($stmtRole->fetchColumn() ?: 1);
        $tenantId = isset($utente['tenant_id']) ? (int)$utente['tenant_id'] : null;
        $tenantName = null;

        if ($tenantId !== null) {
            $stmtTenant = $pdo->prepare("SELECT nome FROM tenants WHERE id = :tenant_id LIMIT 1");
            $stmtTenant->execute(['tenant_id' => $tenantId]);
            $tenantName = $stmtTenant->fetchColumn() ?: null;
        }

        // Genera un nuovo Access Token di 5 minuti
        $newAccessToken = generateJWT($utente['id'], 5, $ruoloId, $tenantId, $tenantName);
        
        echo json_encode([
            "status" => "success",
            "access_token" => $newAccessToken,
            "tenant_id" => $tenantId,
            "tenant_name" => $tenantName,
            "role_id" => $ruoloId
        ]);
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Refresh token non valido o scaduto"]);
    }
}
?>