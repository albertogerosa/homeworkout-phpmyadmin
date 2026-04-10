<?php
session_start();
require_once 'database.php';

$defaultTenantId = 1;
try {
    $stmtTenant = $pdo->prepare("SELECT id FROM tenants WHERE slug = :slug LIMIT 1");
    $stmtTenant->execute(['slug' => 'demo-homeworkout']);
    $defaultTenantId = (int)($stmtTenant->fetchColumn() ?: 1);
} catch (PDOException $e) {
    $defaultTenantId = 1;
}

$messaggio = "";
$tipo_messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $conferma_password = $_POST['conferma_password'] ?? '';

    // Validazione
    if (empty($nome) || empty($cognome) || empty($email) || empty($password)) {
        $messaggio = "Tutti i campi sono obbligatori";
        $tipo_messaggio = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messaggio = "Email non valida";
        $tipo_messaggio = "error";
    } elseif ($password !== $conferma_password) {
        $messaggio = "Le password non coincidono";
        $tipo_messaggio = "error";
    } elseif (strlen($password) < 6) {
        $messaggio = "La password deve avere almeno 6 caratteri";
        $tipo_messaggio = "error";
    } else {
        try {
            // Verifica se l'email esiste già
            $sql_check = "SELECT id FROM utenti WHERE email = :email";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute(['email' => $email]);
            
            if ($stmt_check->rowCount() > 0) {
                $messaggio = "Questa email è già registrata";
                $tipo_messaggio = "error";
            } else {
                // Inserimento nuovo utente
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $sql_insert = "INSERT INTO utenti (tenant_id, nome, cognome, email, password, livello) 
                               VALUES (:tenant_id, :nome, :cognome, :email, :password, 'principiante')";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':tenant_id' => $defaultTenantId,
                    ':nome' => $nome,
                    ':cognome' => $cognome,
                    ':email' => $email,
                    ':password' => $password_hash
                ]);

                $nuovoUtenteId = (int)$pdo->lastInsertId();
                $stmtRuolo = $pdo->prepare("INSERT INTO utente_ruolo (utente_id, ruolo_id) VALUES (:uid, 1)
                                            ON DUPLICATE KEY UPDATE ruolo_id = VALUES(ruolo_id)");
                $stmtRuolo->execute(['uid' => $nuovoUtenteId]);
                
                $messaggio = "Registrazione completata! Accedi con le tue credenziali.";
                $tipo_messaggio = "success";
                // Svuota il form
                $nome = $cognome = $email = '';
            }
        } catch(PDOException $e) {
            $messaggio = "Errore durante la registrazione: " . $e->getMessage();
            $tipo_messaggio = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - HomeWorkout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 1.8em;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .messaggio {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .messaggio.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .messaggio.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .link-login {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .link-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .link-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrazione</h1>
        
        <?php if ($messaggio): ?>
            <div class="messaggio <?php echo $tipo_messaggio; ?>">
                <?php echo htmlspecialchars($messaggio); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="cognome">Cognome</label>
                <input type="text" id="cognome" name="cognome" value="<?php echo htmlspecialchars($cognome ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="conferma_password">Conferma Password</label>
                <input type="password" id="conferma_password" name="conferma_password" required>
            </div>
            
            <button type="submit">Registrati</button>
        </form>
        
        <div class="link-login">
            Hai già un account? <a href="login.php">Accedi qui</a>
        </div>
    </div>
</body>
</html>