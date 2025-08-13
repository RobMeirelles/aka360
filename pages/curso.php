<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Obtener el ID del curso desde la URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener los datos del curso
$course = getCourseById($course_id);

// Si no se encuentra el curso, redirigir a la página principal
if (!$course) {
    header('Location: ../index.php');
    exit;
}

// Obtener cursos relacionados (misma categoría)
$related_courses = getRecentCourses(3);
$related_courses = array_filter($related_courses, function($c) use ($course_id) {
    return $c['id'] != $course_id;
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Akademia 360</title>
    
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
                        <a class="nav-link" href="../index.php#noticias">NOTICIAS</a>
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
        <!-- Course Details Section -->
        <section class="course-details py-5">
            <div class="container">
                <div class="row">
                    <!-- Course Information -->
                    <div class="col-lg-8">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="calendario_cursos.php">Cursos</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
                            </ol>
                        </nav>

                        <div class="course-header mb-4">
                            <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                            <div class="course-meta">
                                <span class="badge bg-primary me-2"><?php echo htmlspecialchars($course['category']); ?></span>
                                <span class="text-muted">Código: <?php echo htmlspecialchars($course['code']); ?></span>
                            </div>
                        </div>

                        <!-- Imagen principal del curso -->
                        <?php if ($course['image']): ?>
                        <div class="course-main-image mb-4">
                            <img src="<?php echo htmlspecialchars($course['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                 class="img-fluid rounded shadow">
                        </div>
                        <?php endif; ?>

                        <div class="course-description mb-4">
                            <h3>Descripción</h3>
                            <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        </div>

                        <?php if ($course['objectives']): ?>
                        <div class="course-objectives mb-4">
                            <h3>Objetivos</h3>
                            <p><?php echo nl2br(htmlspecialchars($course['objectives'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($course['detailed_program']): ?>
                        <div class="course-program mb-4">
                            <h3>Programa Detallado</h3>
                            <div class="program-content">
                                <?php echo nl2br(htmlspecialchars($course['detailed_program'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Course Sidebar -->
                    <div class="col-lg-4">
                        <div class="course-sidebar">
                            <!-- Relator Information -->
                            <?php if ($course['relator_name']): ?>
                            <div class="relator-card mb-4">
                                <h4>Relator</h4>
                                <div class="relator-info">
                                    <?php if ($course['relator_image']): ?>
                                    <img src="<?php echo htmlspecialchars($course['relator_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($course['relator_name']); ?>" 
                                         class="relator-avatar">
                                    <?php endif; ?>
                                    <div class="relator-details">
                                        <h5><?php echo htmlspecialchars($course['relator_name']); ?></h5>
                                        <?php if ($course['relator_title']): ?>
                                        <p class="text-muted"><?php echo htmlspecialchars($course['relator_title']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($course['relator_university']): ?>
                                        <p class="text-muted"><?php echo htmlspecialchars($course['relator_university']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($course['relator_biography']): ?>
                                        <p class="relator-bio"><?php echo htmlspecialchars($course['relator_biography']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Course Actions -->
                            <div class="course-actions mb-4">
                                <div class="price-info mb-3">
                                    <h4>Precio</h4>
                                    <div class="price">
                                    <?php if ($course['price'] !== null && $course['price'] !== ''): ?>
                                        $<?php echo number_format($course['price'], 0, ',', '.'); ?> CLP
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Cotizable</span>
                                    <?php endif; ?>
                                </div>
                                </div>

                                <div class="action-buttons">
                                    <?php if ($course['payment_link']): ?>
                                    <a href="<?php echo htmlspecialchars($course['payment_link']); ?>" 
                                       class="btn btn-primary btn-lg w-100 mb-2" target="_blank">
                                        <i class="fas fa-credit-card"></i> Pagar Ahora
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="contacto.php?curso=<?php echo $course['id']; ?>" 
                                       class="btn btn-outline-primary btn-lg w-100">
                                        <i class="fas fa-envelope"></i> Contactar
                                    </a>
                                </div>
                            </div>

                            <!-- Course Details -->
                            <div class="course-details-info">
                                <h4>Información del Curso</h4>
                                <ul class="list-unstyled">
                                    <li><strong>Categoría:</strong> <?php echo htmlspecialchars($course['category']); ?></li>
                                    <li><strong>Código:</strong> <?php echo htmlspecialchars($course['code']); ?></li>
                                    <li><strong>Fecha de Creación:</strong> <?php echo formatDate($course['created_at']); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Courses -->
                <?php if (!empty($related_courses)): ?>
                <div class="related-courses mt-5">
                    <h3>Cursos Relacionados</h3>
                    <div class="row">
                        <?php foreach (array_slice($related_courses, 0, 3) as $related_course): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if ($related_course['image']): ?>
                                <img src="<?php echo htmlspecialchars($related_course['image']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($related_course['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-graduation-cap fa-3x text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($related_course['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($related_course['short_description']); ?></p>
                                    <div class="course-meta">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($related_course['category']); ?></span>
                                        <span class="price">
                                            <?php if ($related_course['price'] !== null && $related_course['price'] !== ''): ?>
                                                $<?php echo number_format($related_course['price'], 0, ',', '.'); ?> CLP
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Cotizable</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="curso.php?id=<?php echo $related_course['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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