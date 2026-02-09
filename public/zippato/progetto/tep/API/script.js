const EASY_COUNTRIES_URL = './data/easy_countries.json';
const RESTCOUNTRIES_URL = 'https://restcountries.com/v3.1/all?fields=name,flags,cca2';

const modeSelect = document.getElementById('modeSelect');
const startBtn = document.getElementById('startBtn');
const nextBtn = document.getElementById('nextBtn');
const gameEl = document.getElementById('game');
const flagImg = document.getElementById('flagImg');
const guessInput = document.getElementById('guessInput');
const checkBtn = document.getElementById('checkBtn');
const messageEl = document.getElementById('message');
const infoEl = document.getElementById('info');

const scoreEl = document.getElementById('score');
const bestEl = document.getElementById('best');
const streakEl = document.getElementById('streak');

let countries = [];
let current = null;

let score = 0;
let best = Number(localStorage.getItem('flagGame_best') || 0);
let streak = 0;

bestEl.textContent = best;

// Normalizza testo: rimuove accenti e non-lettere, lowercase
function normalize(s) {
  if (!s) return '';
  try {
    s = s.normalize('NFD').replace(/\p{Diacritic}/gu, '');
  } catch (e) {
    s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }
  return s.toLowerCase().replace(/[^a-z0-9 ]+/g, '').trim();
}

function updateScoreDisplay() {
  scoreEl.textContent = score;
  bestEl.textContent = best;
  streakEl.textContent = streak;
}

async function loadCountries(mode) {
  infoEl.textContent = 'Caricamento paesi...';
  countries = [];
  if (mode === 'easy') {
    try {
      const res = await fetch(EASY_COUNTRIES_URL);
      countries = await res.json();
      countries = countries.map(c => ({
        name: c.name,
        flagUrl: c.flagUrl || `https://flagcdn.com/w320/${(c.cca2||'').toLowerCase()}.png`
      }));
      infoEl.textContent = `Modalità Facile: ${countries.length} paesi caricati.`;
    } catch (e) {
      console.error(e);
      infoEl.textContent = 'Impossibile caricare i paesi facili.';
    }
  } else {
    try {
      const res = await fetch(RESTCOUNTRIES_URL);
      const data = await res.json();
      countries = data.map(c => ({
        name: (c.name && (c.name.common || c.name.official)) || '',
        flagUrl: (c.flags && (c.flags.svg || c.flags.png)) || `https://flagcdn.com/w320/${(c.cca2||'').toLowerCase()}.png`
      })).filter(c => c.name && c.flagUrl);
      infoEl.textContent = `Modalità Difficile: ${countries.length} paesi caricati.`;
    } catch (e) {
      console.error(e);
      infoEl.textContent = 'Errore caricamento paesi (restcountries).';
    }
  }
}

function pickRandom() {
  if (!countries || countries.length === 0) return null;
  return countries[Math.floor(Math.random() * countries.length)];
}

function showCountry(c) {
  if (!c) return;
  current = c;
  flagImg.classList.remove('correct', 'wrong');
  flagImg.src = c.flagUrl;
  flagImg.alt = `Bandiera di ${c.name}`;
  guessInput.value = '';
  messageEl.textContent = '';
  gameEl.classList.remove('hidden');
  nextBtn.disabled = false;
}

function resetScore() {
  score = 0;
  streak = 0;
  updateScoreDisplay();
  guessInput.disabled = false; // riabilita solo quando inizia nuova partita
  checkBtn.disabled = false;   // riabilita solo quando inizia nuova partita
}

function correctAnswer() {
  score += 10;
  streak += 1;
  if (score > best) {
    best = score;
    localStorage.setItem('flagGame_best', String(best));
  }
  updateScoreDisplay();
  messageEl.textContent = `Corretto! È ${current.name}.`;
  flagImg.classList.add('correct');
}

function wrongAnswer(reveal = true) {
  score = 0;       // azzera il punteggio
  streak = 0;      // azzera lo streak
  updateScoreDisplay();
  flagImg.classList.add('wrong');
  if (reveal) messageEl.textContent = `Sbagliato. Era ${current.name}.`;

  // Mostra subito una nuova bandiera
  setTimeout(() => {
    const c = pickRandom();
    if (c) showCountry(c);
  }, 900); // leggero delay per far vedere il rosso
}


startBtn.addEventListener('click', async () => {
  startBtn.disabled = true;
  resetScore();
  updateScoreDisplay();
  const mode = modeSelect.value;
  await loadCountries(mode);
  const c = pickRandom();
  if (c) showCountry(c);
  startBtn.disabled = false;
});

nextBtn.addEventListener('click', () => {
  if (guessInput.disabled) return; // se hai sbagliato non fare nulla
  const c = pickRandom();
  if (c) showCountry(c);
});

checkBtn.addEventListener('click', () => {
  if (!current) {
    messageEl.textContent = 'Premi "Inizia Gioco" prima.';
    return;
  }
  const guess = normalize(guessInput.value);
  const target = normalize(current.name);
  if (!guess) {
    messageEl.textContent = 'Inserisci una risposta.';
    return;
  }
  if (guess === target || target.includes(guess) || guess.includes(target)) {
    correctAnswer();
    setTimeout(() => {
      const c = pickRandom();
      if (c && !guessInput.disabled) showCountry(c);
    }, 2000);
  } else {
    wrongAnswer();
  }
});

// consentire invio con Enter
guessInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') checkBtn.click();
});

// inizializza display
updateScoreDisplay();
