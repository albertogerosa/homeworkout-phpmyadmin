<?php
header("Content-Type: application/json");
require_once '../database.php';
require_once 'jwt_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRicevuto = $_POST['refresh_token'] ?? '';

    if (empty($tokenRicevuto)) {
        http_response_code(400);
        echo json_encode(["error" => "Refresh token mancante"]);
        exit;
    }

    // Cerchiamo l'utente che ha quel refresh token ed è ancora valido
    $stmt = $pdo->prepare("SELECT id FROM utenti WHERE refresh_token = :rt AND refresh_token_scadenza > NOW()");
    $stmt->execute(['rt' => $tokenRicevuto]);
    $utente = $stmt->fetch();

    if ($utente) {
        // Genera un nuovo Access Token di 5 minuti
        $newAccessToken = generateJWT($utente['id'], 5);
        
        echo json_encode([
            "status" => "success",
            "access_token" => $newAccessToken
        ]);
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Refresh token non valido o scaduto"]);
    }
}
?>