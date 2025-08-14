<?php
require_once '../includes/functions.php';

// Get current year and month
$current_year = date('Y');
$current_month = date('n');

// Get year and month from URL parameters if specified
$year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
$month = isset($_GET['month']) ? (int)$_GET['month'] : $current_month;

// Get courses with specific dates for the selected month
$cursos_con_fechas = getCoursesByMonth($year, $month);

// Get quotable courses (without specific dates)
$cursos_cotizables = getQuotableCourses();

// Get all courses for general display
$todos_los_cursos = getAllCoursesByDate();

// Traducción de meses
$meses_es = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$mes_actual = $meses_es[$month] . ' ' . $year;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Cursos - Akademia 360</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        :root {
            --negro: #000000;
            --turquesa: #03cbc5;
            --gris-oscuro: #414545;
            --rojo: #f8415b;
            --gris-profundo: #192121;
            --white: #ffffff;
        }
        
        .calendar-header {
            background: linear-gradient(135deg, var(--turquesa), var(--rojo));
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
        }
        
        .month-section {
            margin-bottom: 3rem;
        }
        
        .month-title {
            background: #f8f9fa;
            padding: 1rem;
            border-left: 4px solid var(--turquesa);
            margin-bottom: 2rem;
            border-radius: 0 8px 8px 0;
        }
        
        .course-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .course-image {
            height: 200px;
            background: linear-gradient(45deg, var(--turquesa), var(--rojo));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .course-content {
            padding: 1.5rem;
        }
        
        .course-date {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .course-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .course-description {
            color: #6c757d;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .course-relator {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .relator-thumb {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        .course-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--rojo);
            border-color: var(--rojo);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--gris-profundo);
            border-color: var(--gris-profundo);
            color: var(--white);
        }
        
        .btn-outline-primary {
            color: var(--white);
            border-color: var(--rojo);
            background: var(--rojo);
        }
        
        .btn-outline-primary:hover {
            background: var(--gris-profundo);
            border-color: var(--gris-profundo);
            color: var(--white);
        }
        
        .no-courses {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .no-courses i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg">
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
                            <a class="nav-link active" href="calendario_cursos.php">CURSOS</a>
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
        </nav>
    </header>

    <!-- Calendar Header -->
    <section class="calendar-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="fas fa-calendar-alt me-3"></i>
                        Calendario de Cursos
                    </h1>
                    <p class="lead mb-0">
                        Descubre todos nuestros cursos organizados cronológicamente. 
                        Encuentra el programa perfecto para tu desarrollo profesional.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Calendar Content -->
    <main class="container">
        <!-- Navegación de meses -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="?year=<?php echo $month == 1 ? $year - 1 : $year; ?>&month=<?php echo $month == 1 ? 12 : $month - 1; ?>" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-chevron-left"></i> Mes anterior
                    </a>
                    <h2 class="mb-0"><?php echo $mes_actual; ?></h2>
                    <a href="?year=<?php echo $month == 12 ? $year + 1 : $year; ?>&month=<?php echo $month == 12 ? 1 : $month + 1; ?>" 
                       class="btn btn-outline-primary">
                        Mes siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Cursos con fechas para este mes -->
        <?php if (!empty($cursos_con_fechas)): ?>
            <div class="month-section">
                <div class="month-title">
                    <h3 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Cursos Programados para <?php echo $mes_actual; ?>
                    </h3>
                </div>
                
                <div class="row">
                    <?php foreach ($cursos_con_fechas as $curso): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="course-card">
                                <div class="course-image">
                                    <?php if ($curso['image']): ?>
                                        <img src="<?php echo htmlspecialchars($curso['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($curso['title']); ?>" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-graduation-cap"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="course-content">
                                    <div class="course-date">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($curso['start_date'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($curso['end_date'])); ?>
                                    </div>
                                    <h3 class="course-title"><?php echo htmlspecialchars($curso['title']); ?></h3>
                                    <p class="course-description"><?php echo htmlspecialchars($curso['short_description']); ?></p>
                                    
                                    <?php if ($curso['relator_name']): ?>
                                        <div class="course-relator">
                                            <div class="relator-thumb">
                                                <?php if ($curso['relator_image']): ?>
                                                    <img src="<?php echo htmlspecialchars($curso['relator_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($curso['relator_name']); ?>"
                                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                <?php else: ?>
                                                    <?php echo substr($curso['relator_name'], 0, 1); ?>
                                                <?php endif; ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($curso['relator_name']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="course-actions">
                                        <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i> Ver detalles
                                        </a>
                                        <a href="contacto.php?curso=<?php echo $curso['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-envelope me-1"></i> Contactar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No hay cursos programados para <?php echo $mes_actual; ?>. 
                <a href="#cursos-cotizables" class="alert-link">Ver cursos cotizables</a>
            </div>
        <?php endif; ?>

        <!-- Cursos cotizables -->
        <?php if (!empty($cursos_cotizables)): ?>
            <div class="month-section" id="cursos-cotizables">
                <div class="month-title">
                    <h3 class="mb-0">
                        <i class="fas fa-quote-right me-2"></i>
                        Cursos Cotizables
                    </h3>
                    <p class="text-muted mb-0">Estos cursos se pueden programar según tus necesidades</p>
                </div>
                
                <div class="row">
                    <?php foreach ($cursos_cotizables as $curso): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="course-card">
                                <div class="course-image">
                                    <?php if ($curso['image']): ?>
                                        <img src="<?php echo htmlspecialchars($curso['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($curso['title']); ?>" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-graduation-cap"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="course-content">
                                    <div class="course-date">
                                        <i class="fas fa-clock me-1"></i>
                                        <span class="badge bg-warning">Cotizable</span>
                                    </div>
                                    <h3 class="course-title"><?php echo htmlspecialchars($curso['title']); ?></h3>
                                    <p class="course-description"><?php echo htmlspecialchars($curso['short_description']); ?></p>
                                    
                                    <?php if ($curso['relator_name']): ?>
                                        <div class="course-relator">
                                            <div class="relator-thumb">
                                                <?php if ($curso['relator_image']): ?>
                                                    <img src="<?php echo htmlspecialchars($curso['relator_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($curso['relator_name']); ?>"
                                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                <?php else: ?>
                                                    <?php echo substr($curso['relator_name'], 0, 1); ?>
                                                <?php endif; ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($curso['relator_name']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="course-actions">
                                        <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i> Ver detalles
                                        </a>
                                        <a href="contacto.php?curso=<?php echo $curso['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-quote-right me-1"></i> Solicitar cotización
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Todos los cursos -->
        <div class="month-section">
            <div class="month-title">
                <h3 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Todos los Cursos Disponibles
                </h3>
            </div>
            
            <div class="row">
                <?php foreach ($todos_los_cursos as $curso): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="course-card">
                            <div class="course-image">
                                <?php if ($curso['image']): ?>
                                    <img src="<?php echo htmlspecialchars($curso['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($curso['title']); ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-graduation-cap"></i>
                                <?php endif; ?>
                            </div>
                            <div class="course-content">
                                <div class="course-date">
                                    <?php if ($curso['has_dates']): ?>
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($curso['start_date'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($curso['end_date'])); ?>
                                    <?php elseif ($curso['is_quotable']): ?>
                                        <i class="fas fa-clock me-1"></i>
                                        <span class="badge bg-warning">Cotizable</span>
                                    <?php else: ?>
                                        <i class="fas fa-infinity me-1"></i>
                                        <span class="badge bg-info">Disponible</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="course-title"><?php echo htmlspecialchars($curso['title']); ?></h3>
                                <p class="course-description"><?php echo htmlspecialchars($curso['short_description']); ?></p>
                                
                                <?php if ($curso['relator_name']): ?>
                                    <div class="course-relator">
                                        <div class="relator-thumb">
                                            <?php if ($curso['relator_image']): ?>
                                                <img src="<?php echo htmlspecialchars($curso['relator_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($curso['relator_name']); ?>"
                                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                            <?php else: ?>
                                                <?php echo substr($curso['relator_name'], 0, 1); ?>
                                            <?php endif; ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($curso['relator_name']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="course-actions">
                                    <a href="curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Ver detalles
                                    </a>
                                    <?php if ($curso['is_quotable']): ?>
                                        <a href="contacto.php?curso=<?php echo $curso['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-quote-right me-1"></i> Cotizar
                                        </a>
                                    <?php else: ?>
                                        <a href="contacto.php?curso=<?php echo $curso['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-envelope me-1"></i> Contactar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-4 mt-5">
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
    <!-- Custom JS -->
    <script src="../js/main.js"></script>
</body>
</html> 