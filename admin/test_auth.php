<?php
/**
 * Archivo de prueba para el sistema de autenticación
 * Este archivo debe ser eliminado en producción
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

echo "<h1>Prueba del Sistema de Autenticación - Akademia 360</h1>";

// Probar conexión a base de datos
echo "<h2>1. Prueba de Conexión a Base de Datos</h2>";
try {
    $mysqli = getDBConnection();
    echo "✅ Conexión exitosa a la base de datos<br>";
    
    // Verificar tablas
    $tables = ['usuarios_admin', 'permisos', 'usuarios_permisos', 'sesiones_activas', 'logs_actividad'];
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Tabla '$table' existe<br>";
        } else {
            echo "❌ Tabla '$table' NO existe<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

// Probar funciones de autenticación
echo "<h2>2. Prueba de Funciones de Autenticación</h2>";

// Verificar si hay usuarios en la base de datos
$result = $mysqli->query("SELECT COUNT(*) as count FROM usuarios_admin");
$row = $result->fetch_assoc();
echo "📊 Total de usuarios en la base de datos: " . $row['count'] . "<br>";

// Verificar si hay permisos
$result = $mysqli->query("SELECT COUNT(*) as count FROM permisos");
$row = $result->fetch_assoc();
echo "📊 Total de permisos en la base de datos: " . $row['count'] . "<br>";

// Probar autenticación con usuario por defecto
echo "<h2>3. Prueba de Autenticación</h2>";
$test_result = authenticateUser('superadmin', 'admin123');
if ($test_result['success']) {
    echo "✅ Autenticación exitosa con usuario por defecto<br>";
    echo "Usuario: " . $test_result['user']['username'] . "<br>";
    echo "Rol: " . $test_result['user']['rol'] . "<br>";
    
    // Verificar permisos del super admin
    $user_id = $test_result['user']['id'];
    $result = $mysqli->query("SELECT COUNT(*) as count FROM usuarios_permisos WHERE usuario_id = $user_id");
    $row = $result->fetch_assoc();
    echo "Permisos asignados: " . $row['count'] . "<br>";
} else {
    echo "❌ Error en autenticación: " . $test_result['message'] . "<br>";
}

// Probar funciones de permisos
echo "<h2>4. Prueba de Funciones de Permisos</h2>";

// Simular sesión de super admin
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'superadmin';
$_SESSION['rol'] = 'super_admin';
$_SESSION['authenticated'] = true;

if (isAuthenticated()) {
    echo "✅ Función isAuthenticated() funciona correctamente<br>";
} else {
    echo "❌ Error en isAuthenticated()<br>";
}

if (isSuperAdmin()) {
    echo "✅ Función isSuperAdmin() funciona correctamente<br>";
} else {
    echo "❌ Error en isSuperAdmin()<br>";
}

if (hasPermission('usuarios_view')) {
    echo "✅ Función hasPermission() funciona correctamente<br>";
} else {
    echo "❌ Error en hasPermission()<br>";
}

if (hasModulePermission('usuarios')) {
    echo "✅ Función hasModulePermission() funciona correctamente<br>";
} else {
    echo "❌ Error en hasModulePermission()<br>";
}

// Probar generación de menú
echo "<h2>5. Prueba de Generación de Menú</h2>";
$menu = getNavigationMenu();
echo "📊 Elementos en el menú: " . count($menu) . "<br>";
foreach ($menu as $key => $item) {
    echo "- $key: " . $item['title'] . " (" . $item['icon'] . ")<br>";
}

// Probar funciones de contraseña
echo "<h2>6. Prueba de Funciones de Contraseña</h2>";

$test_password = "Test123";
if (validatePassword($test_password)) {
    echo "✅ Función validatePassword() funciona correctamente<br>";
} else {
    echo "❌ Error en validatePassword()<br>";
}

$hash = hashPassword($test_password);
if (password_verify($test_password, $hash)) {
    echo "✅ Función hashPassword() funciona correctamente<br>";
} else {
    echo "❌ Error en hashPassword()<br>";
}

$random_password = generateRandomPassword();
echo "🔑 Contraseña aleatoria generada: $random_password<br>";

// Probar logging
echo "<h2>7. Prueba de Logging</h2>";
if (logActivity(1, 'test', 'test', 'Prueba del sistema de autenticación')) {
    echo "✅ Función logActivity() funciona correctamente<br>";
} else {
    echo "❌ Error en logActivity()<br>";
}

// Mostrar logs recientes
echo "<h2>8. Logs Recientes</h2>";
$result = $mysqli->query("SELECT * FROM logs_actividad ORDER BY fecha DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "📝 " . $row['fecha'] . " - " . $row['accion'] . " - " . $row['modulo'] . " - " . $row['descripcion'] . "<br>";
    }
}

// Limpiar sesión de prueba
session_destroy();

echo "<h2>✅ Pruebas Completadas</h2>";
echo "<p>Si todas las pruebas muestran ✅, el sistema está funcionando correctamente.</p>";
echo "<p><strong>IMPORTANTE:</strong> Elimina este archivo en producción por seguridad.</p>";
echo "<p><a href='login.php'>Ir al Login</a></p>";
?>
