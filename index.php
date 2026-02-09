<?php // filepath: /workspaces/codespaces-blank/index.php ?>
<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Home - Materie</title>
	<style>
		.container{max-width:900px;margin:40px auto;font-family:Arial,Helvetica,sans-serif;padding:0 16px}
		.card{border:1px solid #e1e1e1;padding:18px;border-radius:8px;margin:12px 0;background:#fff}
		.btn{display:inline-block;padding:8px 14px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:6px}
		.btn:hover{background:#084db9}
	</style>
</head>
<body>
	<div class="container">
		<h1>Materie</h1>

		<!-- Sezione Informatica -->
		<div class="card" id="informatica">
			<h2>Informatica</h2>
			<p>Progetti e risorse di informatica.</p>
			<!-- Bottone jwt login: modifica l'href se il progetto è in un percorso diverso -->
			<a id="jwt-login" class="btn" href="/homeworkout/">jwt login</a>
		</div>

		<!-- ...altre materie... -->
	</div>
</body>
</html>
