<?php
header("Content-Type: application/json");
require_once 'jwt_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $refToken = $_POST['refresh_token'] ?? '';
    $data = validateJWT($refToken);

    if ($data) {
        $newAccessToken = generateJWT($data['user_id'], 5);
        echo json_encode(["access_token" => $newAccessToken]);
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Refresh token scaduto o non valido"]);
    }
}
?>