<?php
// Carrello degli oggetti
session_start();
$carrello = isset($_SESSION['carrello']) ? $_SESSION['carrello'] : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $prezzo = $_POST['prezzo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $immagine = $_POST['immagine'] ?? '';
    if ($nome && $prezzo && $descrizione && $immagine) {
        $carrello[] = [
            'nome' => $nome,
            'prezzo' => $prezzo,
            'descrizione' => $descrizione,
            'immagine' => $immagine
        ];
        $_SESSION['carrello'] = $carrello;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Carrello degli Oggetti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1 class="mb-4">Carrello degli Oggetti</h1>
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Prezzo (€)</label>
            <input type="number" name="prezzo" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrizione</label>
            <textarea name="descrizione" class="form-control" rows="2" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Immagine</label>
            <select name="immagine" class="form-select" required>
                <option value="CANE.jpg">CANE.jpg</option>
                <option value="PANDA.jpg">PANDA.jpg</option>
                <option value="SCIMMIA.jpg">SCIMMIA.jpg</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Aggiungi al carrello</button>
    </form>
    <h2>Oggetti nel carrello</h2>
    <div class="row">
        <?php foreach ($carrello as $oggetto): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <img src="../../../IMG/<?php echo htmlspecialchars($oggetto['immagine']); ?>" class="card-img-top" alt="Immagine">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($oggetto['nome']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($oggetto['descrizione']); ?></p>
                    <p class="card-text"><strong>Prezzo:</strong> €<?php echo number_format($oggetto['prezzo'], 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (count($carrello) === 0): ?>
        <p>Nessun oggetto nel carrello.</p>
        <?php endif; ?>
    </div>
    <a href="../progetti.html" class="btn btn-secondary mt-4">Torna ai progetti di Informatica</a>
    <a href="../../../home.html" class="btn btn-outline-secondary mt-2">Torna alla Home Page</a>
</body>
</html>
