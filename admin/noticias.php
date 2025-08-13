<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';
require_once '../includes/upload_functions.php';

// Requerir autenticación y permisos
requireAuth();
requirePermission('noticias_view');

$mysqli = getDBConnection();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $titulo = $mysqli->real_escape_string($_POST['titulo']);
                $resumen = $mysqli->real_escape_string($_POST['resumen']);
                $contenido = $mysqli->real_escape_string($_POST['contenido']);
                $autor = $mysqli->real_escape_string($_POST['autor']);
                
                // Procesar imagen (URL o archivo subido)
                $imagen = processImageField($_POST, $_FILES, 'imagen');
                
                $sql = "INSERT INTO noticias (titulo, resumen, contenido, autor, imagen) 
                        VALUES ('$titulo', '$resumen', '$contenido', '$autor', '$imagen')";
                
                if ($mysqli->query($sql)) {
                    $noticia_id = $mysqli->insert_id;
                    
                    // Manejar opciones del carrusel
                    if (isset($_POST['incluir_carrusel']) && $_POST['incluir_carrusel'] == '1') {
                        $titulo_carrusel = $mysqli->real_escape_string($_POST['titulo_carrusel'] ?? $titulo);
                        $descripcion_carrusel = $mysqli->real_escape_string($_POST['descripcion_carrusel'] ?? $resumen);
                        $fecha_inicio = $mysqli->real_escape_string($_POST['fecha_inicio'] ?? '');
                        $fecha_fin = $mysqli->real_escape_string($_POST['fecha_fin'] ?? '');
                        
                        $sql_carrusel = "INSERT INTO contenido_destacado (tipo, id_contenido, titulo, descripcion, imagen, fecha_inicio, fecha_fin, activo) 
                                        VALUES ('noticia', $noticia_id, '$titulo_carrusel', '$descripcion_carrusel', '$imagen', " . 
                                        ($fecha_inicio ? "'$fecha_inicio'" : "NULL") . ", " . 
                                        ($fecha_fin ? "'$fecha_fin'" : "NULL") . ", 1)";
                        
                        $mysqli->query($sql_carrusel);
                    }
                    
                    $message = '<div class="alert alert-success">Noticia agregada exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al agregar noticia: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'edit':
                $id = $mysqli->real_escape_string($_POST['id']);
                $titulo = $mysqli->real_escape_string($_POST['titulo']);
                $resumen = $mysqli->real_escape_string($_POST['resumen']);
                $contenido = $mysqli->real_escape_string($_POST['contenido']);
                $autor = $mysqli->real_escape_string($_POST['autor']);
                
                // Obtener imagen anterior para comparar
                $old_image = '';
                if (isset($_POST['old_image'])) {
                    $old_image = $_POST['old_image'];
                }
                
                // Procesar imagen (URL o archivo subido)
                $imagen = processImageField($_POST, $_FILES, 'imagen', $old_image);
                
                $sql = "UPDATE noticias SET titulo = '$titulo', resumen = '$resumen', contenido = '$contenido', 
                        autor = '$autor', imagen = '$imagen' WHERE id = $id";
                
                if ($mysqli->query($sql)) {
                    // Manejar opciones del carrusel
                    if (isset($_POST['incluir_carrusel']) && $_POST['incluir_carrusel'] == '1') {
                        $titulo_carrusel = $mysqli->real_escape_string($_POST['titulo_carrusel'] ?? $titulo);
                        $descripcion_carrusel = $mysqli->real_escape_string($_POST['descripcion_carrusel'] ?? $resumen);
                        $fecha_inicio = $mysqli->real_escape_string($_POST['fecha_inicio'] ?? '');
                        $fecha_fin = $mysqli->real_escape_string($_POST['fecha_fin'] ?? '');
                        
                        // Verificar si ya existe en el carrusel
                        $check_sql = "SELECT id FROM contenido_destacado WHERE tipo = 'noticia' AND id_contenido = $id";
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
                                            WHERE tipo = 'noticia' AND id_contenido = $id";
                        } else {
                            // Crear nuevo registro
                            $sql_carrusel = "INSERT INTO contenido_destacado (tipo, id_contenido, titulo, descripcion, imagen, fecha_inicio, fecha_fin, activo) 
                                            VALUES ('noticia', $id, '$titulo_carrusel', '$descripcion_carrusel', '$imagen', " . 
                                            ($fecha_inicio ? "'$fecha_inicio'" : "NULL") . ", " . 
                                            ($fecha_fin ? "'$fecha_fin'" : "NULL") . ", 1)";
                        }
                        
                        $mysqli->query($sql_carrusel);
                    } else {
                        // Desactivar en el carrusel si no está marcado
                        $sql_carrusel = "UPDATE contenido_destacado SET activo = 0 WHERE tipo = 'noticia' AND id_contenido = $id";
                        $mysqli->query($sql_carrusel);
                    }
                    
                    $message = '<div class="alert alert-success">Noticia actualizada exitosamente</div>';
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al actualizar noticia: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'delete':
                $id = $mysqli->real_escape_string($_POST['id']);
                $sql = "DELETE FROM noticias WHERE id = $id";
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Noticia eliminada exitosamente</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error al eliminar noticia: ' . $mysqli->error . '</div>';
                }
                $action = 'list';
                break;
        }
    }
}

