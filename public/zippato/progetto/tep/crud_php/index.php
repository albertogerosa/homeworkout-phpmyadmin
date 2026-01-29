<?php
// CRUD Utenti - PHP
session_start();
if (!isset($_SESSION['utenti'])) {
    $_SESSION['utenti'] = [];
}
$utenti = $_SESSION['utenti'];
// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'crea') {
    $id = uniqid();
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    if ($nome && $email) {
        $utenti[] = ['id' => $id, 'nome' => $nome, 'email' => $email];
        $_SESSION['utenti'] = $utenti;
    }
}
// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
    foreach ($utenti as &$utente) {
        if ($utente['id'] === $_POST['id']) {
            $utente['nome'] = $_POST['nome'];
            $utente['email'] = $_POST['email'];
        }
    }
    $_SESSION['utenti'] = $utenti;
}
// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'elimina') {
    $utenti = array_filter($utenti, function($u) {
        return $u['id'] !== $_POST['id'];
    });
    $_SESSION['utenti'] = $utenti;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>CRUD Utenti - PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1 class="mb-4">Gestione Utenti (PHP)</h1>
    <form method="POST" class="mb-4">
        <input type="hidden" name="azione" value="crea">
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Crea Utente</button>
    </form>
    <h2>Lista Utenti</h2>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Azioni</th></tr></thead>
        <tbody>
        <?php foreach ($utenti as $utente): ?>
        <tr>
            <form method="POST">
                <td><?php echo htmlspecialchars($utente['id']); ?></td>
                <td><input type="text" name="nome" value="<?php echo htmlspecialchars($utente['nome']); ?>" class="form-control"></td>
                <td><input type="email" name="email" value="<?php echo htmlspecialchars($utente['email']); ?>" class="form-control"></td>
                <td>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($utente['id']); ?>">
                    <button type="submit" name="azione" value="modifica" class="btn btn-warning btn-sm">Modifica</button>
                    <button type="submit" name="azione" value="elimina" class="btn btn-danger btn-sm">Elimina</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../../home.php" class="btn btn-secondary mt-4">Torna alla Home Page</a>
</body>
</html>
