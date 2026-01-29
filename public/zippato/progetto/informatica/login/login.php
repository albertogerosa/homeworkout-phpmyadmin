<?php
session_start();
$errore = '';
if (!file_exists('../../utenti.json')) {
    file_put_contents('../../utenti.json', json_encode([]));
}
$utenti = json_decode(file_get_contents('../../utenti.json'), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $password = $_POST['password'] ?? '';
    if (isset($_POST['azione']) && $_POST['azione'] === 'registrati') {
        // REGISTRAZIONE
        foreach ($utenti as $utente) {
            if ($utente['nome'] === $nome) {
                $errore = 'Nome utente già esistente.';
                break;
            }
        }
        if (!$errore && $nome && $password) {
            $utenti[] = ['nome' => $nome, 'password' => password_hash($password, PASSWORD_DEFAULT)];
            file_put_contents('../../utenti.json', json_encode($utenti));
            $_SESSION['utente'] = $nome;
            header('Location: ../home.php');
            exit;
        }
    } elseif (isset($_POST['azione']) && $_POST['azione'] === 'login') {
        // LOGIN
        foreach ($utenti as $utente) {
            if ($utente['nome'] === $nome && password_verify($password, $utente['password'])) {
                $_SESSION['utente'] = $nome;
                header('Location: ../home.php');
                exit;
            }
        }
        $errore = 'Credenziali non valide.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login / Registrazione</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1 class="mb-4">Login / Registrazione</h1>
    <?php if ($errore): ?>
        <div class="alert alert-danger"><?php echo $errore; ?></div>
    <?php endif; ?>
    <form method="POST" class="mb-3">
        <div class="mb-3">
            <label class="form-label">Nome utente</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="azione" value="login" class="btn btn-primary">Accedi</button>
        <button type="submit" name="azione" value="registrati" class="btn btn-success ms-2">Registrati</button>
    </form>
</body>
</html>

