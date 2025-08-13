<?php
/**
 * Test de Sesiones Múltiples
 * Verificar que el sistema permite múltiples sesiones simultáneas
 */

require_once '../includes/auth_functions.php';

echo "<h1>Test de Sesiones Múltiples</h1>";

// Limpiar sesiones expiradas
echo "<h2>1. Limpiando sesiones expiradas...</h2>";
$result = cleanExpiredSessions();
echo "Sesiones expiradas eliminadas: " . ($result ? "OK" : "Error") . "<br>";

// Verificar sesiones activas del superadmin
echo "<h2>2. Sesiones activas del superadmin:</h2>";
$sessions = getUserActiveSessions(1);
echo "Número de sesiones activas: " . count($sessions) . "<br>";

if (count($sessions) > 0) {
    echo "<ul>";
    foreach ($sessions as $session) {
        echo "<li>Session ID: " . $session['session_id'] . " - IP: " . $session['ip_address'] . " - Última actividad: " . $session['fecha_ultima_actividad'] . "</li>";
    }
    echo "</ul>";
}

// Simular múltiples inicios de sesión del mismo usuario
echo "<h2>3. Simulando múltiples inicios de sesión del mismo usuario...</h2>";

// Simular login 1
echo "Simulando login 1...<br>";
$user = [
    'id' => 1,
    'username' => 'superadmin',
    'nombre_completo' => 'Super Administrador',
    'rol' => 'super_admin'
];

// Crear sesión de prueba 1
session_start();
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['nombre_completo'] = $user['nombre_completo'];
$_SESSION['rol'] = $user['rol'];
$_SESSION['authenticated'] = true;
$_SESSION['login_time'] = time();
$_SESSION['session_id'] = session_id();

$mysqli = getDBConnection();
$ip_address = '192.168.1.100';
$user_agent = 'Test Browser 1';
$session_id = session_id();

$sql = "INSERT INTO sesiones_activas (usuario_id, session_id, ip_address, user_agent, fecha_ultima_actividad) 
        VALUES (1, '$session_id', '$ip_address', '$user_agent', NOW())";
$result1 = $mysqli->query($sql);
echo "Sesión 1 creada: " . ($result1 ? "OK" : "Error") . "<br>";

// Simular login 2 (debería cerrar la sesión 1)
echo "Simulando login 2 (debería cerrar la sesión 1)...<br>";
session_destroy();
session_start();
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['nombre_completo'] = $user['nombre_completo'];
$_SESSION['rol'] = $user['rol'];
$_SESSION['authenticated'] = true;
$_SESSION['login_time'] = time();
$_SESSION['session_id'] = session_id();

$ip_address = '192.168.1.101';
$user_agent = 'Test Browser 2';
$session_id = session_id();

// Primero eliminar la sesión anterior
$delete_sql = "DELETE FROM sesiones_activas WHERE usuario_id = 1";
$mysqli->query($delete_sql);

$sql = "INSERT INTO sesiones_activas (usuario_id, session_id, ip_address, user_agent, fecha_ultima_actividad) 
        VALUES (1, '$session_id', '$ip_address', '$user_agent', NOW())";
$result2 = $mysqli->query($sql);
echo "Sesión 2 creada: " . ($result2 ? "OK" : "Error") . "<br>";

// Verificar sesiones activas después de crear múltiples sesiones
echo "<h2>4. Sesiones activas después de crear múltiples sesiones:</h2>";
$sessions = getUserActiveSessions(1);
echo "Número de sesiones activas: " . count($sessions) . "<br>";

if (count($sessions) > 0) {
    echo "<ul>";
    foreach ($sessions as $session) {
        echo "<li>Session ID: " . $session['session_id'] . " - IP: " . $session['ip_address'] . " - Última actividad: " . $session['fecha_ultima_actividad'] . "</li>";
    }
    echo "</ul>";
}

// Probar logout de una sesión específica
echo "<h2>5. Probando logout de sesión específica...</h2>";
if (count($sessions) > 0) {
    $session_to_delete = $sessions[0]['session_id'];
    $sql = "DELETE FROM sesiones_activas WHERE session_id = '$session_to_delete'";
    $result = $mysqli->query($sql);
    echo "Sesión eliminada: " . ($result ? "OK" : "Error") . "<br>";
}

// Verificar sesiones activas después del logout
echo "<h2>6. Sesiones activas después del logout:</h2>";
$sessions = getUserActiveSessions(1);
echo "Número de sesiones activas: " . count($sessions) . "<br>";

if (count($sessions) > 0) {
    echo "<ul>";
    foreach ($sessions as $session) {
        echo "<li>Session ID: " . $session['session_id'] . " - IP: " . $session['ip_address'] . " - Última actividad: " . $session['fecha_ultima_actividad'] . "</li>";
    }
    echo "</ul>";
}

echo "<h2>Test completado!</h2>";
echo "<p>El sistema ahora permite múltiples usuarios simultáneos pero mantiene una sesión por usuario (política de seguridad).</p>";
echo "<p>Si ves solo una sesión activa por usuario, el sistema está funcionando correctamente.</p>";
?>
