<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Requerir autenticación y permisos
requireAuth();
requirePermission('servicios_view');

$mysqli = getDBConnection();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nombre = $mysqli->real_escape_string($_POST['nombre']);
                $descripcion = $mysqli->real_escape_string($_POST['descripcion']);
                $icono = $mysqli->real_escape_string($_POST['icono']);
                $imagen = $mysqli->real_escape_string($_POST['imagen']);
                
                $sql = "INSERT INTO servicios (nombre, descripcion, icono, imagen, activo) 
                        VALUES ('$nombre', '$descripcion', '$icono', '$imagen', 1)";
                
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Servicio agregado exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al agregar servicio: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'edit':
                $id = $mysqli->real_escape_string($_POST['id']);
                $nombre = $mysqli->real_escape_string($_POST['nombre']);
                $descripcion = $mysqli->real_escape_string($_POST['descripcion']);
                $icono = $mysqli->real_escape_string($_POST['icono']);
                $imagen = $mysqli->real_escape_string($_POST['imagen']);
                $activo = $mysqli->real_escape_string($_POST['activo']);
                
                $sql = "UPDATE servicios SET nombre = '$nombre', descripcion = '$descripcion', 
                        icono = '$icono', imagen = '$imagen', activo = $activo WHERE id = $id";
                
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Servicio actualizado exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al actualizar servicio: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'delete':
                $id = $mysqli->real_escape_string($_POST['id']);
                $sql = "DELETE FROM servicios WHERE id = $id";
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Servicio eliminado exitosamente</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error al eliminar servicio: ' . $mysqli->error . '</div>';
                }
                $action = 'list';
                break;
        }
    }
}

// Get service data for editing
$service = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $result = $mysqli->query("SELECT * FROM servicios WHERE id = $id");
    $service = $result->fetch_assoc();
}

// Get all services for listing
$services = [];
if ($action === 'list') {
    $result = $mysqli->query("SELECT * FROM servicios ORDER BY nombre ASC");
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios - Akademia 360</title>
    
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
        .service-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .icon-preview {
            font-size: 2rem;
            color: #667eea;
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
                        <a class="nav-link" href="index.php">
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
                        <a class="nav-link active" href="servicios.php">
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
                            <i class="fas fa-briefcase text-warning"></i> 
                            <?php echo $action === 'list' ? 'Gestión de Servicios' : ($action === 'add' ? 'Agregar Servicio' : 'Editar Servicio'); ?>
                        </h2>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=add" class="btn btn-warning">
                            <i class="fas fa-plus"></i> Agregar Servicio
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <?php if ($action === 'list'): ?>
                        <!-- Services List -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Icono</th>
                                                <th>Imagen</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($services as $item): ?>
                                            <tr>
                                                <td>
                                                    <i class="<?php echo htmlspecialchars($item['icono']); ?> icon-preview"></i>
                                                </td>
                                                <td>
                                                    <?php if ($item['imagen']): ?>
                                                        <img src="<?php echo htmlspecialchars($item['imagen']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['nombre']); ?>" 
                                                             class="service-image">
                                                    <?php else: ?>
                                                        <div class="service-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-briefcase text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['nombre']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                                <td>
                                                    <?php if ($item['activo']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteService(<?php echo $item['id']; ?>)">
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
                                    <?php if ($service): ?>
                                        <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre del Servicio *</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                                       value="<?php echo htmlspecialchars($service['nombre'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="descripcion" class="form-label">Descripción *</label>
                                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                                          rows="4" required><?php echo htmlspecialchars($service['descripcion'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="icono" class="form-label">Clase del Icono (Font Awesome) *</label>
                                                <input type="text" class="form-control" id="icono" name="icono" 
                                                       value="<?php echo htmlspecialchars($service['icono'] ?? ''); ?>" 
                                                       placeholder="fas fa-users" required>
                                                <small class="form-text text-muted">
                                                    Ejemplos: fas fa-users, fas fa-briefcase, fas fa-calculator, fas fa-chart-line, fas fa-gavel, fas fa-graduation-cap
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="imagen" class="form-label">URL de la Imagen</label>
                                                <input type="url" class="form-control" id="imagen" name="imagen" 
                                                       value="<?php echo htmlspecialchars($service['imagen'] ?? ''); ?>"
                                                       placeholder="https://ejemplo.com/imagen.jpg">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Vista Previa del Icono</label>
                                                <div id="icon-preview" class="border rounded p-3 text-center">
                                                    <?php if ($service && $service['icono']): ?>
                                                        <i class="<?php echo htmlspecialchars($service['icono']); ?> icon-preview"></i>
                                                        <br><small><?php echo htmlspecialchars($service['icono']); ?></small>
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-question-circle fa-3x"></i>
                                                            <br><small>Sin icono</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Vista Previa de la Imagen</label>
                                                <div id="image-preview" class="border rounded p-2 text-center">
                                                    <?php if ($service && $service['imagen']): ?>
                                                        <img src="<?php echo htmlspecialchars($service['imagen']); ?>" 
                                                             alt="Vista previa" class="img-fluid rounded" style="max-height: 200px;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-image fa-3x"></i>
                                                            <br><small>Sin imagen</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($service): ?>
                                            <div class="mb-3">
                                                <label for="activo" class="form-label">Estado</label>
                                                <select class="form-control" id="activo" name="activo">
                                                    <option value="1" <?php echo ($service['activo'] ?? 1) == 1 ? 'selected' : ''; ?>>Activo</option>
                                                    <option value="0" <?php echo ($service['activo'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactivo</option>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=list" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save"></i> 
                                            <?php echo $service ? 'Actualizar' : 'Guardar'; ?> Servicio
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
        function deleteService(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este servicio?')) {
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
        
        // Icon preview functionality
        document.getElementById('icono')?.addEventListener('input', function() {
            const preview = document.getElementById('icon-preview');
            const iconClass = this.value;
            
            if (iconClass) {
                preview.innerHTML = `<i class="${iconClass} icon-preview"></i><br><small>${iconClass}</small>`;
            } else {
                preview.innerHTML = '<div class="text-muted"><i class="fas fa-question-circle fa-3x"></i><br><small>Sin icono</small></div>';
            }
        });
        
        // Image preview functionality
        document.getElementById('imagen')?.addEventListener('input', function() {
            const preview = document.getElementById('image-preview');
            const url = this.value;
            
            if (url) {
                preview.innerHTML = `<img src="${url}" alt="Vista previa" class="img-fluid rounded" style="max-height: 200px;" onerror="this.parentElement.innerHTML='<div class=\'text-danger\'><i class=\'fas fa-exclamation-triangle\'></i><br><small>Error al cargar imagen</small></div>'">`;
            } else {
                preview.innerHTML = '<div class="text-muted"><i class="fas fa-image fa-3x"></i><br><small>Sin imagen</small></div>';
            }
        });
    </script>
</body>
</html> 