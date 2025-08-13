<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';
require_once '../includes/upload_functions.php';

// Requerir autenticación y permisos
requireAuth();
requirePermission('carousel_view');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_featured'])) {
        // Procesar imagen (URL o archivo subido)
        $image_url = processImageField($_POST, $_FILES, 'image');
        
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'image' => $image_url,
            'type' => $_POST['type'],
            'content_id' => $_POST['content_id'],
            'start_date' => $_POST['start_date'] ?: null,
            'end_date' => $_POST['end_date'] ?: null
        ];
        
        if (addFeaturedContent($data)) {
            $success = 'Contenido destacado agregado exitosamente';
        } else {
            $error = 'Error al agregar contenido destacado';
        }
    }
    
    if (isset($_POST['update_featured'])) {
        $id = $_POST['id'];
        
        // Obtener imagen anterior para comparar
        $old_image = '';
        if (isset($_POST['old_image'])) {
            $old_image = $_POST['old_image'];
        }
        
        // Procesar imagen (URL o archivo subido)
        $image_url = processImageField($_POST, $_FILES, 'image', $old_image);
        
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'image' => $image_url,
            'type' => $_POST['type'],
            'content_id' => $_POST['content_id'],
            'start_date' => $_POST['start_date'] ?: null,
            'end_date' => $_POST['end_date'] ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if (updateFeaturedContent($id, $data)) {
            $success = 'Contenido destacado actualizado exitosamente';
        } else {
            $error = 'Error al actualizar contenido destacado';
        }
    }
    
    if (isset($_POST['delete_featured'])) {
        $id = $_POST['id'];
        if (deleteFeaturedContent($id)) {
            $success = 'Contenido destacado eliminado exitosamente';
        } else {
            $error = 'Error al eliminar contenido destacado';
        }
    }
}

