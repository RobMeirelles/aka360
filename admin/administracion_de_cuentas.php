<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Requerir autenticación y permisos de super admin
requireSuperAdmin();
requirePermission('usuarios_view');

$mysqli = getDBConnection();
$action = $_GET['action'] ?? 'list';
$message = '';

// Procesar formularios
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = $mysqli->real_escape_string($_POST['username']);
                $email = $mysqli->real_escape_string($_POST['email']);
                $nombre_completo = $mysqli->real_escape_string($_POST['nombre_completo']);
                $rol = $mysqli->real_escape_string($_POST['rol']);
                $password = $_POST['password'];
                $permisos = $_POST['permisos'] ?? [];
                
                // Validar datos
                $errors = [];
                if (empty($username)) $errors[] = 'El nombre de usuario es obligatorio';
                if (empty($email)) $errors[] = 'El email es obligatorio';
                if (empty($nombre_completo)) $errors[] = 'El nombre completo es obligatorio';
                if (empty($password)) $errors[] = 'La contraseña es obligatoria';
                if (!validatePassword($password)) $errors[] = 'La contraseña debe tener al menos 8 caracteres, una letra y un número';
                
                if (empty($errors)) {
                    $password_hash = hashPassword($password);
                    $current_user = getCurrentUser();
                    
                    $sql = "INSERT INTO usuarios_admin (username, email, password_hash, nombre_completo, rol, creado_por) 
                            VALUES ('$username', '$email', '$password_hash', '$nombre_completo', '$rol', " . $current_user['id'] . ")";
                    
                    if ($mysqli->query($sql)) {
                        $usuario_id = $mysqli->insert_id;
                        
                        // Asignar permisos
                        foreach ($permisos as $permiso_id) {
                            $permiso_id = $mysqli->real_escape_string($permiso_id);
                            $sql_permiso = "INSERT INTO usuarios_permisos (usuario_id, permiso_id, otorgado_por) 
                                           VALUES ($usuario_id, $permiso_id, " . $current_user['id'] . ")";
                            $mysqli->query($sql_permiso);
                        }
                        
                        $message = '<div class="alert alert-success">Usuario creado exitosamente</div>';
                        logActivity($current_user['id'], 'create', 'usuarios', "Creó usuario: $username");
                        $action = 'list';
                    } else {
                        $message = '<div class="alert alert-danger">Error al crear usuario: ' . $mysqli->error . '</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
                }
                break;
                
            case 'edit':
                $id = $mysqli->real_escape_string($_POST['id']);
                $email = $mysqli->real_escape_string($_POST['email']);
                $nombre_completo = $mysqli->real_escape_string($_POST['nombre_completo']);
                $rol = $mysqli->real_escape_string($_POST['rol']);
                $activo = $mysqli->real_escape_string($_POST['activo']);
                $permisos = $_POST['permisos'] ?? [];
                
                $sql = "UPDATE usuarios_admin SET email = '$email', nombre_completo = '$nombre_completo', 
                        rol = '$rol', activo = $activo WHERE id = $id";
                
                if ($mysqli->query($sql)) {
                    // Actualizar permisos
                    $mysqli->query("DELETE FROM usuarios_permisos WHERE usuario_id = $id");
                    foreach ($permisos as $permiso_id) {
                        $permiso_id = $mysqli->real_escape_string($permiso_id);
                        $current_user = getCurrentUser();
                        $sql_permiso = "INSERT INTO usuarios_permisos (usuario_id, permiso_id, otorgado_por) 
                                       VALUES ($id, $permiso_id, " . $current_user['id'] . ")";
                        $mysqli->query($sql_permiso);
                    }
                    
                    $message = '<div class="alert alert-success">Usuario actualizado exitosamente</div>';
                    $current_user = getCurrentUser();
                    logActivity($current_user['id'], 'update', 'usuarios', "Actualizó usuario ID: $id");
                    $action = 'list';
                } else {
                    $message = '<div class="alert alert-danger">Error al actualizar usuario: ' . $mysqli->error . '</div>';
                }
                break;
                
            case 'delete':
                $id = $mysqli->real_escape_string($_POST['id']);
                
                // No permitir eliminar el propio usuario
                $current_user = getCurrentUser();
                if ($id == $current_user['id']) {
                    $message = '<div class="alert alert-danger">No puedes eliminar tu propia cuenta</div>';
                } else {
                    $sql = "DELETE FROM usuarios_admin WHERE id = $id";
                    if ($mysqli->query($sql)) {
                        $message = '<div class="alert alert-success">Usuario eliminado exitosamente</div>';
                        logActivity($current_user['id'], 'delete', 'usuarios', "Eliminó usuario ID: $id");
                    } else {
                        $message = '<div class="alert alert-danger">Error al eliminar usuario: ' . $mysqli->error . '</div>';
                    }
                }
                $action = 'list';
                break;
                
            case 'change_password':
                $id = $mysqli->real_escape_string($_POST['id']);
                $new_password = $_POST['new_password'];
                
                if (!validatePassword($new_password)) {
                    $message = '<div class="alert alert-danger">La contraseña debe tener al menos 8 caracteres, una letra y un número</div>';
                } else {
                    $password_hash = hashPassword($new_password);
                    $sql = "UPDATE usuarios_admin SET password_hash = '$password_hash' WHERE id = $id";
                    
                    if ($mysqli->query($sql)) {
                        $message = '<div class="alert alert-success">Contraseña actualizada exitosamente</div>';
                        $current_user = getCurrentUser();
                        logActivity($current_user['id'], 'change_password', 'usuarios', "Cambió contraseña de usuario ID: $id");
                    } else {
                        $message = '<div class="alert alert-danger">Error al actualizar contraseña: ' . $mysqli->error . '</div>';
                    }
                }
                $action = 'list';
                break;
        }
    }
}

