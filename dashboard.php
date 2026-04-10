<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* UTENTE */
$user = $conn->query("SELECT * FROM utenti WHERE id=$user_id")->fetch_assoc();

/* RUOLO */
$roleQuery = $conn->query("
SELECT r.nome_ruolo 
FROM ruoli r
JOIN utente_ruolo ur ON ur.ruolo_id = r.id
WHERE ur.utente_id = $user_id
LIMIT 1
");

$role = $roleQuery->fetch_assoc()['nome_ruolo'];

/* PIANO UTENTE */
$plan = $conn->query("SELECT * FROM piani_allenamento WHERE utente_id=$user_id AND stato='attivo' LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">

<head>
<meta charset="UTF-8">
<title>Home Workout Dashboard</title>

<style>

*{margin:0;padding:0;box-sizing:border-box;}

body{
font-family:Segoe UI;
background:linear-gradient(135deg,#667eea,#764ba2);
min-height:100vh;
color:#333;
}

.container{
max-width:1200px;
margin:auto;
padding:20px;
}

header{
background:white;
padding:20px;
border-radius:10px;
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
gap:20px;
}

.card{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 6px rgba(0,0,0,0.1);
}

.btn{
padding:12px 20px;
background:#667eea;
color:white;
border:none;
border-radius:5px;
cursor:pointer;
margin-top:10px;
}

.btn:hover{
background:#764ba2;
}

.tabs{
display:flex;
gap:10px;
margin-bottom:20px;
}

.tab{
padding:10px 20px;
background:#e0e0e0;
cursor:pointer;
border-radius:5px;
}

.tab.active{
background:#667eea;
color:white;
}

.tab-content{
display:none;
}

.tab-content.active{
display:block;
}

</style>
</head>

<body>

<div class="container">

<header>

<h1>🏋️ Home Workout</h1>

<div>

<b><?php echo $user['nome']; ?></b>

(<?php echo $role; ?>)

<button class="btn" onclick="logout()">Esci</button>

</div>

</header>

<!-- TABS -->

<div class="tabs">

<?php if($role=="utente"): ?>

<div class="tab active" onclick="switchTab('home')">Home</div>
<div class="tab" onclick="switchTab('allenamento')">Allenamento</div>
<div class="tab" onclick="switchTab('progressi')">Progressi</div>
<div class="tab" onclick="switchTab('amici')">Amici</div>
<div class="tab" onclick="switchTab('classifica')">Classifica</div>

<?php elseif($role=="allenatore"): ?>

<div class="tab active" onclick="switchTab('piani')">Piani</div>
<div class="tab" onclick="switchTab('esercizi')">Esercizi</div>
<div class="tab" onclick="switchTab('statistiche')">Statistiche</div>

<?php elseif($role=="amministratore"): ?>

<div class="tab active" onclick="switchTab('utenti')">Utenti</div>
<div class="tab" onclick="switchTab('ruoli')">Ruoli</div>
<div class="tab" onclick="switchTab('global_stats')">Statistiche</div>

<?php endif; ?>

</div>

<!-- ====================== -->
<!-- INTERFACCIA UTENTE -->
<!-- ====================== -->

<?php if($role=="utente"): ?>

<div id="home" class="tab-content active">

<div class="grid">

<div class="card">

<h2>Piano attuale</h2>

<?php if($plan): ?>

<p>Difficoltà: <?php echo $plan['difficolta']; ?></p>
<p>Inizio: <?php echo $plan['data_inizio']; ?></p>
<p>Fine: <?php echo $plan['data_fine']; ?></p>

<?php else: ?>

<p>Nessun piano attivo</p>
<button class="btn">Fai il quiz</button>

<?php endif; ?>

</div>

<div class="card">

<h2>Statistiche</h2>

<p>Allenamenti completati</p>
<p>Ripetizioni totali</p>

</div>

</div>

</div>


<div id="allenamento" class="tab-content">

<div class="card">

<h2>Esercizio di oggi</h2>

<button class="btn">Completa allenamento</button>

</div>

</div>


<div id="progressi" class="tab-content">

<div class="card">

<h2>I tuoi progressi</h2>

<p>Grafico progressi</p>

</div>

</div>


<div id="amici" class="tab-content">

<div class="card">

<h2>Amici</h2>

<button class="btn">Aggiungi amico</button>

</div>

</div>


<div id="classifica" class="tab-content">

<div class="card">

<h2>Classifica mondiale</h2>

</div>

</div>

<?php endif; ?>


<!-- ====================== -->
<!-- INTERFACCIA ALLENATORE -->
<!-- ====================== -->

<?php if($role=="allenatore"): ?>

<div id="piani" class="tab-content active">

<div class="card">

<h2>Gestione piani allenamento</h2>

<button class="btn">Crea piano</button>
<button class="btn">Modifica piano</button>
<button class="btn">Elimina piano</button>

</div>

</div>


<div id="esercizi" class="tab-content">

<div class="card">

<h2>Gestione esercizi</h2>

<button class="btn">Aggiungi esercizio</button>
<button class="btn">Modifica esercizio</button>

</div>

</div>


<div id="statistiche" class="tab-content">

<div class="card">

<h2>Statistiche utenti</h2>

<button class="btn">Visualizza progressi utenti</button>

</div>

</div>

<?php endif; ?>


<!-- ====================== -->
<!-- INTERFACCIA ADMIN -->
<!-- ====================== -->

<?php if($role=="amministratore"): ?>

<div id="utenti" class="tab-content active">

<div class="card">

<h2>Gestione utenti</h2>

<button class="btn">Lista utenti</button>
<button class="btn">Elimina utente</button>
<button class="btn">Cambia ruolo</button>

</div>

</div>


<div id="ruoli" class="tab-content">

<div class="card">

<h2>Gestione ruoli</h2>

<button class="btn">Crea ruolo</button>
<button class="btn">Modifica permessi</button>

</div>

</div>


<div id="global_stats" class="tab-content">

<div class="card">

<h2>Statistiche globali</h2>

<button class="btn">Classifica mondiale</button>

</div>

</div>

<?php endif; ?>


</div>

<script>

function switchTab(tab){

document.querySelectorAll(".tab-content").forEach(t=>t.classList.remove("active"))
document.querySelectorAll(".tab").forEach(t=>t.classList.remove("active"))

document.getElementById(tab).classList.add("active")

event.target.classList.add("active")

}

function logout(){

window.location="logout.php"

}

</script>

</body>
</html>