<?php
/**
 * Funciones de Autenticación y Permisos
 * Akademia 360 - Sistema de Administración
 */

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Autenticar usuario
 */
function authenticateUser($username, $password) {
    $mysqli = getDBConnection();
    
    $username = $mysqli->real_escape_string($username);
    $sql = "SELECT id, username, email, password_hash, nombre_completo, rol, activo 
            FROM usuarios_admin 
            WHERE username = '$username' AND activo = 1";
    
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password_hash'])) {
            // Actualizar último acceso
            $update_sql = "UPDATE usuarios_admin SET ultimo_acceso = NOW() WHERE id = " . $user['id'];
            $mysqli->query($update_sql);
            
            // Crear sesión
            createUserSession($user);
            
            // Registrar log de login
            logActivity($user['id'], 'login', 'auth', 'Inicio de sesión exitoso');
            
            return [
                'success' => true,
                'user' => $user
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Usuario o contraseña incorrectos'
    ];
}

/**
 * Crear sesión de usuario
 */
function createUserSession($user) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generar session ID único
    $session_id = session_id();
    
    // Guardar datos en sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nombre_completo'] = $user['nombre_completo'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['authenticated'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['session_id'] = $session_id;
    
    // Registrar sesión en base de datos
    $mysqli = getDBConnection();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Eliminar cualquier sesión previa del mismo usuario (una sesión por usuario)
    $delete_sql = "DELETE FROM sesiones_activas WHERE usuario_id = " . $user['id'];
    $mysqli->query($delete_sql);
    
    $sql = "INSERT INTO sesiones_activas (usuario_id, session_id, ip_address, user_agent, fecha_ultima_actividad) 
            VALUES (" . $user['id'] . ", '$session_id', '$ip_address', '$user_agent', NOW())";
    $mysqli->query($sql);
}

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
        return false;
    }
    
    // Verificar que la sesión existe en la base de datos
    $mysqli = getDBConnection();
    $session_id = $_SESSION['session_id'] ?? session_id();
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $sql = "SELECT id FROM sesiones_activas 
            WHERE session_id = '$session_id' AND usuario_id = $user_id";
    $result = $mysqli->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        logout();
        return false;
    }
    
    // Actualizar última actividad
    $update_sql = "UPDATE sesiones_activas SET fecha_ultima_actividad = NOW() 
                   WHERE session_id = '$session_id'";
    $mysqli->query($update_sql);
    
    return true;
}

/**
 * Cerrar sesión
 */
function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user_id = $_SESSION['user_id'] ?? 0;
    $session_id = $_SESSION['session_id'] ?? session_id();
    
    // Eliminar sesión de la base de datos
    if ($user_id && $session_id) {
        $mysqli = getDBConnection();
        $sql = "DELETE FROM sesiones_activas WHERE session_id = '$session_id' AND usuario_id = $user_id";
        $mysqli->query($sql);
        
        // Registrar log de logout
        logActivity($user_id, 'logout', 'auth', 'Cierre de sesión');
    }
    
    // Destruir sesión
    session_destroy();
}

/**
 * Verificar si el usuario tiene un permiso específico
 */
function hasPermission($permission_code) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $mysqli = getDBConnection();
    
    $sql = "SELECT COUNT(*) as count 
            FROM usuarios_permisos up 
            JOIN permisos p ON up.permiso_id = p.id 
            WHERE up.usuario_id = $user_id AND p.codigo = '$permission_code' AND p.activo = 1";
    
    $result = $mysqli->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['count'] > 0;
    }
    
    return false;
}

/**
 * Verificar si el usuario tiene cualquier permiso de un módulo
 */
function hasModulePermission($module) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $mysqli = getDBConnection();
    
    $sql = "SELECT COUNT(*) as count 
            FROM usuarios_permisos up 
            JOIN permisos p ON up.permiso_id = p.id 
            WHERE up.usuario_id = $user_id AND p.modulo = '$module' AND p.activo = 1";
    
    $result = $mysqli->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['count'] > 0;
    }
    
    return false;
}

/**
 * Obtener todos los permisos del usuario actual
 */
function getUserPermissions() {
    if (!isAuthenticated()) {
        return [];
    }
    
    $user_id = $_SESSION['user_id'];
    $mysqli = getDBConnection();
    
    $sql = "SELECT p.codigo, p.nombre, p.modulo 
            FROM usuarios_permisos up 
            JOIN permisos p ON up.permiso_id = p.id 
            WHERE up.usuario_id = $user_id AND p.activo = 1 
            ORDER BY p.modulo, p.nombre";
    
    $result = $mysqli->query($sql);
    $permissions = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row;
        }
    }
    
    return $permissions;
}

/**
 * Obtener permisos agrupados por módulo
 */
function getUserPermissionsByModule() {
    $permissions = getUserPermissions();
    $grouped = [];
    
    foreach ($permissions as $perm) {
        $module = $perm['modulo'];
        if (!isset($grouped[$module])) {
            $grouped[$module] = [];
        }
        $grouped[$module][] = $perm;
    }
    
    return $grouped;
}

/**
 * Verificar si el usuario es super admin
 */
function isSuperAdmin() {
    if (!isAuthenticated()) {
        return false;
    }
    
    return $_SESSION['rol'] === 'super_admin';
}

/**
 * Verificar si el usuario es admin
 */
function isAdmin() {
    if (!isAuthenticated()) {
        return false;
    }
    
    return in_array($_SESSION['rol'], ['super_admin', 'admin']);
}

