<?php
class Articolo {
    public $titolo;
    public $descrizione;
    public $prezzo;
    public $immagine;
    public function __construct($titolo, $descrizione, $prezzo, $immagine) {
        $this->titolo = $titolo;
        $this->descrizione = $descrizione;
        $this->prezzo = $prezzo;
        $this->immagine = $immagine;
    }
    public function show() {
        echo '<div class="card" style="width: 18rem; margin: 20px auto;">
            <img src="../../IMG/' . htmlspecialchars($this->immagine) . '" class="card-img-top" alt="Immagine articolo">
            <div class="card-body">
                <h5 class="card-title">' . htmlspecialchars($this->titolo) . '</h5>
                <p class="card-text">' . htmlspecialchars($this->descrizione) . '</p>
                <p class="card-text"><strong>Prezzo:</strong> €' . number_format($this->prezzo, 2, ',', '.') . '</p>
            </div>
        </div>';
    }
}

$articoli = [];
$file = '../../articoli.json';
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if ($data) {
        foreach ($data as $a) {
            $articoli[] = new Articolo($a['titolo'], $a['descrizione'], $a['prezzo'], $a['immagine']);
        }
    }
}
// Se non ci sono articoli, mostra quelli di esempio
if (count($articoli) === 0) {
    $articoli[] = new Articolo("CANE", "Un cane simpatico", 99.99, "CANE.jpg");
    $articoli[] = new Articolo("PANDA", "Un panda dolce", 149.99, "PANDA.jpg");
    $articoli[] = new Articolo("SCIMMIA", "Una scimmia vivace", 79.99, "SCIMMIA.jpg");
}
