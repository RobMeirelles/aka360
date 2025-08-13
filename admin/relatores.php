<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Simple authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$mysqli = getDBConnection();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nombre = $mysqli->real_escape_string($_POST['nombre']);
                $titulo = $mysqli->real_escape_string($_POST['titulo']);
                $biografia = $mysqli->real_escape_string($_POST['biografia']);
                $especializacion = $mysqli->real_escape_string($_POST['especializacion']);
                $universidad = $mysqli->real_escape_string($_POST['universidad']);
                $email = $mysqli->real_escape_string($_POST['email']);
                $imagen = $mysqli->real_escape_string($_POST['imagen']);
                
                $sql = "INSERT INTO relatores (nombre, titulo, biografia, especializacion, universidad, email, imagen, activo) 
                        VALUES ('$nombre', '$titulo', '$biografia', '$especializacion', '$universidad', '$email', '$imagen', 1)";
                
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Relator agregado exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al agregar relator: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'edit':
                $id = $mysqli->real_escape_string($_POST['id']);
                $nombre = $mysqli->real_escape_string($_POST['nombre']);
                $titulo = $mysqli->real_escape_string($_POST['titulo']);
                $biografia = $mysqli->real_escape_string($_POST['biografia']);
                $especializacion = $mysqli->real_escape_string($_POST['especializacion']);
                $universidad = $mysqli->real_escape_string($_POST['universidad']);
                $email = $mysqli->real_escape_string($_POST['email']);
                $imagen = $mysqli->real_escape_string($_POST['imagen']);
                $activo = $mysqli->real_escape_string($_POST['activo']);
                
                $sql = "UPDATE relatores SET nombre = '$nombre', titulo = '$titulo', biografia = '$biografia', 
                        especializacion = '$especializacion', universidad = '$universidad', email = '$email', 
                        imagen = '$imagen', activo = $activo WHERE id = $id";
                
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Relator actualizado exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al actualizar relator: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'delete':
                $id = $mysqli->real_escape_string($_POST['id']);
                $sql = "DELETE FROM relatores WHERE id = $id";
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Relator eliminado exitosamente</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error al eliminar relator: ' . $mysqli->error . '</div>';
                }
                $action = 'list';
                break;
        }
    }
}

// Get relator data for editing
$relator = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $result = $mysqli->query("SELECT * FROM relatores WHERE id = $id");
    $relator = $result->fetch_assoc();
}

// Get all relators for listing
$relators = [];
if ($action === 'list') {
    $result = $mysqli->query("SELECT * FROM relatores ORDER BY nombre ASC");
    while ($row = $result->fetch_assoc()) {
        $relators[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Relatores - Akademia 360</title>
    
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
        .relator-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
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
                        <a class="nav-link active" href="relatores.php">
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
                            <i class="fas fa-user-tie text-info"></i> 
                            <?php echo $action === 'list' ? 'Gestión de Relatores' : ($action === 'add' ? 'Agregar Relator' : 'Editar Relator'); ?>
                        </h2>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=add" class="btn btn-info">
                            <i class="fas fa-plus"></i> Agregar Relator
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <?php if ($action === 'list'): ?>
                        <!-- Relators List -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Foto</th>
                                                <th>Nombre</th>
                                                <th>Título</th>
                                                <th>Especialización</th>
                                                <th>Universidad</th>
                                                <th>Email</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($relators as $item): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($item['imagen']): ?>
                                                        <img src="<?php echo htmlspecialchars($item['imagen']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['nombre']); ?>" 
                                                             class="relator-image">
                                                    <?php else: ?>
                                                        <div class="relator-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-user text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['nombre']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($item['especializacion']); ?></td>
                                                <td><?php echo htmlspecialchars($item['universidad']); ?></td>
                                                <td><?php echo htmlspecialchars($item['email']); ?></td>
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
                                                                onclick="deleteRelator(<?php echo $item['id']; ?>)">
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
                                    <?php if ($relator): ?>
                                        <input type="hidden" name="id" value="<?php echo $relator['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                                       value="<?php echo htmlspecialchars($relator['nombre'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="titulo" class="form-label">Título Profesional *</label>
                                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                                       value="<?php echo htmlspecialchars($relator['titulo'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="especializacion" class="form-label">Especialización *</label>
                                                <input type="text" class="form-control" id="especializacion" name="especializacion" 
                                                       value="<?php echo htmlspecialchars($relator['especializacion'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="universidad" class="form-label">Universidad *</label>
                                                <input type="text" class="form-control" id="universidad" name="universidad" 
                                                       value="<?php echo htmlspecialchars($relator['universidad'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($relator['email'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="biografia" class="form-label">Biografía</label>
                                                <textarea class="form-control" id="biografia" name="biografia" 
                                                          rows="5"><?php echo htmlspecialchars($relator['biografia'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="imagen" class="form-label">URL de la Foto</label>
                                                <input type="url" class="form-control" id="imagen" name="imagen" 
                                                       value="<?php echo htmlspecialchars($relator['imagen'] ?? ''); ?>"
                                                       placeholder="https://ejemplo.com/foto.jpg">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Vista Previa de la Foto</label>
                                                <div id="image-preview" class="border rounded p-2 text-center">
                                                    <?php if ($relator && $relator['imagen']): ?>
                                                        <img src="<?php echo htmlspecialchars($relator['imagen']); ?>" 
                                                             alt="Vista previa" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-user fa-3x"></i>
                                                            <br><small>Sin foto</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($relator): ?>
                                            <div class="mb-3">
                                                <label for="activo" class="form-label">Estado</label>
                                                <select class="form-control" id="activo" name="activo">
                                                    <option value="1" <?php echo ($relator['activo'] ?? 1) == 1 ? 'selected' : ''; ?>>Activo</option>
                                                    <option value="0" <?php echo ($relator['activo'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactivo</option>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=list" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-save"></i> 
                                            <?php echo $relator ? 'Actualizar' : 'Guardar'; ?> Relator
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
        function deleteRelator(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este relator?')) {
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
        
        // Image preview functionality
        document.getElementById('imagen')?.addEventListener('input', function() {
            const preview = document.getElementById('image-preview');
            const url = this.value;
            
            if (url) {
                preview.innerHTML = `<img src="${url}" alt="Vista previa" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.parentElement.innerHTML='<div class=\'text-danger\'><i class=\'fas fa-exclamation-triangle\'></i><br><small>Error al cargar imagen</small></div>'">`;
            } else {
                preview.innerHTML = '<div class="text-muted"><i class="fas fa-user fa-3x"></i><br><small>Sin foto</small></div>';
            }
        });
    </script>
</body>
</html> 