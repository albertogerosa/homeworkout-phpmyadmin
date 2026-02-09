<?php
require_once 'articolo.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $prezzo = $_POST['prezzo'] ?? 0;
    $immagine = $_POST['immagine'] ?? '';

    $articolo = new Articolo($titolo, $descrizione, $prezzo, $immagine);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Articolo Inserito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1 class="mb-4">Articolo Creato</h1>
    <?php
        if (isset($articolo)) {
            $articolo->show();
        } else {
            echo '<p>Errore nella creazione dell\'articolo.</p>';
        }
    ?>
    <a href="index.php" class="btn btn-secondary mt-3">Torna indietro</a>
</body>
</html>
