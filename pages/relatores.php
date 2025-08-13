<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Obtener todos los relatores
$relators = getAllRelators();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatores - Akademia 360</title>
    
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
                        <a class="nav-link active" href="relatores.php">RELATORES</a>
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
                        <h1 class="display-4 fw-bold mb-4">Nuestros Relatores</h1>
                        <p class="lead">Conoce a los expertos profesionales que imparten nuestros cursos</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Relators Section -->
        <section class="relators-section py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Relatores</li>
                            </ol>
                        </nav>
                        
                        <div class="section-header text-center mb-5">
                            <h2 class="section-title">Expertos en Formación Profesional</h2>
                            <p class="section-subtitle">Nuestros relatores cuentan con amplia experiencia y reconocimiento en sus áreas de especialización</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php if (!empty($relators)): ?>
                        <?php foreach ($relators as $relator): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="relator-card h-100 bg-white rounded shadow">
                                <div class="relator-header p-4 text-center">
                                    <?php if ($relator['image']): ?>
                                    <div class="relator-image mb-3">
                                        <img src="<?php echo htmlspecialchars($relator['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($relator['name']); ?>" 
                                             class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                    </div>
                                    <?php else: ?>
                                    <div class="relator-image mb-3">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                             style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-4x text-muted"></i>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <h4 class="relator-name"><?php echo htmlspecialchars($relator['name']); ?></h4>
                                    <?php if ($relator['title']): ?>
                                    <p class="relator-title text-primary mb-2"><?php echo htmlspecialchars($relator['title']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($relator['university']): ?>
                                    <p class="relator-university text-muted small"><?php echo htmlspecialchars($relator['university']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="relator-content p-4">
                                    <?php if ($relator['biography']): ?>
                                    <div class="relator-bio mb-3">
                                        <h6><i class="fas fa-info-circle text-primary"></i> Biografía</h6>
                                        <p class="small"><?php echo htmlspecialchars($relator['biography']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($relator['specialization']): ?>
                                    <div class="relator-specialties mb-3">
                                        <h6><i class="fas fa-star text-primary"></i> Especialización</h6>
                                        <p class="small"><?php echo htmlspecialchars($relator['specialization']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($relator['university']): ?>
                                    <div class="relator-university mb-3">
                                        <h6><i class="fas fa-university text-primary"></i> Universidad</h6>
                                        <p class="small"><?php echo htmlspecialchars($relator['university']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="relator-actions text-center">
                                        <a href="contacto.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-envelope"></i> Contactar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="no-relators">
                                <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                                <h3>Relatores en Construcción</h3>
                                <p class="text-muted">Pronto tendremos información sobre nuestros relatores expertos.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Why Choose Our Relators Section -->
        <section class="why-relators-section py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center mb-5">
                        <h2 class="section-title">¿Por qué elegir a nuestros relatores?</h2>
                        <p class="section-subtitle">Características que distinguen a nuestro equipo de profesionales</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-card text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                            </div>
                            <h5>Formación Académica</h5>
                            <p class="small">Todos nuestros relatores cuentan con formación académica sólida y reconocida.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-card text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-briefcase fa-3x text-primary"></i>
                            </div>
                            <h5>Experiencia Práctica</h5>
                            <p class="small">Amplia experiencia en el campo profesional y empresarial.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-card text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-chalkboard-teacher fa-3x text-primary"></i>
                            </div>
                            <h5>Habilidades Pedagógicas</h5>
                            <p class="small">Capacidad para transmitir conocimientos de manera efectiva y clara.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-card text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-award fa-3x text-primary"></i>
                            </div>
                            <h5>Reconocimiento Profesional</h5>
                            <p class="small">Reconocidos en sus respectivas áreas de especialización.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact CTA Section -->
        <section class="cta-section py-5 bg-primary text-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center">
                        <h3 class="mb-4">¿Interesado en nuestros cursos?</h3>
                        <p class="lead mb-4">Nuestros relatores están listos para compartir su conocimiento contigo. 
                        Contáctanos para más información sobre nuestros programas.</p>
                        <a href="contacto.php" class="btn btn-light btn-lg">
                            <i class="fas fa-envelope"></i> Contactar Ahora
                        </a>
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