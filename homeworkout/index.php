<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeWorkout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        
        .sottotitolo {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 30px;
        }
        
        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        a {
            display: inline-block;
            padding: 15px 40px;
            font-size: 1.1em;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: bold;
            cursor: pointer;
        }
        
        .btn-login {
            background-color: #667eea;
            color: white;
        }
        
        .btn-login:hover {
            background-color: #5568d3;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-register {
            background-color: #764ba2;
            color: white;
        }
        
        .btn-register:hover {
            background-color: #63408f;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>HomeWorkout</h1>
        <p class="sottotitolo">Il tuo allenamento personalizzato da casa</p>
        <div class="button-group">
            <a href="register.php" class="btn-register">Registrati</a>
            <a href="login.php" class="btn-login">Login</a>
        </div>
    </div>
</body>
</html>
