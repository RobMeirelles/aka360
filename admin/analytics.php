<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';
require_once '../includes/analytics_functions.php';

// Require authentication for admin access
requireAuth();

// Verify user permissions for analytics access
if (!hasPermission('analytics_view')) {
    header('Location: index.php');
    exit;
}

// Get filter parameters from URL
$periodo = $_GET['periodo'] ?? '30';
$tipo_contenido = $_GET['tipo'] ?? '';

// Retrieve analytics statistics and data
$stats = getAnalyticsStats($periodo);
$chart_data = getChartData($periodo);
$content_metrics = getContentMetrics($tipo_contenido, 10);

// Get analytics configuration settings
$config = getAnalyticsConfig();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Panel de Administración - Akademia 360</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
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
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .period-selector {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
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
                        <?php echo getNavigationMenu(); ?>
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
                                <i class="fas fa-chart-line text-primary"></i> Analytics
                            </h2>
                            <div class="d-flex align-items-center">
                                <div class="text-muted me-3">
                                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i'); ?>
                                </div>
                                <div class="text-muted">
                                    <i class="fas fa-user"></i> 
                                    <?php 
                                    $currentUser = getCurrentUser();
                                    echo htmlspecialchars($currentUser['nombre_completo']); 
                                    ?>
                                    <small class="badge bg-primary ms-1"><?php echo ucfirst($currentUser['rol']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Period Selector -->
                    <div class="period-selector">
                        <form method="GET" class="row align-items-center">
                            <div class="col-md-3">
                                <label class="form-label">Período:</label>
                                <select name="periodo" class="form-select" onchange="this.form.submit()">
                                    <option value="7" <?php echo $periodo == '7' ? 'selected' : ''; ?>>Últimos 7 días</option>
                                    <option value="30" <?php echo $periodo == '30' ? 'selected' : ''; ?>>Últimos 30 días</option>
                                    <option value="90" <?php echo $periodo == '90' ? 'selected' : ''; ?>>Últimos 90 días</option>
                                    <option value="365" <?php echo $periodo == '365' ? 'selected' : ''; ?>>Último año</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Contenido:</label>
                                <select name="tipo" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    <option value="curso" <?php echo $tipo_contenido == 'curso' ? 'selected' : ''; ?>>Cursos</option>
                                    <option value="noticia" <?php echo $tipo_contenido == 'noticia' ? 'selected' : ''; ?>>Noticias</option>
                                    <option value="relator" <?php echo $tipo_contenido == 'relator' ? 'selected' : ''; ?>>Relatores</option>
                                    <option value="servicio" <?php echo $tipo_contenido == 'servicio' ? 'selected' : ''; ?>>Servicios</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    <a href="analytics.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-refresh"></i> Actualizar
                                    </a>
                                    <button type="button" class="btn btn-success" onclick="exportReport()">
                                        <i class="fas fa-download"></i> Exportar Reporte
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-eye fa-2x mb-2"></i>
                                <h3><?php echo number_format($stats['visitas_totales']); ?></h3>
                                <small>Visitas Totales</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h3><?php echo number_format($stats['usuarios_unicos']); ?></h3>
                                <small>Usuarios Únicos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h3><?php echo number_format($stats['sesiones']); ?></h3>
                                <small>Sesiones</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <h3><?php echo $stats['sesiones'] > 0 ? round($stats['visitas_totales'] / $stats['sesiones'], 1) : 0; ?></h3>
                                <small>Páginas/Sesión</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-lg-8">
                            <div class="chart-container">
                                <h5><i class="fas fa-chart-line text-primary"></i> Visitas por Día</h5>
                                <canvas id="visitsChart" height="100"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="chart-container">
                                <h5><i class="fas fa-mobile-alt text-primary"></i> Dispositivos</h5>
                                <canvas id="devicesChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content and Pages Row -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="chart-container">
                                <h5><i class="fas fa-file-alt text-primary"></i> Páginas Más Visitadas</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Página</th>
                                                <th>Vistas</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_views = array_sum(array_column($stats['paginas_populares'], 'vistas'));
                                            foreach ($stats['paginas_populares'] as $page): 
                                                $percentage = $total_views > 0 ? round(($page['vistas'] / $total_views) * 100, 1) : 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <small><?php echo htmlspecialchars($page['pagina']); ?></small>
                                                </td>
                                                <td><?php echo number_format($page['vistas']); ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                                            <?php echo $percentage; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="chart-container">
                                <h5><i class="fas fa-globe text-primary"></i> Navegadores</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Navegador</th>
                                                <th>Usuarios</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_browsers = array_sum(array_column($stats['navegadores'], 'total'));
                                            foreach ($stats['navegadores'] as $browser): 
                                                $percentage = $total_browsers > 0 ? round(($browser['total'] / $total_browsers) * 100, 1) : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($browser['navegador']); ?></td>
                                                <td><?php echo number_format($browser['total']); ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-info" style="width: <?php echo $percentage; ?>%">
                                                            <?php echo $percentage; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Metrics -->
                    <?php if (!empty($content_metrics)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="chart-container">
                                <h5><i class="fas fa-star text-primary"></i> Contenido Más Visto</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>ID</th>
                                                <th>Vistas</th>
                                                <th>Última Vista</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($content_metrics as $content): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo ucfirst($content['tipo_contenido']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $content['contenido_id']; ?></td>
                                                <td><?php echo number_format($content['vistas']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($content['fecha_ultima_vista'])); ?></td>
                                                <td>
                                                    <a href="../pages/<?php echo $content['tipo_contenido']; ?>.php?id=<?php echo $content['contenido_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Datos para los gráficos
        const chartData = <?php echo json_encode($chart_data); ?>;
        const deviceData = <?php echo json_encode($stats['dispositivos']); ?>;
        
        // Gráfico de visitas por día
        const visitsCtx = document.getElementById('visitsChart').getContext('2d');
        new Chart(visitsCtx, {
            type: 'line',
            data: {
                labels: chartData.visitas_por_dia.map(item => item.fecha),
                datasets: [{
                    label: 'Visitas',
                    data: chartData.visitas_por_dia.map(item => item.visitas),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Usuarios Únicos',
                    data: chartData.usuarios_por_dia.map(item => item.usuarios),
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Gráfico de dispositivos
        const devicesCtx = document.getElementById('devicesChart').getContext('2d');
        new Chart(devicesCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(deviceData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                datasets: [{
                    data: Object.values(deviceData),
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        
        // Función para exportar reporte
        function exportReport() {
            const periodo = document.querySelector('select[name="periodo"]').value;
            const tipo = document.querySelector('select[name="tipo"]').value;
            
            // Crear URL para descarga
            const url = `analytics_export.php?periodo=${periodo}&tipo=${tipo}`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