/**
 * Obtener información del usuario actual
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'nombre_completo' => $_SESSION['nombre_completo'],
        'rol' => $_SESSION['rol']
    ];
}

/**
 * Registrar actividad en el log
 */
function logActivity($user_id, $action, $module, $description = '') {
    $mysqli = getDBConnection();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $user_id = $mysqli->real_escape_string($user_id);
    $action = $mysqli->real_escape_string($action);
    $module = $mysqli->real_escape_string($module);
    $description = $mysqli->real_escape_string($description);
    $ip_address = $mysqli->real_escape_string($ip_address);
    
    $sql = "INSERT INTO logs_actividad (usuario_id, accion, modulo, descripcion, ip_address) 
            VALUES ($user_id, '$action', '$module', '$description', '$ip_address')";
    
    return $mysqli->query($sql);
}

/**
 * Crear hash de contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verificar si una contraseña es válida
 */
function validatePassword($password) {
    // Mínimo 8 caracteres, al menos una letra y un número
    return strlen($password) >= 8 && 
           preg_match('/[A-Za-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

/**
 * Generar contraseña aleatoria
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Obtener menú de navegación basado en permisos
 */
function getNavigationMenu() {
    $html = '';
    
    // Dashboard (siempre visible para usuarios autenticados)
    if (isAuthenticated()) {
        $html .= '<a class="nav-link active" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                  </a>';
    }
    
    // Cursos
    if (hasModulePermission('cursos')) {
        $html .= '<a class="nav-link" href="cursos.php">
                    <i class="fas fa-graduation-cap"></i> Cursos
                  </a>';
    }
    
    // Noticias
    if (hasModulePermission('noticias')) {
        $html .= '<a class="nav-link" href="noticias.php">
                    <i class="fas fa-newspaper"></i> Noticias
                  </a>';
    }
    
    // Relatores
    if (hasModulePermission('relatores')) {
        $html .= '<a class="nav-link" href="relatores.php">
                    <i class="fas fa-user-tie"></i> Relatores
                  </a>';
    }
    
    // Servicios
    if (hasModulePermission('servicios')) {
        $html .= '<a class="nav-link" href="servicios.php">
                    <i class="fas fa-briefcase"></i> Servicios
                  </a>';
    }
    
    // Carrusel
    if (hasModulePermission('carousel')) {
        $html .= '<a class="nav-link" href="carousel.php">
                    <i class="fas fa-images"></i> Carrusel
                  </a>';
    }
    
    // Contactos
    if (hasModulePermission('contactos')) {
        $html .= '<a class="nav-link" href="contactos.php">
                    <i class="fas fa-envelope"></i> Contactos
                  </a>';
    }
    
    // Analytics
    if (hasPermission('analytics_view')) {
        $html .= '<a class="nav-link" href="analytics.php">
                    <i class="fas fa-chart-line"></i> Analytics
                  </a>';
    }
    
    // Administración de Usuarios (solo super admin)
    if (isSuperAdmin() && hasModulePermission('usuarios')) {
        $html .= '<a class="nav-link" href="administracion_de_cuentas.php">
                    <i class="fas fa-users-cog"></i> Usuarios
                  </a>';
    }
    
    // Cerrar Sesión (siempre visible para usuarios autenticados)
    if (isAuthenticated()) {
        $html .= '<a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                  </a>';
    }
    
    return $html;
}

/**
 * Requerir autenticación
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Requerir permiso específico
 */
function requirePermission($permission_code) {
    requireAuth();
    
    if (!hasPermission($permission_code)) {
        header('Location: error.php?code=403');
        exit;
    }
}

/**
 * Requerir permiso de módulo
 */
function requireModulePermission($module) {
    requireAuth();
    
    if (!hasModulePermission($module)) {
        header('Location: error.php?code=403');
        exit;
    }
}

/**
 * Requerir rol de super admin
 */
function requireSuperAdmin() {
    requireAuth();
    
    if (!isSuperAdmin()) {
        header('Location: error.php?code=403');
        exit;
    }
}

/**
 * Limpiar sesiones expiradas
 */
function cleanExpiredSessions($user_id = null) {
    $mysqli = getDBConnection();
    
    if ($user_id) {
        // Limpiar sesiones expiradas de un usuario específico
        $sql = "DELETE FROM sesiones_activas 
                WHERE usuario_id = $user_id 
                AND fecha_ultima_actividad < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    } else {
        // Limpiar todas las sesiones expiradas
        $sql = "DELETE FROM sesiones_activas 
                WHERE fecha_ultima_actividad < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    }
    
    return $mysqli->query($sql);
}

/**
 * Obtener sesiones activas de un usuario
 */
function getUserActiveSessions($user_id) {
    $mysqli = getDBConnection();
    
    $sql = "SELECT session_id, ip_address, user_agent, fecha_ultima_actividad 
            FROM sesiones_activas 
            WHERE usuario_id = $user_id
            ORDER BY fecha_ultima_actividad DESC";
    
    $result = $mysqli->query($sql);
    $sessions = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
    }
    
    return $sessions;
}

/**
 * Cerrar todas las sesiones de un usuario (excepto la actual)
 */
function closeAllUserSessions($user_id, $current_session_id = null) {
    $mysqli = getDBConnection();
    
    if ($current_session_id) {
        $sql = "DELETE FROM sesiones_activas 
                WHERE usuario_id = $user_id AND session_id != '$current_session_id'";
    } else {
        $sql = "DELETE FROM sesiones_activas WHERE usuario_id = $user_id";
    }
    
    return $mysqli->query($sql);
}
?>
