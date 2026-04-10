<?php
// 1. Error reporting per il debug (rimuovilo quando hai finito)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'database.php';

// Assicurati che il percorso sia corretto. 
// Se login.php è nella root e jwt_helper è in /JWT/
$helper_path = __DIR__ . '/JWT/jwt_helper.php';

if (file_exists($helper_path)) {
    require_once $helper_path;
} else {
    die("Errore: Il file $helper_path non è stato trovato. Controlla la cartella JWT.");
}

$messaggio = "";
$tipo_messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM utenti WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $utente = $stmt->fetch();

            if ($utente && password_verify($password, $utente['password'])) {
                
                $stmtRuolo = $pdo->prepare("SELECT ur.ruolo_id, r.nome_ruolo
                                            FROM utente_ruolo ur
                                            LEFT JOIN ruoli r ON r.id = ur.ruolo_id
                                            WHERE ur.utente_id = :uid
                                            LIMIT 1");
                $stmtRuolo->execute(['uid' => $utente['id']]);
                $ruolo = $stmtRuolo->fetch();

                $ruoloId = (int)($ruolo['ruolo_id'] ?? 1);
                $ruoloNome = $ruolo['nome_ruolo'] ?? 'utente';

                // Generazione Token
                $accessToken = generateJWT($utente['id'], 5, $ruoloId);
                $refreshTokenStr = bin2hex(random_bytes(32));
                $scadenzaRefresh = date('Y-m-d H:i:s', time() + (10 * 60));

                // Update tabella utenti
                $stmt_update = $pdo->prepare("UPDATE utenti SET refresh_token = :rt, refresh_token_scadenza = :rts WHERE id = :uid");
                $stmt_update->execute([
                    'rt' => $refreshTokenStr,
                    'rts' => $scadenzaRefresh,
                    'uid' => $utente['id']
                ]);

                // Sessioni
                $_SESSION['utente_id'] = $utente['id'];
                $_SESSION['access_token'] = $accessToken;
                $_SESSION['refresh_token'] = $refreshTokenStr;
                $_SESSION['nome'] = $utente['nome'];
                $_SESSION['cognome'] = $utente['cognome'] ?? '';
                $_SESSION['ruolo_id'] = $ruoloId;
                $_SESSION['ruolo_nome'] = $ruoloNome;

                header("Location: dashboard.php");
                exit;
            } else {
                $messaggio = "Credenziali errate";
                $tipo_messaggio = "error";
            }
        } catch(PDOException $e) {
            $messaggio = "Errore DB: " . $e->getMessage();
            $tipo_messaggio = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - HomeWorkout</title>
    <style>
        body { font-family: Arial; background: #764ba2; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 8px; width: 300px; }
        .error { color: red; margin-bottom: 10px; }
        input { width: 100%; margin-bottom: 10px; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #667eea; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($messaggio): ?>
            <div class="error"><?php echo $messaggio; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Accedi</button>
        </form>
    </div>
</body>
</html>