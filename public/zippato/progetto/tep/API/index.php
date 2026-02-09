 <!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Indovina la Bandiera — Modalità Facile / Difficile</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container">
    <header class="header-card">
      <h1>Indovina la Bandiera</h1>
      <div class="score-panel">
        <div class="score-item">Punteggio: <span id="score">0</span></div>
        <div class="score-item">Best: <span id="best">0</span></div>
        <div class="score-item">Streak: <span id="streak">0</span></div>
      </div>
    </header>

    <div class="controls">
      <label>Modalità:
        <select id="modeSelect">
          <option value="easy">Facile</option>
          <option value="hard">Difficile</option>
        </select>
      </label>
      <button id="startBtn" class="btn">Inizia Gioco</button>
      <button id="nextBtn" class="btn" disabled>Nuova Bandiera</button>
    </div>

    <div id="game" class="card hidden">
      <img id="flagImg" src="" alt="Bandiera" />
      <input id="guessInput" placeholder="Scrivi il nome del paese" />
      <div class="actions">
        <button id="checkBtn" class="btn primary">Controlla</button>
      </div>
      <p id="message" class="message"></p>
    </div>

    <p id="info" class="muted">Scegli la modalità e premi "Inizia Gioco".</p>
  </main>

  <script src="script.js"></script>
</body>
</html>