// Obtener datos para editar
$usuario = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $result = $mysqli->query("SELECT * FROM usuarios_admin WHERE id = $id");
    $usuario = $result->fetch_assoc();
}

// Obtener todos los usuarios
$usuarios = [];
if ($action === 'list') {
    $result = $mysqli->query("SELECT u.*, 
                             (SELECT COUNT(*) FROM usuarios_permisos WHERE usuario_id = u.id) as total_permisos,
                             c.nombre_completo as creado_por_nombre
                             FROM usuarios_admin u 
                             LEFT JOIN usuarios_admin c ON u.creado_por = c.id
                             ORDER BY u.fecha_creacion DESC");
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

// Obtener todos los permisos
$permisos = [];
$result = $mysqli->query("SELECT * FROM permisos WHERE activo = 1 ORDER BY modulo, nombre");
while ($row = $result->fetch_assoc()) {
    $permisos[] = $row;
}

// Agrupar permisos por módulo
$permisos_por_modulo = [];
foreach ($permisos as $permiso) {
    $modulo = $permiso['modulo'];
    if (!isset($permisos_por_modulo[$modulo])) {
        $permisos_por_modulo[$modulo] = [];
    }
    $permisos_por_modulo[$modulo][] = $permiso;
}

// Obtener permisos del usuario para editar
$usuario_permisos = [];
if ($usuario) {
    $result = $mysqli->query("SELECT permiso_id FROM usuarios_permisos WHERE usuario_id = " . $usuario['id']);
    while ($row = $result->fetch_assoc()) {
        $usuario_permisos[] = $row['permiso_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Cuentas - Akademia 360</title>
    
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
        .admin-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-action {
            margin: 2px;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .alert {
            border-radius: 8px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .permission-group {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .permission-group h6 {
            color: #495057;
            margin-bottom: 10px;
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
                                <i class="fas fa-users-cog text-primary"></i> Administración de Cuentas
                            </h2>
                            <div class="text-muted">
                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <?php if ($action === 'list'): ?>
                        <!-- Lista de usuarios -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3><i class="fas fa-users"></i> Gestión de Usuarios</h3>
                            <a href="?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Usuario
                            </a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Permisos</th>
                                        <th>Último Acceso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['rol'] === 'super_admin' ? 'danger' : ($user['rol'] === 'admin' ? 'warning' : 'info'); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $user['rol'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $user['total_permisos']; ?> permisos</span>
                                            </td>
                                            <td>
                                                <?php echo $user['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($user['ultimo_acceso'])) : 'Nunca'; ?>
                                            </td>
                                            <td>
                                                <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning btn-action" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-info btn-action" title="Cambiar Contraseña" 
                                                        onclick="showChangePasswordModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                
                                                <?php if ($user['id'] != getCurrentUser()['id']): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este usuario?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger btn-action" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                    <?php else: ?>
                        <!-- Formulario de agregar/editar -->
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-user"></i> <?php echo $action === 'add' ? 'Agregar' : 'Editar'; ?> Usuario</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                                    <?php if ($action === 'edit' && $usuario): ?>
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Nombre de Usuario</label>
                                                <input type="text" class="form-control" id="username" name="username" 
                                                       value="<?php echo htmlspecialchars($usuario['username'] ?? ''); ?>" 
                                                       <?php echo $action === 'edit' ? 'readonly' : 'required'; ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre_completo" class="form-label">Nombre Completo</label>
                                                <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                                                       value="<?php echo htmlspecialchars($usuario['nombre_completo'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="rol" class="form-label">Rol</label>
                                                <select class="form-select" id="rol" name="rol" required>
                                                    <option value="">Seleccionar rol</option>
                                                    <option value="editor" <?php echo ($usuario['rol'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                    <option value="admin" <?php echo ($usuario['rol'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    <option value="super_admin" <?php echo ($usuario['rol'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($action === 'add'): ?>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Contraseña</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <small class="form-text text-muted">Mínimo 8 caracteres, al menos una letra y un número</small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($action === 'edit' && $usuario): ?>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" 
                                                       <?php echo $usuario['activo'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="activo">Usuario Activo</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Permisos -->
                                    <div class="mb-3">
                                        <label class="form-label">Permisos</label>
                                        <?php foreach ($permisos_por_modulo as $modulo => $modulo_permisos): ?>
                                            <div class="permission-group">
                                                <h6><i class="fas fa-folder"></i> <?php echo ucfirst($modulo); ?></h6>
                                                <div class="row">
                                                    <?php foreach ($modulo_permisos as $permiso): ?>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" 
                                                                       id="permiso_<?php echo $permiso['id']; ?>" 
                                                                       name="permisos[]" 
                                                                       value="<?php echo $permiso['id']; ?>"
                                                                       <?php echo in_array($permiso['id'], $usuario_permisos) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="permiso_<?php echo $permiso['id']; ?>">
                                                                    <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=list" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Crear' : 'Actualizar'; ?> Usuario
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

    <!-- Modal para cambiar contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="id" id="changePasswordUserId">
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">Mínimo 8 caracteres, al menos una letra y un número</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showChangePasswordModal(userId, username) {
            document.getElementById('changePasswordUserId').value = userId;
            document.querySelector('#changePasswordModal .modal-title').textContent = 'Cambiar Contraseña - ' + username;
            new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
        }
        
        // Validar confirmación de contraseña
        document.getElementById('changePasswordModal').addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>
