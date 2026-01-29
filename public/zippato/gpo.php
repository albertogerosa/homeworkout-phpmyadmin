<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Progetti GPO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .projects-container {
            margin: 30px 0;
        }
        .project-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="container py-5">
    <h1 class="mb-4">Progetti di GPO</h1>
    <div class="projects-container">
        <div class="project-card">
            <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#diagrammaClassiModal">Diagramma delle Classi del Sito</button>
        </div>
    </div>
    <a href="../home.php" class="btn btn-secondary mt-3">Torna indietro</a>

    <!-- Modal Diagramma Classi -->
    <div class="modal fade" id="diagrammaClassiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diagramma delle Classi del Sito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="IMG/classi.png" alt="Diagramma delle Classi" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
