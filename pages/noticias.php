<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Retrieve all news articles for display
$news = getRecentNews(50); // Get up to 50 news articles
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias - Akademia 360</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <div class="logo">
                    <img src="../img/logo-akademia360.png" alt="Akademia 360" onerror="this.style.display='none'">
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">INICIO</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nosotros.php">NOSOTROS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendario_cursos.php">CURSOS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatores.php">RELATORES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="noticias.php">NOTICIAS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacto.php">CONTACTO</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="margin-top: 80px;">
        <!-- News Section -->
        <section class="news-section py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Noticias</li>
                            </ol>
                        </nav>
                        
                        <div class="section-header text-center mb-5">
                            <h1 class="section-title">Noticias y Actualizaciones</h1>
                            <p class="section-subtitle">Mantente informado sobre las últimas novedades en el mundo empresarial y legal</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php if (!empty($news)): ?>
                        <?php foreach ($news as $item): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="news-card h-100">
                                <?php if ($item['image']): ?>
                                <div class="news-image">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         class="img-fluid">
                                </div>
                                <?php endif; ?>
                                
                                <div class="news-content">
                                    <div class="news-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo formatDate($item['created_at']); ?>
                                    </div>
                                    
                                    <h3 class="news-title">
                                        <a href="noticia.php?id=<?php echo $item['id']; ?>">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="news-summary">
                                        <?php echo htmlspecialchars($item['summary']); ?>
                                    </p>
                                    
                                    <?php if ($item['author']): ?>
                                    <div class="news-author">
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($item['author']); ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="news-actions mt-3">
                                        <a href="noticia.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            Leer más
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="no-news">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                <h3>No hay noticias disponibles</h3>
                                <p class="text-muted">Pronto tendremos nuevas noticias para compartir contigo.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="footer-logo">
                        <div class="logo">
                            <img src="../img/logo-akademia360.png" alt="Akademia 360" style="height: 30px; width: auto;">
                        </div>
                    </div>
                    <p class="mt-3">Transforma tu forma de liderar y comunicar. Únete a nuestra comunidad de profesionales.</p>
                </div>
                <div class="col-lg-4">
                    <h5>Contacto</h5>
                    <p><i class="fas fa-envelope"></i> contacto@akademia360.cl</p>
                    <p><i class="fas fa-globe"></i> www.akademia360.cl</p>
                </div>
                <div class="col-lg-4">
                    <h5>Síguenos</h5>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2024 Akademia 360. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 