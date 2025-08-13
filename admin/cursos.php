<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Requerir autenticación y permisos
requireAuth();
requirePermission('cursos_view');

$mysqli = getDBConnection();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $titulo = $mysqli->real_escape_string($_POST['titulo']);
                $descripcion_corta = $mysqli->real_escape_string($_POST['descripcion_corta']);
                $descripcion_completa = $mysqli->real_escape_string($_POST['descripcion_completa']);
                $objetivos = $mysqli->real_escape_string($_POST['objetivos']);
                $programa_detallado = $mysqli->real_escape_string($_POST['programa_detallado']);
                $codigo = $mysqli->real_escape_string($_POST['codigo']);
                $precio = $mysqli->real_escape_string($_POST['precio']);
                $id_relator = $mysqli->real_escape_string($_POST['id_relator']);
                $categoria = $mysqli->real_escape_string($_POST['categoria']);
                $imagen = $mysqli->real_escape_string($_POST['imagen']);
                
                // Manejar fechas del curso
                $tiene_fechas = isset($_POST['tiene_fechas']) ? 1 : 0;
                $es_cotizable = isset($_POST['es_cotizable']) ? 1 : 0;
                $fecha_inicio_curso = $tiene_fechas && !empty($_POST['fecha_inicio_curso']) ? 
                    "'" . $mysqli->real_escape_string($_POST['fecha_inicio_curso']) . "'" : "NULL";
                $fecha_fin_curso = $tiene_fechas && !empty($_POST['fecha_fin_curso']) ? 
                    "'" . $mysqli->real_escape_string($_POST['fecha_fin_curso']) . "'" : "NULL";
                
                // Si es cotizable, el precio debe ser NULL
                if ($es_cotizable) {
                    $precio = "NULL";
                }
                
                $sql = "INSERT INTO cursos (titulo, descripcion_corta, descripcion_completa, objetivos, programa_detallado, codigo, precio, id_relator, categoria, imagen, activo, fecha_inicio, fecha_fin, tiene_fechas, es_cotizable) 
                        VALUES ('$titulo', '$descripcion_corta', '$descripcion_completa', '$objetivos', '$programa_detallado', '$codigo', $precio, $id_relator, '$categoria', '$imagen', 1, $fecha_inicio_curso, $fecha_fin_curso, $tiene_fechas, $es_cotizable)";
                
                if ($mysqli->query($sql)) {
                    $curso_id = $mysqli->insert_id;
                    
                    // Manejar opciones del carrusel
                    if (isset($_POST['incluir_carrusel']) && $_POST['incluir_carrusel'] == '1') {
                        $titulo_carrusel = $mysqli->real_escape_string($_POST['titulo_carrusel'] ?? $titulo);
                        $descripcion_carrusel = $mysqli->real_escape_string($_POST['descripcion_carrusel'] ?? $descripcion_corta);
                        $fecha_inicio = $mysqli->real_escape_string($_POST['fecha_inicio'] ?? '');
                        $fecha_fin = $mysqli->real_escape_string($_POST['fecha_fin'] ?? '');
                        
                        $sql_carrusel = "INSERT INTO contenido_destacado (tipo, id_contenido, titulo, descripcion, imagen, fecha_inicio, fecha_fin, activo) 
                                        VALUES ('curso', $curso_id, '$titulo_carrusel', '$descripcion_carrusel', '$imagen', " . 
                                        ($fecha_inicio ? "'$fecha_inicio'" : "NULL") . ", " . 
                                        ($fecha_fin ? "'$fecha_fin'" : "NULL") . ", 1)";
                        
                        $mysqli->query($sql_carrusel);
                    }
                    
                    $message = '<div class="alert alert-success">Curso agregado exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al agregar curso: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'edit':
                $id = $mysqli->real_escape_string($_POST['id']);
                $titulo = $mysqli->real_escape_string($_POST['titulo']);
                $descripcion_corta = $mysqli->real_escape_string($_POST['descripcion_corta']);
                $descripcion_completa = $mysqli->real_escape_string($_POST['descripcion_completa']);
                $objetivos = $mysqli->real_escape_string($_POST['objetivos']);
                $programa_detallado = $mysqli->real_escape_string($_POST['programa_detallado']);
                $codigo = $mysqli->real_escape_string($_POST['codigo']);
                $precio = $mysqli->real_escape_string($_POST['precio']);
                $id_relator = $mysqli->real_escape_string($_POST['id_relator']);
                $categoria = $mysqli->real_escape_string($_POST['categoria']);
                $imagen = $mysqli->real_escape_string($_POST['imagen']);
                $activo = $mysqli->real_escape_string($_POST['activo']);
                
                // Manejar fechas del curso
                $tiene_fechas = isset($_POST['tiene_fechas']) ? 1 : 0;
                $es_cotizable = isset($_POST['es_cotizable']) ? 1 : 0;
                $fecha_inicio_curso = $tiene_fechas && !empty($_POST['fecha_inicio_curso']) ? 
                    "'" . $mysqli->real_escape_string($_POST['fecha_inicio_curso']) . "'" : "NULL";
                $fecha_fin_curso = $tiene_fechas && !empty($_POST['fecha_fin_curso']) ? 
                    "'" . $mysqli->real_escape_string($_POST['fecha_fin_curso']) . "'" : "NULL";
                
                // Si es cotizable, el precio debe ser NULL
                if ($es_cotizable) {
                    $precio = "NULL";
                }
                
                $sql = "UPDATE cursos SET titulo = '$titulo', descripcion_corta = '$descripcion_corta', descripcion_completa = '$descripcion_completa', 
                        objetivos = '$objetivos', programa_detallado = '$programa_detallado', codigo = '$codigo', precio = $precio, 
                        id_relator = $id_relator, categoria = '$categoria', imagen = '$imagen', activo = $activo, 
                        fecha_inicio = $fecha_inicio_curso, fecha_fin = $fecha_fin_curso, tiene_fechas = $tiene_fechas, es_cotizable = $es_cotizable 
                        WHERE id = $id";
                
                if ($mysqli->query($sql)) {
                    // Manejar opciones del carrusel
                    if (isset($_POST['incluir_carrusel']) && $_POST['incluir_carrusel'] == '1') {
                        $titulo_carrusel = $mysqli->real_escape_string($_POST['titulo_carrusel'] ?? $titulo);
                        $descripcion_carrusel = $mysqli->real_escape_string($_POST['descripcion_carrusel'] ?? $descripcion_corta);
                        $fecha_inicio = $mysqli->real_escape_string($_POST['fecha_inicio'] ?? '');
                        $fecha_fin = $mysqli->real_escape_string($_POST['fecha_fin'] ?? '');
                        
                        // Verificar si ya existe en el carrusel
                        $check_sql = "SELECT id FROM contenido_destacado WHERE tipo = 'curso' AND id_contenido = $id";
                        $check_result = $mysqli->query($check_sql);
                        
                        if ($check_result->num_rows > 0) {
                            // Actualizar registro existente
                            $sql_carrusel = "UPDATE contenido_destacado SET 
                                            titulo = '$titulo_carrusel', 
                                            descripcion = '$descripcion_carrusel', 
                                            imagen = '$imagen', 
                                            fecha_inicio = " . ($fecha_inicio ? "'$fecha_inicio'" : "NULL") . ", 
                                            fecha_fin = " . ($fecha_fin ? "'$fecha_fin'" : "NULL") . ", 
                                            activo = 1 
                                            WHERE tipo = 'curso' AND id_contenido = $id";
                        } else {
                            // Crear nuevo registro
                            $sql_carrusel = "INSERT INTO contenido_destacado (tipo, id_contenido, titulo, descripcion, imagen, fecha_inicio, fecha_fin, activo) 
                                            VALUES ('curso', $id, '$titulo_carrusel', '$descripcion_carrusel', '$imagen', " . 
                                            ($fecha_inicio ? "'$fecha_inicio'" : "NULL") . ", " . 
                                            ($fecha_fin ? "'$fecha_fin'" : "NULL") . ", 1)";
                        }
                        
                        $mysqli->query($sql_carrusel);
                    } else {
                        // Desactivar en el carrusel si no está marcado
                        $sql_carrusel = "UPDATE contenido_destacado SET activo = 0 WHERE tipo = 'curso' AND id_contenido = $id";
                        $mysqli->query($sql_carrusel);
                    }
                    
                    $message = '<div class="alert alert-success">Curso actualizado exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al actualizar curso: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'delete':
                $id = $mysqli->real_escape_string($_POST['id']);
                $sql = "DELETE FROM cursos WHERE id = $id";
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Curso eliminado exitosamente</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error al eliminar curso: ' . $mysqli->error . '</div>';
                }
                $action = 'list';
                break;
        }
    }
}

