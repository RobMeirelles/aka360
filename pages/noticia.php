<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get news ID from URL parameter
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Retrieve news data from database
$news = getNewsById($news_id);

// Redirect to news page if article not found
if (!$news) {
    header('Location: noticias.php');
    exit;
}

// Get related news articles for sidebar
$related_news = getRecentNews(3);
$related_news = array_filter($related_news, function($n) use ($news_id) {
    return $n['id'] != $news_id;
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - Akademia 360</title>
    
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
        <!-- News Details Section -->
        <section class="news-details py-5">
            <div class="container">
                <div class="row">
                    <!-- News Content -->
                    <div class="col-lg-8">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="noticias.php">Noticias</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($news['title']); ?></li>
                            </ol>
                        </nav>

                        <article class="news-article">
                            <header class="news-header mb-4">
                                <h1 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h1>
                                
                                <div class="news-meta">
                                    <div class="news-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo formatDate($news['created_at']); ?>
                                    </div>
                                    
                                    <?php if ($news['author']): ?>
                                    <div class="news-author">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($news['author']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($news['updated_at'] && $news['updated_at'] != $news['created_at']): ?>
                                    <div class="news-updated">
                                        <i class="fas fa-edit"></i>
                                        Actualizado: <?php echo formatDate($news['updated_at']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <?php if ($news['image']): ?>
                            <div class="news-image mb-4">
                                <img src="<?php echo htmlspecialchars($news['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                     class="img-fluid rounded">
                            </div>
                            <?php endif; ?>

                            <div class="news-summary mb-4">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> Resumen</h5>
                                    <p class="mb-0"><?php echo htmlspecialchars($news['summary']); ?></p>
                                </div>
                            </div>

                            <div class="news-content">
                                <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                            </div>

                            <!-- Social Sharing -->
                            <div class="social-sharing mt-5">
                                <h5>Compartir esta noticia</h5>
                                <div class="social-buttons">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" class="btn btn-outline-primary me-2">
                                        <i class="fab fa-facebook"></i> Facebook
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($news['title']); ?>" 
                                       target="_blank" class="btn btn-outline-info me-2">
                                        <i class="fab fa-twitter"></i> Twitter
                                    </a>
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" class="btn btn-outline-secondary">
                                        <i class="fab fa-linkedin"></i> LinkedIn
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="news-sidebar">
                            <!-- Related News -->
                            <?php if (!empty($related_news)): ?>
                            <div class="related-news mb-4">
                                <h4>Noticias Relacionadas</h4>
                                <?php foreach (array_slice($related_news, 0, 3) as $related_item): ?>
                                <div class="related-news-item mb-3">
                                    <h6>
                                        <a href="noticia.php?id=<?php echo $related_item['id']; ?>">
                                            <?php echo htmlspecialchars($related_item['title']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo formatDate($related_item['created_at']); ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Quick Links -->
                            <div class="quick-links">
                                <h4>Enlaces Rápidos</h4>
                                <ul class="list-unstyled">
                                    <li><a href="calendario_cursos.php"><i class="fas fa-calendar"></i> Calendario de Cursos</a></li>
                                    <li><a href="contacto.php"><i class="fas fa-envelope"></i> Contacto</a></li>
                                    <li><a href="../index.php#nosotros"><i class="fas fa-info-circle"></i> Sobre Nosotros</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
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