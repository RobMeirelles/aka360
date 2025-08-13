<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Simple authentication (you can enhance this later)
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true; // For demo purposes
}

// Get statistics
$mysqli = getDBConnection();
$stats = [];

// Count courses
$result = $mysqli->query("SELECT COUNT(*) as total FROM cursos WHERE activo = 1");
$stats['courses'] = $result->fetch_assoc()['total'];

// Count news
$result = $mysqli->query("SELECT COUNT(*) as total FROM noticias");
$stats['news'] = $result->fetch_assoc()['total'];

// Count relators
$result = $mysqli->query("SELECT COUNT(*) as total FROM relatores WHERE activo = 1");
$stats['relators'] = $result->fetch_assoc()['total'];

// Count services
$result = $mysqli->query("SELECT COUNT(*) as total FROM servicios WHERE activo = 1");
$stats['services'] = $result->fetch_assoc()['total'];

// Count featured content
$result = $mysqli->query("SELECT COUNT(*) as total FROM contenido_destacado WHERE activo = 1");
$stats['featured'] = $result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Akademia 360</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .admin-sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-card h3 {
            font-size: 2.5rem;
            margin: 0;
        }
        .admin-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .content-area {
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="admin-sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Akademia 360</h4>
                        <small class="text-white-50">Panel de Administración</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="cursos.php">
                            <i class="fas fa-graduation-cap"></i> Cursos
                        </a>
                        <a class="nav-link" href="noticias.php">
                            <i class="fas fa-newspaper"></i> Noticias
                        </a>
                        <a class="nav-link" href="relatores.php">
                            <i class="fas fa-user-tie"></i> Relatores
                        </a>
                        <a class="nav-link" href="servicios.php">
                            <i class="fas fa-briefcase"></i> Servicios
                        </a>
                        <a class="nav-link" href="carousel.php">
                            <i class="fas fa-images"></i> Carrusel
                        </a>
                        <a class="nav-link" href="contactos.php">
                            <i class="fas fa-envelope"></i> Contactos
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="content-area p-4">
                    <!-- Header -->
                    <div class="admin-header p-3 mb-4 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0">
                                <i class="fas fa-tachometer-alt text-primary"></i> Dashboard
                            </h2>
                            <div class="text-muted">
                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4 col-lg-2">
                            <div class="stats-card text-center">
                                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                <h3><?php echo $stats['courses']; ?></h3>
                                <small>Cursos Activos</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="stats-card text-center">
                                <i class="fas fa-newspaper fa-2x mb-2"></i>
                                <h3><?php echo $stats['news']; ?></h3>
                                <small>Noticias</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="stats-card text-center">
                                <i class="fas fa-user-tie fa-2x mb-2"></i>
                                <h3><?php echo $stats['relators']; ?></h3>
                                <small>Relatores</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="stats-card text-center">
                                <i class="fas fa-briefcase fa-2x mb-2"></i>
                                <h3><?php echo $stats['services']; ?></h3>
                                <small>Servicios</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="stats-card text-center">
                                <i class="fas fa-images fa-2x mb-2"></i>
                                <h3><?php echo $stats['featured']; ?></h3>
                                <small>Destacados</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt text-warning"></i> Acciones Rápidas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <a href="cursos.php?action=add" class="btn btn-primary w-100">
                                                <i class="fas fa-plus"></i> Agregar Curso
                                            </a>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <a href="noticias.php?action=add" class="btn btn-success w-100">
                                                <i class="fas fa-plus"></i> Agregar Noticia
                                            </a>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <a href="relatores.php?action=add" class="btn btn-info w-100">
                                                <i class="fas fa-plus"></i> Agregar Relator
                                            </a>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <a href="carousel.php?action=add" class="btn btn-warning w-100">
                                                <i class="fas fa-plus"></i> Agregar al Carrusel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-link text-primary"></i> Enlaces Útiles
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="../index.php" target="_blank" class="list-group-item list-group-item-action">
                                            <i class="fas fa-external-link-alt"></i> Ver Sitio Web
                                        </a>
                                        <a href="cursos.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-graduation-cap"></i> Gestionar Cursos
                                        </a>
                                        <a href="noticias.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-newspaper"></i> Gestionar Noticias
                                        </a>
                                        <a href="carousel.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-images"></i> Gestionar Carrusel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 