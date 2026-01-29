<?php
// articolo.php

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
        echo '
        <div class="card" style="width: 18rem; margin: 20px auto;">
            <img src="IMG/' . htmlspecialchars($this->immagine) . '" class="card-img-top" alt="Immagine articolo">
            <div class="card-body">
                <h5 class="card-title">' . htmlspecialchars($this->titolo) . '</h5>
                <p class="card-text">' . htmlspecialchars($this->descrizione) . '</p>
                <p class="card-text"><strong>Prezzo:</strong> €' . number_format($this->prezzo, 2, ',', '.') . '</p>
            </div>
        </div>';
    }
}
?>
