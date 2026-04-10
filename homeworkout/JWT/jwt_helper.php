<?php
// Chiave segreta per la firma (usa una stringa complessa)
define('JWT_SECRET', 'GPO_Secret_Key_2026_!@#');

function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

// Genera un JWT con scadenza personalizzabile (default 5 minuti per la consegna)
function generateJWT($userId, $expiryMinutes = 5, $roleId = null) {
    $payloadData = [
        'user_id' => $userId,
        'iat' => time(),
        'exp' => time() + ($expiryMinutes * 60)
    ];

    if ($roleId !== null) {
        $payloadData['role_id'] = (int)$roleId;
    }

    $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    
    $payload = base64UrlEncode(json_encode($payloadData));

    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$signature";
}

// Valida il token e restituisce il payload o false
function validateJWT($jwt) {
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) != 3) return false;

    $header = $tokenParts[0];
    $payload = $tokenParts[1];
    $signatureProvided = $tokenParts[2];

    $signatureCheck = base64UrlEncode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

    if ($signatureCheck !== $signatureProvided) return false;

    $payloadData = json_decode(base64_decode($payload), true);
    if ($payloadData['exp'] < time()) return false; // Scaduto

    return $payloadData;
}
?>