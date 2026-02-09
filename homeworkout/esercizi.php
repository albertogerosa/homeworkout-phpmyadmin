<?php
session_start();
require_once 'database.php';

// Verifica autenticazione
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit;
}

$messaggio = "";
$tipo_messaggio = "";

// Gestione aggiunta esercizio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['azione']) && $_POST['azione'] == 'aggiungi') {
    $nome = trim($_POST['nome'] ?? '');
    $descrizione = trim($_POST['descrizione'] ?? '');
    $gruppo_muscolare = $_POST['gruppo_muscolare'] ?? '';
    $livello = $_POST['livello'] ?? '';
    
    if (empty($nome) || empty($gruppo_muscolare) || empty($livello)) {
        $messaggio = "Tutti i campi sono obbligatori";
        $tipo_messaggio = "error";
    } else {
        try {
            $sql = "INSERT INTO esercizi (nome, descrizione, gruppo_muscolare, livello) 
                    VALUES (:nome, :descrizione, :gruppo_muscolare, :livello)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':descrizione' => $descrizione,
                ':gruppo_muscolare' => $gruppo_muscolare,
                ':livello' => $livello
            ]);
            $messaggio = "Esercizio aggiunto con successo!";
            $tipo_messaggio = "success";
        } catch(PDOException $e) {
            $messaggio = "Errore: " . $e->getMessage();
            $tipo_messaggio = "error";
        }
    }
}

// Recupera tutti gli esercizi
try {
    $sql = "SELECT * FROM esercizi ORDER BY gruppo_muscolare, nome";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $esercizi = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Errore nel recupero degli esercizi: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esercizi - HomeWorkout</title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .messaggio {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .messaggio.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .messaggio.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .form-section h2 {
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .esercizi-list {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .esercizi-list h2 {
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .esercizio-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        
        .esercizio-item h3 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .esercizio-info {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .badge-muscolo {
            background-color: #e7f3ff;
            color: #0066cc;
        }
        
        .badge-livello {
            background-color: #fff0e7;
            color: #cc6600;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            background: #666;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>Esercizi - HomeWorkout</h1>
            <a href="dashboard.php" class="back-btn">Torna alla Dashboard</a>
        </div>
    </header>
    
    <div class="container">
        <?php if ($messaggio): ?>
            <div class="messaggio <?php echo $tipo_messaggio; ?>">
                <?php echo htmlspecialchars($messaggio); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <h2>Aggiungi Nuovo Esercizio</h2>
            <form method="POST">
                <input type="hidden" name="azione" value="aggiungi">
                
                <div class="form-group">
                    <label for="nome">Nome Esercizio</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="descrizione">Descrizione</label>
                    <textarea id="descrizione" name="descrizione"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="gruppo_muscolare">Gruppo Muscolare</label>
                    <select id="gruppo_muscolare" name="gruppo_muscolare" required>
                        <option value="">Seleziona un gruppo</option>
                        <option value="Petto">Petto</option>
                        <option value="Schiena">Schiena</option>
                        <option value="Spalle">Spalle</option>
                        <option value="Bicipiti">Bicipiti</option>
                        <option value="Tricipiti">Tricipiti</option>
                        <option value="Gambe">Gambe</option>
                        <option value="Addominali">Addominali</option>
                        <option value="Cardio">Cardio</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="livello">Livello Difficoltà</label>
                    <select id="livello" name="livello" required>
                        <option value="">Seleziona livello</option>
                        <option value="facile">Facile</option>
                        <option value="medio">Medio</option>
                        <option value="difficile">Difficile</option>
                    </select>
                </div>
                
                <button type="submit">Aggiungi Esercizio</button>
            </form>
        </div>
        
        <div class="esercizi-list">
            <h2>Esercizi Disponibili</h2>
            
            <?php if (count($esercizi) > 0): ?>
                <?php foreach ($esercizi as $esercizio): ?>
                    <div class="esercizio-item">
                        <h3><?php echo htmlspecialchars($esercizio['nome']); ?></h3>
                        <?php if (!empty($esercizio['descrizione'])): ?>
                            <p><?php echo htmlspecialchars($esercizio['descrizione']); ?></p>
                        <?php endif; ?>
                        <div class="esercizio-info">
                            <span class="badge badge-muscolo"><?php echo htmlspecialchars($esercizio['gruppo_muscolare']); ?></span>
                            <span class="badge badge-livello"><?php echo ucfirst($esercizio['livello']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nessun esercizio disponibile. Aggiungine uno per iniziare!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
