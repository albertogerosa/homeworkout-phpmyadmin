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
        .zoomable-img {
            cursor: zoom-in;
            max-height: 500px;
            transition: transform 0.2s;
        }
        .zoomable-img.zoomed {
            cursor: zoom-out;
        }
        .zoom-container {
            max-height: 600px;
            overflow: auto;
            position: relative;
        }
    </style>
</head>
<body class="container py-5">
    <h1 class="mb-4">Progetti di GPO</h1>
    <div class="projects-container">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="project-card">
                    <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#diagrammaClassiModal">schema ER</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="project-card">
                    <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#diagrammaCasiUsoModal">diagramma casi d'uso</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="project-card">
                    <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#diagrammaDiClassiModal">diagramma classi</button>
                </div>
            </div>
        </div>
        <div class="project-card">
            <a class="btn btn-outline-primary btn-lg w-100" href="progetto/gpo/manuale_homeworkout.php">Manuale Utente Homeworkout</a>
        </div>
        <div class="project-card">
            <a class="btn btn-outline-success btn-lg w-100" href="progetto/informatica/homeworkout_mockup.php">Documentazione Tecnica Homeworkout (Rotte e API)</a>
        </div>
        <div class="project-card">
            <a class="btn btn-outline-info btn-lg w-100" href="/homeworkout/">Applicazione Homeworkout</a>
        </div>
        <div class="project-card">
            <a class="btn btn-outline-dark btn-lg w-100" href="progetto/gpo/descrizione_applicazione_homeworkout.php">descrizone applicazione homeworkout</a>
        </div>
    </div>
    <a href="../home.php" class="btn btn-secondary mt-3">Torna indietro</a>

    <!-- Modal Diagramma Classi -->
    <div class="modal fade" id="diagrammaClassiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schema ER</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center zoom-container">
                    <img id="erImg" src="progetto/gpo/diagramma classi/er.png" alt="Diagramma delle Classi" class="img-fluid zoomable-img">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Diagramma Casi d'Uso -->
    <div class="modal fade" id="diagrammaCasiUsoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diagramma Casi d'Uso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center zoom-container">
                    <img id="casiUsoImg" src="progetto/gpo/diagramma classi/casi_uso.png" alt="Diagramma Casi d'Uso" class="img-fluid zoomable-img">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Diagramma Classi (da Casi d'Uso) -->
    <div class="modal fade" id="diagrammaDiClassiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diagramma Classi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center zoom-container">
                    <img id="classiImg" src="progetto/gpo/diagramma classi/classi.png" alt="Diagramma Classi" class="img-fluid zoomable-img">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function enableZoomForImage(imgId) {
            let zoomLevel = 1;
            const img = document.getElementById(imgId);
            
            if (img) {
                // Zoom con scroll
                img.addEventListener('wheel', (e) => {
                    e.preventDefault();
                    zoomLevel += (e.deltaY > 0 ? -0.1 : 0.1);
                    zoomLevel = Math.max(1, Math.min(zoomLevel, 3));
                    img.style.transform = `scale(${zoomLevel})`;
                    img.classList.toggle('zoomed', zoomLevel > 1);
                });
                
                // Reset zoom al doppio click
                img.addEventListener('dblclick', () => {
                    zoomLevel = 1;
                    img.style.transform = 'scale(1)';
                    img.classList.remove('zoomed');
                });
                
                // Zoom al click singolo
                img.addEventListener('click', () => {
                    if (zoomLevel === 1) {
                        zoomLevel = 2;
                        img.style.transform = `scale(${zoomLevel})`;
                        img.classList.add('zoomed');
                    } else if (zoomLevel === 2) {
                        zoomLevel = 1;
                        img.style.transform = 'scale(1)';
                        img.classList.remove('zoomed');
                    }
                });
            }
        }
        
        // Abilita zoom per tutte le immagini
        enableZoomForImage('erImg');
        enableZoomForImage('casiUsoImg');
        enableZoomForImage('classiImg');
    </script>
</body>
</html>