// Get news data for editing
$news = null;
$carrusel_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $result = $mysqli->query("SELECT * FROM noticias WHERE id = $id");
    $news = $result->fetch_assoc();
    
    // Obtener datos del carrusel si existe
    $carrusel_result = $mysqli->query("SELECT * FROM contenido_destacado WHERE tipo = 'noticia' AND id_contenido = $id AND activo = 1");
    if ($carrusel_result->num_rows > 0) {
        $carrusel_data = $carrusel_result->fetch_assoc();
    }
}

// Get all news for listing
$news_list = [];
if ($action === 'list') {
    $result = $mysqli->query("SELECT * FROM noticias ORDER BY fecha_creacion DESC");
    while ($row = $result->fetch_assoc()) {
        $news_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Noticias - Akademia 360</title>
    
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
        .news-image {
            width: 80px;
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
                        <a class="nav-link active" href="noticias.php">
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
                            <i class="fas fa-newspaper text-success"></i> 
                            <?php echo $action === 'list' ? 'Gestión de Noticias' : ($action === 'add' ? 'Agregar Noticia' : 'Editar Noticia'); ?>
                        </h2>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=add" class="btn btn-success">
                            <i class="fas fa-plus"></i> Agregar Noticia
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <?php if ($action === 'list'): ?>
                        <!-- News List -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Imagen</th>
                                                <th>Título</th>
                                                <th>Autor</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($news_list as $item): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($item['imagen']): ?>
                                                        <img src="<?php echo htmlspecialchars($item['imagen']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['titulo']); ?>" 
                                                             class="news-image">
                                                    <?php else: ?>
                                                        <div class="news-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-newspaper text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['titulo']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($item['resumen']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['autor']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($item['fecha_creacion'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="../pages/noticia.php?id=<?php echo $item['id']; ?>" 
                                                           target="_blank" class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteNews(<?php echo $item['id']; ?>)">
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
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                                    <?php if ($news): ?>
                                        <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="titulo" class="form-label">Título de la Noticia *</label>
                                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                                       value="<?php echo htmlspecialchars($news['titulo'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="resumen" class="form-label">Resumen *</label>
                                                <textarea class="form-control" id="resumen" name="resumen" 
                                                          rows="3" required><?php echo htmlspecialchars($news['resumen'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="contenido" class="form-label">Contenido Completo</label>
                                                <textarea class="form-control" id="contenido" name="contenido" 
                                                          rows="10"><?php echo htmlspecialchars($news['contenido'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="autor" class="form-label">Autor *</label>
                                                <input type="text" class="form-control" id="autor" name="autor" 
                                                       value="<?php echo htmlspecialchars($news['autor'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="imagen" class="form-label">Imagen</label>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Subir imagen desde tu computadora</label>
                                                        <input type="file" class="form-control" id="imagen_file" name="imagen" accept="image/*">
                                                        <small class="text-muted">Formatos: JPG, PNG, GIF, WebP. Máximo 5MB.</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">O usar URL de imagen</label>
                                                        <input type="url" class="form-control" id="imagen_url" name="imagen_url" 
                                                               value="<?php echo htmlspecialchars($news['imagen'] ?? ''); ?>"
                                                               placeholder="https://ejemplo.com/imagen.jpg">
                                                        <small class="text-muted">Si subes un archivo, la URL se ignorará.</small>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($news['imagen'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Vista Previa de la Imagen</label>
                                                <div id="image-preview" class="border rounded p-2 text-center">
                                                    <?php if ($news && $news['imagen']): ?>
                                                        <img src="<?php echo htmlspecialchars($news['imagen']); ?>" 
                                                             alt="Vista previa" class="img-fluid" style="max-height: 150px;">
                                                    <?php else: ?>
                                                        <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                                        <p class="text-muted mt-2">Sin imagen</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
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
                                                                   value="<?php echo ($carrusel_data && $carrusel_data['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($carrusel_data['fecha_inicio'])) : ''; ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                                            <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" 
                                                                   value="<?php echo ($carrusel_data && $carrusel_data['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($carrusel_data['fecha_fin'])) : ''; ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="titulo_carrusel" class="form-label">Título para el Carrusel</label>
                                                            <input type="text" class="form-control" id="titulo_carrusel" name="titulo_carrusel" 
                                                                   value="<?php echo htmlspecialchars($carrusel_data['titulo'] ?? $news['titulo'] ?? ''); ?>"
                                                                   placeholder="Título que aparecerá en el carrusel">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="descripcion_carrusel" class="form-label">Descripción para el Carrusel</label>
                                                            <textarea class="form-control" id="descripcion_carrusel" name="descripcion_carrusel" 
                                                                      rows="3" placeholder="Descripción que aparecerá en el carrusel"><?php echo htmlspecialchars($carrusel_data['descripcion'] ?? $news['resumen'] ?? ''); ?></textarea>
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
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> 
                                            <?php echo $news ? 'Actualizar' : 'Guardar'; ?> Noticia
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
        function deleteNews(id) {
            if (confirm('¿Estás seguro de que quieres eliminar esta noticia?')) {
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
        
        // Image preview functionality for file upload
        document.getElementById('imagen_file')?.addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Vista previa" class="img-fluid rounded" style="max-height: 200px;">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<div class="text-muted"><i class="fas fa-image fa-3x"></i><br><small>Sin imagen</small></div>';
            }
        });

        // Image preview functionality for URL
        document.getElementById('imagen_url')?.addEventListener('input', function() {
            const preview = document.getElementById('image-preview');
            const url = this.value.trim();
            
            if (url) {
                preview.innerHTML = `<img src="${url}" alt="Vista previa" class="img-fluid rounded" style="max-height: 200px;" onerror="this.parentElement.innerHTML='<div class=\'text-danger\'><i class=\'fas fa-exclamation-triangle\'></i><br><small>Error al cargar imagen</small></div>'">`;
            } else {
                preview.innerHTML = '<div class="text-muted"><i class="fas fa-image fa-3x"></i><br><small>Sin imagen</small></div>';
            }
        });
        
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
                const resumen = document.getElementById('resumen').value;
                
                if (titulo) {
                    document.getElementById('titulo_carrusel').value = titulo;
                }
                if (resumen) {
                    document.getElementById('descripcion_carrusel').value = resumen;
                }
            }
        });
    </script>
</body>
</html> 