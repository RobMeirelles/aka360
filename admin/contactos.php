<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Requerir autenticación y permisos
requireAuth();
requirePermission('contactos_view');

$mysqli = getDBConnection();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $id = $mysqli->real_escape_string($_POST['id']);
                $sql = "DELETE FROM formularios_contacto WHERE id = $id";
                if ($mysqli->query($sql)) {
                    $message = '<div class="alert alert-success">Contacto eliminado exitosamente</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error al eliminar contacto: ' . $mysqli->error . '</div>';
                }
                $action = 'list';
                break;
        }
    }
}

// Get all contacts for listing
$contacts = [];
if ($action === 'list') {
    $result = $mysqli->query("SELECT fc.*, c.titulo as nombre_curso 
                              FROM formularios_contacto fc 
                              LEFT JOIN cursos c ON fc.id_curso = c.id 
                              ORDER BY fc.fecha_creacion DESC");
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
}

// Get contact details for viewing
$contact = null;
if ($action === 'view' && isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $result = $mysqli->query("SELECT fc.*, c.titulo as nombre_curso 
                              FROM formularios_contacto fc 
                              LEFT JOIN cursos c ON fc.id_curso = c.id 
                              WHERE fc.id = $id");
    $contact = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Contactos - Akademia 360</title>
    
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
        .contact-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .status-new {
            background: #d4edda;
            color: #155724;
        }
        .status-read {
            background: #d1ecf1;
            color: #0c5460;
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
                        <a class="nav-link" href="carousel.php">
                            <i class="fas fa-images"></i> Carrusel
                        </a>
                        <a class="nav-link active" href="contactos.php">
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
                            <i class="fas fa-envelope text-primary"></i> 
                            <?php echo $action === 'list' ? 'Gestión de Contactos' : 'Ver Contacto'; ?>
                        </h2>
                        <?php if ($action === 'view'): ?>
                        <a href="?action=list" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a la Lista
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <?php if ($action === 'list'): ?>
                        <!-- Contacts List -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Teléfono</th>
                                                <th>Curso</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contacts as $item): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($item['fecha_creacion'])); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['nombre']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['email']); ?></td>
                                                <td><?php echo htmlspecialchars($item['telefono']); ?></td>
                                                <td>
                                                    <?php if ($item['nombre_curso']): ?>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($item['nombre_curso']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">General</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['leido']): ?>
                                                        <span class="contact-status status-read">Leído</span>
                                                    <?php else: ?>
                                                        <span class="contact-status status-new">Nuevo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?action=view&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="mailto:<?php echo htmlspecialchars($item['email']); ?>" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-reply"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteContact(<?php echo $item['id']; ?>)">
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
                        
                    <?php elseif ($action === 'view' && $contact): ?>
                        <!-- Contact Details -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user"></i> Detalles del Contacto
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Información Personal</h6>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Nombre:</strong></td>
                                                <td><?php echo htmlspecialchars($contact['nombre']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                                        <?php echo htmlspecialchars($contact['email']); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Teléfono:</strong></td>
                                                <td><?php echo htmlspecialchars($contact['telefono']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Fecha:</strong></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($contact['fecha_creacion'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Información del Curso</h6>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Curso:</strong></td>
                                                <td>
                                                    <?php if ($contact['nombre_curso']): ?>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($contact['nombre_curso']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Consulta general</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Estado:</strong></td>
                                                <td>
                                                    <?php if ($contact['leido']): ?>
                                                        <span class="contact-status status-read">Leído</span>
                                                    <?php else: ?>
                                                        <span class="contact-status status-new">Nuevo</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <h6>Mensaje</h6>
                                        <div class="border rounded p-3 bg-light">
                                            <?php echo nl2br(htmlspecialchars($contact['mensaje'])); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>?subject=Respuesta a tu consulta - Akademia 360" 
                                       class="btn btn-success">
                                        <i class="fas fa-reply"></i> Responder por Email
                                    </a>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="deleteContact(<?php echo $contact['id']; ?>)">
                                        <i class="fas fa-trash"></i> Eliminar Contacto
                                    </button>
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
        function deleteContact(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este contacto?')) {
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
    </script>
</body>
</html> 