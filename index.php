<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured content for carousel
$featured_content = getFeaturedContent();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akademia 360 - Liderazgo y Comunicación Asertiva</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="logo">
                    <img src="img/logo-akademia360.png" alt="Akademia 360" onerror="this.style.display='none'">
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">INICIO</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/nosotros.php">NOSOTROS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/calendario_cursos.php">CURSOS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/relatores.php">RELATORES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/noticias.php">NOTICIAS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/contacto.php">CONTACTO</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Carousel Section -->
        <section id="hero-carousel" class="hero-section">
            <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($featured_content as $index => $item): ?>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                            class="<?php echo $index === 0 ? 'active' : ''; ?>"></button>
                    <?php endforeach; ?>
                </div>
                
                <div class="carousel-inner">
                    <?php foreach ($featured_content as $index => $item): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="carousel-content">
                            <div class="container">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div class="carousel-text">
                                            <h1 class="carousel-title"><?php echo htmlspecialchars($item['title']); ?></h1>
                                            <p class="carousel-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                            <?php if ($item['type'] === 'curso'): ?>
                                                <div class="relator-info">
                                                    <div class="relator-avatar">
                                                        <img src="<?php echo htmlspecialchars($item['relator_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['relator_name']); ?>">
                                                    </div>
                                                    <div class="relator-details">
                                                        <h4><?php echo htmlspecialchars($item['relator_name']); ?></h4>
                                                        <p><?php echo htmlspecialchars($item['relator_title']); ?></p>
                                                        <p><?php echo htmlspecialchars($item['relator_university']); ?></p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="carousel-actions">
                                                                                        <?php if ($item['type'] === 'curso'): ?>
                                            <a href="pages/curso.php?id=<?php echo $item['content_id']; ?>" class="btn btn-primary">Inscríbete ahora</a>
                                        <?php elseif ($item['type'] === 'noticia'): ?>
                                            <a href="pages/noticia.php?id=<?php echo $item['content_id']; ?>" class="btn btn-outline-primary">Leer más</a>
                                        <?php elseif ($item['type'] === 'servicio'): ?>
                                            <a href="pages/servicio.php?id=<?php echo $item['content_id']; ?>" class="btn btn-primary">Conoce más</a>
                                        <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="carousel-image">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </section>

        <!-- About Us Section -->
        <section id="nosotros" class="about-section py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="about-image">
                            <img src="img/Default-image.jpeg" alt="Akademia 360" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-content">
                            <h2 class="section-title">Somos Akademia 360</h2>
                            <p class="about-text">
                                Somos un espacio de preparación y formación para emprendedores, profesionales y empresas 
                                que buscan potenciar sus habilidades técnicas y/o de gestión. Nuestros especialistas 
                                ofrecen una amplia gama de herramientas, estrategias y consejos para el crecimiento continuo.
                            </p>
                            <p class="about-text">
                                ¡Sé parte de la comunidad Akademia 360! Cientos de personas ya se han unido y se benefician 
                                del apoyo, herramientas y estrategias necesarias para el éxito en 2024.
                            </p>
                            <a href="pages/contacto.php" class="btn btn-primary">Únete a nosotros</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="servicios" class="services-section py-5 bg-light">
            <div class="container">
                <h2 class="section-title text-center mb-5">Nuestros Servicios</h2>
                <div class="row">
                    <?php
                    $services = getAllServices();
                    foreach ($services as $service):
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card">
                            <?php if ($service['image']): ?>
                            <div class="service-image">
                                <img src="<?php echo htmlspecialchars($service['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($service['name']); ?>"
                                     class="img-fluid rounded">
                            </div>
                            <?php else: ?>
                            <div class="service-icon">
                                <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                            </div>
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <a href="pages/servicio.php?id=<?php echo $service['id']; ?>" class="btn btn-outline-primary">Leer más</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Courses Section -->
        <section id="cursos" class="courses-section py-5">
            <div class="container">
                <h2 class="section-title text-center mb-5">Nuestros Cursos</h2>
                <div class="row">
                    <?php
                    $courses = getRecentCourses(6);
                    foreach ($courses as $course):
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="course-card">
                            <div class="course-image">
                                <img src="<?php echo htmlspecialchars($course['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>">
                            </div>
                            <div class="course-content">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p><?php echo htmlspecialchars($course['short_description']); ?></p>
                                <div class="course-relator">
                                    <img src="<?php echo htmlspecialchars($course['relator_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($course['relator_name']); ?>" class="relator-thumb">
                                    <span><?php echo htmlspecialchars($course['relator_name']); ?></span>
                                </div>
                                <div class="course-actions">
                                    <a href="pages/curso.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Ver detalles</a>
                                    <a href="pages/contacto.php?curso=<?php echo $course['id']; ?>" class="btn btn-outline-primary">Contactar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4">
                    <a href="pages/calendario_cursos.php" class="btn btn-primary">Ver todos los cursos</a>
                </div>
            </div>
        </section>

        <!-- News Section -->
        <section id="noticias" class="news-section py-5 bg-light">
            <div class="container">
                <h2 class="section-title text-center mb-5">Últimas Noticias</h2>
                <div class="row">
                    <?php
                    $news = getRecentNews(3);
                    foreach ($news as $article):
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="news-card">
                            <div class="news-image">
                                <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($article['title']); ?>">
                            </div>
                            <div class="news-content">
                                <div class="news-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                                </div>
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <p><?php echo htmlspecialchars($article['summary']); ?></p>
                                <a href="pages/noticia.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary">Leer más</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4">
                    <a href="pages/noticias.php" class="btn btn-primary">Ver todas las noticias</a>
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
                            <img src="img/logo-akademia360.png" alt="Akademia 360" style="height: 30px; width: auto;">
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
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html>