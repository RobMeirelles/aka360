<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Obtener información de relatores para mostrar en la página
$relators = getAllRelators();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - Akademia 360</title>
    
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
                        <a class="nav-link active" href="nosotros.php">NOSOTROS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendario_cursos.php">CURSOS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatores.php">RELATORES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="noticias.php">NOTICIAS</a>
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
        <!-- Hero Section -->
        <section class="hero-section py-5" style="background: var(--gradient-primary);">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center text-white">
                        <h1 class="display-4 fw-bold mb-4">Sobre Akademia 360</h1>
                        <p class="lead">Transformando la formación profesional con excelencia y compromiso</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <h2 class="section-title mb-4">Nuestra Misión</h2>
                        <p class="lead mb-4">En Akademia 360, nos dedicamos a formar líderes y profesionales de excelencia, 
                        proporcionando herramientas y conocimientos que impulsen el crecimiento personal y empresarial.</p>
                        
                        <div class="mission-values">
                            <div class="value-item mb-3">
                                <h5><i class="fas fa-star text-primary"></i> Excelencia</h5>
                                <p>Comprometidos con la más alta calidad en todos nuestros programas y servicios.</p>
                            </div>
                            
                            <div class="value-item mb-3">
                                <h5><i class="fas fa-users text-primary"></i> Innovación</h5>
                                <p>Utilizamos metodologías modernas y tecnología avanzada para el aprendizaje.</p>
                            </div>
                            
                            <div class="value-item mb-3">
                                <h5><i class="fas fa-handshake text-primary"></i> Compromiso</h5>
                                <p>Nos comprometemos con el éxito de nuestros participantes y sus organizaciones.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="about-image">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80" 
                                 alt="Equipo Akademia 360" class="img-fluid rounded shadow">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="services-section py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center mb-5">
                        <h2 class="section-title">Nuestros Servicios</h2>
                        <p class="section-subtitle">Ofrecemos una amplia gama de servicios para el desarrollo profesional</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card text-center p-4 bg-white rounded shadow h-100">
                            <div class="service-icon mb-3">
                                <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                            </div>
                            <h4>Cursos Especializados</h4>
                            <p>Programas diseñados para desarrollar habilidades específicas en diferentes áreas profesionales.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card text-center p-4 bg-white rounded shadow h-100">
                            <div class="service-icon mb-3">
                                <i class="fas fa-chalkboard-teacher fa-3x text-primary"></i>
                            </div>
                            <h4>Capacitación Empresarial</h4>
                            <p>Soluciones de formación adaptadas a las necesidades específicas de cada organización.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card text-center p-4 bg-white rounded shadow h-100">
                            <div class="service-icon mb-3">
                                <i class="fas fa-certificate fa-3x text-primary"></i>
                            </div>
                            <h4>Certificaciones</h4>
                            <p>Programas que otorgan certificaciones reconocidas en el mercado laboral.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center mb-5">
                        <h2 class="section-title">Nuestro Equipo</h2>
                        <p class="section-subtitle">Conoce a los profesionales que hacen posible nuestra misión</p>
                    </div>
                </div>
                
                <div class="row">
                    <?php if (!empty($relators)): ?>
                        <?php foreach (array_slice($relators, 0, 6) as $relator): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="team-card text-center p-4 bg-white rounded shadow h-100">
                                <?php if ($relator['image']): ?>
                                <div class="team-image mb-3">
                                    <img src="<?php echo htmlspecialchars($relator['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($relator['name']); ?>" 
                                         class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                </div>
                                <?php else: ?>
                                <div class="team-image mb-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 120px; height: 120px;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <h5><?php echo htmlspecialchars($relator['name']); ?></h5>
                                <?php if ($relator['title']): ?>
                                <p class="text-muted"><?php echo htmlspecialchars($relator['title']); ?></p>
                                <?php endif; ?>
                                <?php if ($relator['university']): ?>
                                <p class="text-muted small"><?php echo htmlspecialchars($relator['university']); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($relator['biography']): ?>
                                <p class="small"><?php echo htmlspecialchars(substr($relator['biography'], 0, 100)) . '...'; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="no-team">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h3>Equipo en Construcción</h3>
                                <p class="text-muted">Pronto tendremos información sobre nuestro equipo de profesionales.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section py-5 bg-primary text-white">
            <div class="container">
                <div class="row text-center">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-item">
                            <h3 class="display-4 fw-bold">500+</h3>
                            <p>Participantes Formados</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-item">
                            <h3 class="display-4 fw-bold">50+</h3>
                            <p>Cursos Impartidos</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-item">
                            <h3 class="display-4 fw-bold">95%</h3>
                            <p>Satisfacción de Clientes</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-item">
                            <h3 class="display-4 fw-bold">5+</h3>
                            <p>Años de Experiencia</p>
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