$featured_content = getAllFeaturedContent();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Carousel - Akademia 360</title>
    
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
        .featured-item {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .date-badge {
            font-size: 0.8rem;
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
                        <a class="nav-link" href="servicios.php">
                            <i class="fas fa-briefcase"></i> Servicios
                        </a>
                        <a class="nav-link active" href="carousel.php">
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
                            <i class="fas fa-images text-info"></i> 
                            Administración de Carousel
                        </h2>
                        <a href="../index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Ver Sitio
                        </a>
                    </div>
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-images"></i> Contenido Destacado del Carousel</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($featured_content)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay contenido destacado configurado</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($featured_content as $item): ?>
                                <div class="featured-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <?php if ($item['imagen']): ?>
                                                <img src="<?php echo htmlspecialchars($item['imagen']); ?>" 
                                                     alt="Imagen" class="img-fluid rounded" style="max-height: 100px;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="height: 100px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['titulo'] ?? ''); ?></h6>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></p>
                                            <div class="mb-2">
                                                <span class="badge bg-info"><?php echo ucfirst($item['tipo'] ?? 'general'); ?></span>
                                                <?php if ($item['fecha_inicio']): ?>
                                                    <span class="badge bg-success date-badge">
                                                        Desde: <?php echo formatDate($item['fecha_inicio']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($item['fecha_fin']): ?>
                                                    <span class="badge bg-warning date-badge">
                                                        Hasta: <?php echo formatDate($item['fecha_fin']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteItem(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Agregar Contenido Destacado</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="featuredForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Imagen</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Subir imagen desde tu computadora</label>
                                        <input type="file" class="form-control" id="image_file" name="image" accept="image/*">
                                        <small class="text-muted">Formatos: JPG, PNG, GIF, WebP. Máximo 5MB.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">O usar URL de imagen</label>
                                        <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://ejemplo.com/imagen.jpg">
                                        <small class="text-muted">Si subes un archivo, la URL se ignorará.</small>
                                    </div>
                                </div>
                                <div id="image-preview" class="mt-2 border rounded p-2 text-center" style="display: none;">
                                    <img id="preview-img" src="" alt="Vista previa" class="img-fluid" style="max-height: 150px;">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type" class="form-label">Tipo de contenido</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="curso">Curso</option>
                                    <option value="noticia">Noticia</option>
                                    <option value="servicio">Servicio</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content_id" class="form-label">Contenido específico</label>
                                <select class="form-control" id="content_id" name="content_id" required>
                                    <option value="">Primero selecciona el tipo</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Fecha de inicio (opcional)</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">Fecha de fin (opcional)</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="add_featured" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i> Agregar al Carousel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Contenido Destacado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Título</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <input type="hidden" name="old_image" id="edit_old_image">
                        
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Imagen</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Subir nueva imagen</label>
                                    <input type="file" class="form-control" id="edit_image_file" name="image" accept="image/*">
                                    <small class="text-muted">Formatos: JPG, PNG, GIF, WebP. Máximo 5MB.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">O usar URL de imagen</label>
                                    <input type="url" class="form-control" id="edit_image_url" name="image_url" placeholder="https://ejemplo.com/imagen.jpg">
                                    <small class="text-muted">Si subes un archivo, la URL se ignorará.</small>
                                </div>
                            </div>
                            <div id="edit-image-preview" class="mt-2 border rounded p-2 text-center">
                                <img id="edit-preview-img" src="" alt="Vista previa" class="img-fluid" style="max-height: 150px;">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_type" class="form-label">Tipo de contenido</label>
                            <select class="form-control" id="edit_type" name="type" required>
                                <option value="curso">Curso</option>
                                <option value="noticia">Noticia</option>
                                <option value="servicio">Servicio</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_content_id" class="form-label">Contenido específico</label>
                            <select class="form-control" id="edit_content_id" name="content_id" required>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label">Fecha de inicio</label>
                                    <input type="date" class="form-control" id="edit_start_date" name="start_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">Fecha de fin</label>
                                    <input type="date" class="form-control" id="edit_end_date" name="end_date">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" checked>
                                <label class="form-check-label" for="edit_is_active">
                                    Activo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update_featured" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres eliminar este contenido destacado?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" name="delete_featured" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load content options when type changes
        document.getElementById('type').addEventListener('change', function() {
            const type = this.value;
            const contentSelect = document.getElementById('content_id');
            
            if (type) {
                // Simulate AJAX call to get available content
                fetch(`get_content.php?type=${type}`)
                    .then(response => response.json())
                    .then(data => {
                        contentSelect.innerHTML = '<option value="">Seleccionar contenido</option>';
                        data.forEach(item => {
                            contentSelect.innerHTML += `<option value="${item.id}">${item.title}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        contentSelect.innerHTML = '<option value="">Error al cargar contenido</option>';
                    });
            } else {
                contentSelect.innerHTML = '<option value="">Primero selecciona el tipo</option>';
            }
        });

        // Edit item function
        function editItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_title').value = item.titulo;
            document.getElementById('edit_description').value = item.descripcion;
            document.getElementById('edit_image').value = item.imagen;
            document.getElementById('edit_type').value = item.tipo;
            document.getElementById('edit_start_date').value = item.fecha_inicio || '';
            document.getElementById('edit_end_date').value = item.fecha_fin || '';
            document.getElementById('edit_is_active').checked = item.activo == 1;
            
            // Load content for this type
            loadContentForEdit(item.tipo, item.id_contenido);
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        // Delete item function
        function deleteItem(id) {
            document.getElementById('delete_id').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Load content for edit modal
        function loadContentForEdit(type, selectedId) {
            const contentSelect = document.getElementById('edit_content_id');
            
            fetch(`get_content.php?type=${type}`)
                .then(response => response.json())
                .then(data => {
                    contentSelect.innerHTML = '<option value="">Seleccionar contenido</option>';
                    data.forEach(item => {
                        const selected = item.id == selectedId ? 'selected' : '';
                        contentSelect.innerHTML += `<option value="${item.id}" ${selected}>${item.title}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentSelect.innerHTML = '<option value="">Error al cargar contenido</option>';
                });
        }

        // Handle edit type change
        document.getElementById('edit_type').addEventListener('change', function() {
            const type = this.value;
            loadContentForEdit(type, null);
        });

        // Image preview functionality for add form
        document.getElementById('image_file').addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        document.getElementById('image_url').addEventListener('input', function() {
            const url = this.value.trim();
            const preview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');
            
            if (url) {
                previewImg.src = url;
                preview.style.display = 'block';
                previewImg.onerror = function() {
                    preview.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i><br><small>Error al cargar imagen</small></div>';
                };
            } else {
                preview.style.display = 'none';
            }
        });

        // Image preview functionality for edit form
        document.getElementById('edit_image_file').addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('edit-image-preview');
            const previewImg = document.getElementById('edit-preview-img');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        document.getElementById('edit_image_url').addEventListener('input', function() {
            const url = this.value.trim();
            const preview = document.getElementById('edit-image-preview');
            const previewImg = document.getElementById('edit-preview-img');
            
            if (url) {
                previewImg.src = url;
                preview.style.display = 'block';
                previewImg.onerror = function() {
                    preview.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i><br><small>Error al cargar imagen</small></div>';
                };
            } else {
                preview.style.display = 'none';
            }
        });

        // Update editItem function to handle image preview
        function editItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_title').value = item.titulo;
            document.getElementById('edit_description').value = item.descripcion;
            document.getElementById('edit_image_url').value = item.imagen;
            document.getElementById('edit_old_image').value = item.imagen;
            document.getElementById('edit_type').value = item.tipo;
            document.getElementById('edit_start_date').value = item.fecha_inicio || '';
            document.getElementById('edit_end_date').value = item.fecha_fin || '';
            document.getElementById('edit_is_active').checked = item.activo == 1;
            
            // Show current image preview
            const preview = document.getElementById('edit-image-preview');
            const previewImg = document.getElementById('edit-preview-img');
            if (item.imagen) {
                previewImg.src = item.imagen;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
            
            // Load content for this type
            loadContentForEdit(item.tipo, item.id_contenido);
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html> 