// Get course data for editing
$course = null;
$carrusel_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $result = $mysqli->query("SELECT * FROM cursos WHERE id = $id");
    $course = $result->fetch_assoc();
    
    // Obtener datos del carrusel si existe
    $carrusel_result = $mysqli->query("SELECT * FROM contenido_destacado WHERE tipo = 'curso' AND id_contenido = $id AND activo = 1");
    if ($carrusel_result->num_rows > 0) {
        $carrusel_data = $carrusel_result->fetch_assoc();
    }
}

// Get all relators for dropdown
$relators = [];
$result = $mysqli->query("SELECT id, nombre FROM relatores WHERE activo = 1 ORDER BY nombre");
while ($row = $result->fetch_assoc()) {
    $relators[] = $row;
}

// Get all courses for listing
$courses = [];
if ($action === 'list') {
    $result = $mysqli->query("SELECT c.*, r.nombre as nombre_relator FROM cursos c LEFT JOIN relatores r ON c.id_relator = r.id ORDER BY c.fecha_creacion DESC");
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos - Akademia 360</title>
    
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
        .content-area {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .course-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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
                        <img src="../img/logo-akademia360.png" alt="Akademia 360" style="height: 40px; width: auto; filter: brightness(0) invert(1); margin-bottom: 10px;">
                        <small class="text-white-50">Panel de Administración</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="cursos.php">
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>
                            <i class="fas fa-graduation-cap text-primary"></i> 
                            <?php echo $action === 'list' ? 'Gestión de Cursos' : ($action === 'add' ? 'Agregar Curso' : 'Editar Curso'); ?>
                        </h2>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Curso
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <?php if ($action === 'list'): ?>
                        <!-- Courses List -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Imagen</th>
                                                <th>Título</th>
                                                <th>Código</th>
                                                <th>Relator</th>
                                                <th>Precio</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($course['imagen']): ?>
                                                        <img src="<?php echo htmlspecialchars($course['imagen']); ?>" 
                                                             alt="<?php echo htmlspecialchars($course['titulo']); ?>" 
                                                             class="course-image">
                                                    <?php else: ?>
                                                        <div class="course-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-graduation-cap text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($course['titulo']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($course['descripcion_corta']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($course['codigo']); ?></td>
                                                <td><?php echo htmlspecialchars($course['nombre_relator'] ?? 'Sin asignar'); ?></td>
                                                <td>
                                                    <?php if ($course['es_cotizable']): ?>
                                                        <span class="badge bg-warning">Cotizable</span>
                                                    <?php elseif (!empty($course['precio']) && $course['precio'] !== null): ?>
                                                        $<?php echo number_format($course['precio'], 0, ',', '.'); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin precio</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($course['activo']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?action=edit&id=<?php echo $course['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="../pages/curso.php?id=<?php echo $course['id']; ?>" 
                                                           target="_blank" class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Add/Edit Form -->
                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                                    <?php if ($course): ?>
                                        <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="titulo" class="form-label">Título del Curso *</label>
                                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                                       value="<?php echo htmlspecialchars($course['titulo'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="descripcion_corta" class="form-label">Descripción Corta *</label>
                                                <textarea class="form-control" id="descripcion_corta" name="descripcion_corta" 
                                                          rows="3" required><?php echo htmlspecialchars($course['descripcion_corta'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="descripcion_completa" class="form-label">Descripción Completa</label>
                                                <textarea class="form-control" id="descripcion_completa" name="descripcion_completa" 
                                                          rows="5"><?php echo htmlspecialchars($course['descripcion_completa'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="objetivos" class="form-label">Objetivos del Curso</label>
                                                <textarea class="form-control" id="objetivos" name="objetivos" 
                                                          rows="3"><?php echo htmlspecialchars($course['objetivos'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="programa_detallado" class="form-label">Programa Detallado</label>
                                                <textarea class="form-control" id="programa_detallado" name="programa_detallado" 
                                                          rows="5"><?php echo htmlspecialchars($course['programa_detallado'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="codigo" class="form-label">Código del Curso *</label>
                                                <input type="text" class="form-control" id="codigo" name="codigo" 
                                                       value="<?php echo htmlspecialchars($course['codigo'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="precio" class="form-label">Precio</label>
                                                <input type="number" class="form-control" id="precio" name="precio" 
                                                       value="<?php echo $course['precio'] ?? ''; ?>" 
                                                       <?php echo ($course['es_cotizable'] ?? false) ? 'disabled' : ''; ?>>
                                                <small class="form-text text-muted">
                                                    Para cursos cotizables, el precio se establece al momento de la cotización
                                                </small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="id_relator" class="form-label">Relator</label>
                                                <select class="form-control" id="id_relator" name="id_relator">
                                                    <option value="">Seleccionar relator</option>
                                                    <?php foreach ($relators as $relator): ?>
                                                        <option value="<?php echo $relator['id']; ?>" 
                                                                <?php echo ($course['id_relator'] ?? '') == $relator['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($relator['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="categoria" class="form-label">Categoría</label>
                                                <select class="form-control" id="categoria" name="categoria">
                                                    <option value="Recursos Humanos" <?php echo ($course['categoria'] ?? '') == 'Recursos Humanos' ? 'selected' : ''; ?>>Recursos Humanos</option>
                                                    <option value="Derecho Laboral" <?php echo ($course['categoria'] ?? '') == 'Derecho Laboral' ? 'selected' : ''; ?>>Derecho Laboral</option>
                                                    <option value="Contabilidad" <?php echo ($course['categoria'] ?? '') == 'Contabilidad' ? 'selected' : ''; ?>>Contabilidad</option>
                                                    <option value="Tributario" <?php echo ($course['categoria'] ?? '') == 'Tributario' ? 'selected' : ''; ?>>Tributario</option>
                                                    <option value="Liderazgo" <?php echo ($course['categoria'] ?? '') == 'Liderazgo' ? 'selected' : ''; ?>>Liderazgo</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="imagen" class="form-label">URL de la Imagen</label>
                                                <input type="url" class="form-control" id="imagen" name="imagen" 
                                                       value="<?php echo htmlspecialchars($course['imagen'] ?? ''); ?>"
                                                       placeholder="https://ejemplo.com/imagen.jpg">
                                            </div>
                                            
                                            <!-- Configuración de Fechas del Curso -->
                                            <div class="card mt-4">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0"><i class="fas fa-calendar"></i> Configuración de Fechas</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tiene_fechas" name="tiene_fechas" value="1" 
                                                                   <?php echo ($course['tiene_fechas'] ?? false) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tiene_fechas">
                                                                Este curso tiene fechas específicas
                                                            </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div id="fechas-curso" style="display: none;">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="fecha_inicio_curso" class="form-label">Fecha de Inicio del Curso</label>
                                                                    <input type="date" class="form-control" id="fecha_inicio_curso" name="fecha_inicio_curso" 
                                                                           value="<?php echo (!empty($course['fecha_inicio']) && $course['fecha_inicio'] != '0000-00-00') ? date('Y-m-d', strtotime($course['fecha_inicio'])) : ''; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="fecha_fin_curso" class="form-label">Fecha de Fin del Curso</label>
                                                                    <input type="date" class="form-control" id="fecha_fin_curso" name="fecha_fin_curso" 
                                                                           value="<?php echo (!empty($course['fecha_fin']) && $course['fecha_fin'] != '0000-00-00') ? date('Y-m-d', strtotime($course['fecha_fin'])) : ''; ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="es_cotizable" name="es_cotizable" value="1" 
                                                                   <?php echo ($course['es_cotizable'] ?? false) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="es_cotizable">
                                                                Este curso es cotizable (sin precio fijo)
                                                            </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i>
                                                        <strong>Tipos de cursos:</strong><br>
                                                        • <strong>Con fechas:</strong> Aparece en el calendario del mes correspondiente<br>
                                                        • <strong>Cotizable:</strong> Se puede vender/cotizar en cualquier momento<br>
                                                        • <strong>Regular:</strong> Tiene precio fijo pero sin fechas específicas
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php if ($course): ?>
                                            <div class="mb-3">
                                                <label for="activo" class="form-label">Estado</label>
                                                <select class="form-control" id="activo" name="activo">
                                                    <option value="1" <?php echo ($course['activo'] ?? 1) == 1 ? 'selected' : ''; ?>>Activo</option>
                                                    <option value="0" <?php echo ($course['activo'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactivo</option>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Opciones del Carrusel -->
                                            <div class="card mt-4">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-images"></i> Opciones del Carrusel</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="incluir_carrusel" name="incluir_carrusel" value="1" 
                                                                   <?php echo ($carrusel_data) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="incluir_carrusel">
                                                                Incluir en el carrusel principal
                                                            </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div id="carrusel-options" style="display: none;">
                                                        <div class="mb-3">
                                                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                                            <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                                                   value="<?php echo ($carrusel_data && !empty($carrusel_data['fecha_inicio']) && $carrusel_data['fecha_inicio'] != '0000-00-00 00:00:00') ? date('Y-m-d\TH:i', strtotime($carrusel_data['fecha_inicio'])) : ''; ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                                            <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" 
                                                                   value="<?php echo ($carrusel_data && !empty($carrusel_data['fecha_fin']) && $carrusel_data['fecha_fin'] != '0000-00-00 00:00:00') ? date('Y-m-d\TH:i', strtotime($carrusel_data['fecha_fin'])) : ''; ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="titulo_carrusel" class="form-label">Título para el Carrusel</label>
                                                            <input type="text" class="form-control" id="titulo_carrusel" name="titulo_carrusel" 
                                                                   value="<?php echo htmlspecialchars($carrusel_data['titulo'] ?? $course['titulo'] ?? ''); ?>"
                                                                   placeholder="Título que aparecerá en el carrusel">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="descripcion_carrusel" class="form-label">Descripción para el Carrusel</label>
                                                            <textarea class="form-control" id="descripcion_carrusel" name="descripcion_carrusel" 
                                                                      rows="3" placeholder="Descripción que aparecerá en el carrusel"><?php echo htmlspecialchars($carrusel_data['descripcion'] ?? $course['descripcion_corta'] ?? ''); ?></textarea>
                                                        </div>
                                                        
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i>
                                                            <strong>Nota:</strong> Si no especificas fechas, el contenido aparecerá inmediatamente en el carrusel.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=list" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> 
                                            <?php echo $course ? 'Actualizar' : 'Guardar'; ?> Curso
                                        </button>
                                    </div>
                                </form>
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
        function deleteCourse(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este curso?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Carrusel options functionality
        document.getElementById('incluir_carrusel')?.addEventListener('change', function() {
            const options = document.getElementById('carrusel-options');
            if (this.checked) {
                options.style.display = 'block';
            } else {
                options.style.display = 'none';
            }
        });
        
        // Show carrusel options on page load if checkbox is checked
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('incluir_carrusel');
            const options = document.getElementById('carrusel-options');
            if (checkbox && checkbox.checked) {
                options.style.display = 'block';
            }
        });
        
        // Auto-fill carrusel fields when checkbox is checked
        document.getElementById('incluir_carrusel')?.addEventListener('change', function() {
            if (this.checked) {
                const titulo = document.getElementById('titulo').value;
                const descripcion = document.getElementById('descripcion_corta').value;
                
                if (titulo) {
                    document.getElementById('titulo_carrusel').value = titulo;
                }
                if (descripcion) {
                    document.getElementById('descripcion_carrusel').value = descripcion;
                }
            }
        });
        
        // Course dates functionality
        document.getElementById('tiene_fechas')?.addEventListener('change', function() {
            const fechasCurso = document.getElementById('fechas-curso');
            if (this.checked) {
                fechasCurso.style.display = 'block';
            } else {
                fechasCurso.style.display = 'none';
            }
        });
        
        // Show course dates on page load if checkbox is checked
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('tiene_fechas');
            const fechasCurso = document.getElementById('fechas-curso');
            if (checkbox && checkbox.checked) {
                fechasCurso.style.display = 'block';
            }
        });
        
        // Handle quotable course checkbox
        document.getElementById('es_cotizable')?.addEventListener('change', function() {
            const precioField = document.getElementById('precio');
            if (this.checked) {
                precioField.value = '';
                precioField.disabled = true;
                precioField.placeholder = 'Precio no aplicable para cursos cotizables';
            } else {
                precioField.disabled = false;
                precioField.placeholder = 'Ingrese el precio del curso';
            }
        });
        
        // Show price field state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const quotableCheckbox = document.getElementById('es_cotizable');
            const precioField = document.getElementById('precio');
            if (quotableCheckbox && quotableCheckbox.checked) {
                precioField.disabled = true;
                precioField.placeholder = 'Precio no aplicable para cursos cotizables';
            }
        });
    </script>
</body>
</html> 