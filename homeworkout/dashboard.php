<?php
session_start();
require_once 'database.php';

// Verifica se l'utente è autenticato
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit;
}

$utente_id = $_SESSION['utente_id'];

try {
    // Recupera i piani di allenamento dell'utente
    $sql_piani = "SELECT id, data_inizio, data_fine, difficolta, stato FROM piani_allenamento WHERE utente_id = :utente_id";
    $stmt_piani = $pdo->prepare($sql_piani);
    $stmt_piani->execute(['utente_id' => $utente_id]);
    $piani = $stmt_piani->fetchAll();
    
    // Recupera i progressi recenti
    $sql_progressi = "SELECT p.calorie_bruciate, p.fatica, p.data_all 
                      FROM progressi p 
                      WHERE p.utente_id = :utente_id 
                      ORDER BY p.data_all DESC 
                      LIMIT 7";
    $stmt_progressi = $pdo->prepare($sql_progressi);
    $stmt_progressi->execute(['utente_id' => $utente_id]);
    $progressi = $stmt_progressi->fetchAll();
    
    // Calcola statistiche
    $calorie_totali = 0;
    $fatica_media = 0;
    if (count($progressi) > 0) {
        $calorie_totali = array_sum(array_column($progressi, 'calorie_bruciate'));
        $fatica_media = array_sum(array_column($progressi, 'fatica')) / count($progressi);
    }
    
} catch(PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HomeWorkout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content h1 {
            font-size: 1.8em;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logout-btn {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .benvenuto {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .benvenuto h2 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .statistiche {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card-stat {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .card-stat h3 {
            color: #666;
            margin-bottom: 10px;
            font-size: 0.9em;
            text-transform: uppercase;
        }
        
        .card-stat .valore {
            font-size: 2em;
            color: #667eea;
            font-weight: bold;
        }
        
        .piani-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .piani-section h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .piano-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        
        .piano-item p {
            margin: 5px 0;
            color: #666;
        }
        
        .stato-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .stato-badge.attivo {
            background-color: #d4edda;
            color: #155724;
        }
        
        .stato-badge.completato {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .piani-vuoto {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .btn-nuovo-piano {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-nuovo-piano:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>HomeWorkout</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['cognome']); ?></span>
                <a href="logout.php" class="logout-btn">Esci</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="benvenuto">
            <h2>Benvenuto, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
            <p>Livello: <strong><?php echo ucfirst($_SESSION['livello']); ?></strong></p>
        </div>
        
        <div class="statistiche">
            <div class="card-stat">
                <h3>Calorie Bruciate (ultimi 7 giorni)</h3>
                <div class="valore"><?php echo number_format($calorie_totali, 0); ?></div>
            </div>
            <div class="card-stat">
                <h3>Fatica Media</h3>
                <div class="valore"><?php echo number_format($fatica_media, 1); ?>/5</div>
            </div>
            <div class="card-stat">
                <h3>Sessioni Completate</h3>
                <div class="valore"><?php echo count($progressi); ?></div>
            </div>
        </div>
        
        <div class="piani-section">
            <h3>I Tuoi Piani di Allenamento</h3>
            
            <?php if (count($piani) > 0): ?>
                <?php foreach ($piani as $piano): ?>
                    <div class="piano-item">
                        <p><strong>Dal <?php echo date('d/m/Y', strtotime($piano['data_inizio'])); ?> al <?php echo date('d/m/Y', strtotime($piano['data_fine'])); ?></strong></p>
                        <p>Difficoltà: <strong><?php echo $piano['difficolta']; ?>/5</strong></p>
                        <p>Stato: <span class="stato-badge <?php echo $piano['stato']; ?>"><?php echo ucfirst($piano['stato']); ?></span></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="piani-vuoto">
                    <p>Non hai ancora creato nessun piano di allenamento.</p>
                </div>
            <?php endif; ?>
            
            <button class="btn-nuovo-piano">Crea Nuovo Piano</button>
        </div>
    </div>
</body>
</html>
