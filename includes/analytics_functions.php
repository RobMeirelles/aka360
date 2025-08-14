<?php
/**
 * Funciones de Analytics para Akademia 360
 * Sistema de tracking y análisis de datos
 */

require_once '../config/database.php';

/**
 * Obtener configuración de analytics
 */
function getAnalyticsConfig($key = null) {
    $mysqli = getDBConnection();
    
    if ($key) {
        $key = $mysqli->real_escape_string($key);
        $sql = "SELECT valor FROM analytics_config WHERE clave = '$key'";
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc()['valor'];
        }
        return null;
    }
    
    $sql = "SELECT clave, valor, descripcion FROM analytics_config";
    $result = $mysqli->query($sql);
    $config = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $config[$row['clave']] = $row['valor'];
        }
    }
    
    return $config;
}

/**
 * Verificar si el tracking está habilitado
 */
function isTrackingEnabled() {
    return getAnalyticsConfig('tracking_habilitado') == '1';
}

/**
 * Verificar si la IP debe ser excluida
 */
function isIPExcluded($ip) {
    $excluded_ips = getAnalyticsConfig('excluir_ips');
    if (empty($excluded_ips)) return false;
    
    $excluded_list = array_map('trim', explode(',', $excluded_ips));
    return in_array($ip, $excluded_list);
}

/**
 * Verificar si es un bot/crawler
 */
function isBot($user_agent) {
    $bots = [
        'bot', 'crawler', 'spider', 'scraper', 'googlebot', 'bingbot', 
        'yandex', 'baiduspider', 'facebookexternalhit', 'twitterbot',
        'linkedinbot', 'whatsapp', 'telegrambot'
    ];
    
    $user_agent_lower = strtolower($user_agent);
    foreach ($bots as $bot) {
        if (strpos($user_agent_lower, $bot) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Detectar dispositivo
 */
function detectDevice($user_agent) {
    $user_agent_lower = strtolower($user_agent);
    
    if (strpos($user_agent_lower, 'mobile') !== false || 
        strpos($user_agent_lower, 'android') !== false ||
        strpos($user_agent_lower, 'iphone') !== false) {
        return 'mobile';
    }
    
    if (strpos($user_agent_lower, 'tablet') !== false || 
        strpos($user_agent_lower, 'ipad') !== false) {
        return 'tablet';
    }
    
    return 'desktop';
}

/**
 * Detectar navegador
 */
function detectBrowser($user_agent) {
    if (strpos($user_agent, 'Chrome') !== false) return 'Chrome';
    if (strpos($user_agent, 'Firefox') !== false) return 'Firefox';
    if (strpos($user_agent, 'Safari') !== false) return 'Safari';
    if (strpos($user_agent, 'Edge') !== false) return 'Edge';
    if (strpos($user_agent, 'Opera') !== false) return 'Opera';
    return 'Otro';
}

/**
 * Detectar sistema operativo
 */
function detectOS($user_agent) {
    if (strpos($user_agent, 'Windows') !== false) return 'Windows';
    if (strpos($user_agent, 'Mac') !== false) return 'macOS';
    if (strpos($user_agent, 'Linux') !== false) return 'Linux';
    if (strpos($user_agent, 'Android') !== false) return 'Android';
    if (strpos($user_agent, 'iOS') !== false) return 'iOS';
    return 'Otro';
}

/**
 * Registrar visita
 */
function trackPageView($page = null) {
    if (!isTrackingEnabled()) return false;
    
    $mysqli = getDBConnection();
    
    // Obtener datos del visitante
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Verificar exclusiones
    if (isIPExcluded($ip) || isBot($user_agent)) {
        return false;
    }
    
    // Si no se especifica página, usar la actual
    if (!$page) {
        $page = $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    // Detectar información del dispositivo
    $dispositivo = detectDevice($user_agent);
    $navegador = detectBrowser($user_agent);
    $sistema_operativo = detectOS($user_agent);
    
    // Obtener session_id
    $session_id = session_id();
    
    // Escapar datos
    $ip = $mysqli->real_escape_string($ip);
    $user_agent = $mysqli->real_escape_string($user_agent);
    $page = $mysqli->real_escape_string($page);
    $referer = $mysqli->real_escape_string($referer);
    $session_id = $mysqli->real_escape_string($session_id);
    $dispositivo = $mysqli->real_escape_string($dispositivo);
    $navegador = $mysqli->real_escape_string($navegador);
    $sistema_operativo = $mysqli->real_escape_string($sistema_operativo);
    
    // Insertar visita
    $sql = "INSERT INTO analytics_visitas (ip_address, user_agent, pagina, referer, session_id, dispositivo, navegador, sistema_operativo) 
            VALUES ('$ip', '$user_agent', '$page', '$referer', '$session_id', '$dispositivo', '$navegador', '$sistema_operativo')";
    
    $result = $mysqli->query($sql);
    
    if ($result) {
        // Actualizar o crear sesión
        trackSession($session_id, $ip, $dispositivo, $navegador, $sistema_operativo);
        
        // Actualizar métricas de contenido si es una página de contenido
        updateContentMetrics($page);
        
        return true;
    }
    
    return false;
}

/**
 * Registrar sesión
 */
function trackSession($session_id, $ip, $dispositivo, $navegador, $sistema_operativo) {
    $mysqli = getDBConnection();
    
    $session_id = $mysqli->real_escape_string($session_id);
    $ip = $mysqli->real_escape_string($ip);
    $dispositivo = $mysqli->real_escape_string($dispositivo);
    $navegador = $mysqli->real_escape_string($navegador);
    $sistema_operativo = $mysqli->real_escape_string($sistema_operativo);
    
    // Verificar si la sesión ya existe
    $check_sql = "SELECT id FROM analytics_sesiones WHERE session_id = '$session_id'";
    $check_result = $mysqli->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        // Actualizar sesión existente
        $sql = "UPDATE analytics_sesiones SET 
                paginas_vistas = paginas_vistas + 1,
                fecha_fin = NOW(),
                duracion = TIMESTAMPDIFF(SECOND, fecha_inicio, NOW())
                WHERE session_id = '$session_id'";
    } else {
        // Crear nueva sesión
        $sql = "INSERT INTO analytics_sesiones (session_id, ip_address, dispositivo, navegador, sistema_operativo, paginas_vistas) 
                VALUES ('$session_id', '$ip', '$dispositivo', '$navegador', '$sistema_operativo', 1)";
    }
    
    return $mysqli->query($sql);
}

/**
 * Registrar evento
 */
function trackEvent($tipo_evento, $categoria = '', $accion = '', $etiqueta = '', $valor = '') {
    if (!isTrackingEnabled()) return false;
    
    $mysqli = getDBConnection();
    
    $session_id = session_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $page = $_SERVER['REQUEST_URI'] ?? '';
    
    // Escapar datos
    $session_id = $mysqli->real_escape_string($session_id);
    $tipo_evento = $mysqli->real_escape_string($tipo_evento);
    $categoria = $mysqli->real_escape_string($categoria);
    $accion = $mysqli->real_escape_string($accion);
    $etiqueta = $mysqli->real_escape_string($etiqueta);
    $valor = $mysqli->real_escape_string($valor);
    $page = $mysqli->real_escape_string($page);
    $ip = $mysqli->real_escape_string($ip);
    
    $sql = "INSERT INTO analytics_eventos (session_id, tipo_evento, categoria, accion, etiqueta, valor, pagina, ip_address) 
            VALUES ('$session_id', '$tipo_evento', '$categoria', '$accion', '$etiqueta', '$valor', '$page', '$ip')";
    
    return $mysqli->query($sql);
}

/**
 * Actualizar métricas de contenido
 */
function updateContentMetrics($page) {
    $mysqli = getDBConnection();
    
    // Detectar tipo de contenido basado en la URL
    $tipo_contenido = null;
    $contenido_id = null;
    
    if (preg_match('/\/curso\/(\d+)/', $page, $matches)) {
        $tipo_contenido = 'curso';
        $contenido_id = $matches[1];
    } elseif (preg_match('/\/noticia\/(\d+)/', $page, $matches)) {
        $tipo_contenido = 'noticia';
        $contenido_id = $matches[1];
    } elseif (preg_match('/\/relator\/(\d+)/', $page, $matches)) {
        $tipo_contenido = 'relator';
        $contenido_id = $matches[1];
    } elseif (preg_match('/\/servicio\/(\d+)/', $page, $matches)) {
        $tipo_contenido = 'servicio';
        $contenido_id = $matches[1];
    }
    
    if ($tipo_contenido && $contenido_id) {
        $tipo_contenido = $mysqli->real_escape_string($tipo_contenido);
        $contenido_id = (int)$contenido_id;
        
        // Verificar si ya existe el registro
        $check_sql = "SELECT id FROM analytics_contenido WHERE tipo_contenido = '$tipo_contenido' AND contenido_id = $contenido_id";
        $check_result = $mysqli->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            // Actualizar vistas existentes
            $sql = "UPDATE analytics_contenido SET vistas = vistas + 1 WHERE tipo_contenido = '$tipo_contenido' AND contenido_id = $contenido_id";
        } else {
            // Crear nuevo registro
            $sql = "INSERT INTO analytics_contenido (tipo_contenido, contenido_id, vistas) VALUES ('$tipo_contenido', $contenido_id, 1)";
        }
        
        $mysqli->query($sql);
    }
}

/**
 * Registrar envío de formulario
 */
function trackFormSubmission($tipo_formulario, $exitoso = true) {
    $mysqli = getDBConnection();
    
    $tipo_formulario = $mysqli->real_escape_string($tipo_formulario);
    
    // Verificar si ya existe el registro
    $check_sql = "SELECT id FROM analytics_formularios WHERE tipo_formulario = '$tipo_formulario'";
    $check_result = $mysqli->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        // Actualizar métricas existentes
        $sql = "UPDATE analytics_formularios SET 
                enviados = enviados + 1,
                " . ($exitoso ? "exitosos = exitosos + 1" : "fallidos = fallidos + 1") . "
                WHERE tipo_formulario = '$tipo_formulario'";
    } else {
        // Crear nuevo registro
        $enviados = 1;
        $exitosos = $exitoso ? 1 : 0;
        $fallidos = $exitoso ? 0 : 1;
        
        $sql = "INSERT INTO analytics_formularios (tipo_formulario, enviados, exitosos, fallidos) 
                VALUES ('$tipo_formulario', $enviados, $exitosos, $fallidos)";
    }
    
    return $mysqli->query($sql);
}

/**
 * Obtener estadísticas generales
 */
function getAnalyticsStats($days = 30) {
    $mysqli = getDBConnection();
    $days = (int)$days;
    
    $stats = [];
    
    // Visitas totales
    $sql = "SELECT COUNT(*) as total FROM analytics_visitas WHERE fecha_visita >= DATE_SUB(NOW(), INTERVAL $days DAY)";
    $result = $mysqli->query($sql);
    $stats['visitas_totales'] = $result->fetch_assoc()['total'];
    
    // Usuarios únicos
    $sql = "SELECT COUNT(DISTINCT ip_address) as total FROM analytics_visitas WHERE fecha_visita >= DATE_SUB(NOW(), INTERVAL $days DAY)";
    $result = $mysqli->query($sql);
    $stats['usuarios_unicos'] = $result->fetch_assoc()['total'];
    
    // Sesiones
    $sql = "SELECT COUNT(*) as total FROM analytics_sesiones WHERE fecha_inicio >= DATE_SUB(NOW(), INTERVAL $days DAY)";
    $result = $mysqli->query($sql);
    $stats['sesiones'] = $result->fetch_assoc()['total'];
    
    // Páginas más visitadas
    $sql = "SELECT pagina, COUNT(*) as vistas FROM analytics_visitas 
            WHERE fecha_visita >= DATE_SUB(NOW(), INTERVAL $days DAY) 
            GROUP BY pagina ORDER BY vistas DESC LIMIT 10";
    $result = $mysqli->query($sql);
    $stats['paginas_populares'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['paginas_populares'][] = $row;
        }
    }
    
    // Dispositivos
    $sql = "SELECT dispositivo, COUNT(*) as total FROM analytics_visitas 
            WHERE fecha_visita >= DATE_SUB(NOW(), INTERVAL $days DAY) 
            GROUP BY dispositivo";
    $result = $mysqli->query($sql);
    $stats['dispositivos'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['dispositivos'][$row['dispositivo']] = $row['total'];
        }
    }
    
    // Navegadores
    $sql = "SELECT navegador, COUNT(*) as total FROM analytics_visitas 
            WHERE fecha_visita >= DATE_SUB(NOW(), INTERVAL $days DAY) 
            GROUP BY navegador ORDER BY total DESC LIMIT 5";
    $result = $mysqli->query($sql);
    $stats['navegadores'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['navegadores'][] = $row;
        }
    }
    
    return $stats;
}

/**
 * Obtener métricas de contenido
 */
function getContentMetrics($tipo = null, $limit = 10) {
    $mysqli = getDBConnection();
    
    if ($tipo) {
        $tipo = $mysqli->real_escape_string($tipo);
        $sql = "SELECT * FROM analytics_contenido WHERE tipo_contenido = '$tipo' ORDER BY vistas DESC LIMIT $limit";
    } else {
        $sql = "SELECT * FROM analytics_contenido ORDER BY vistas DESC LIMIT $limit";
    }
    
    $result = $mysqli->query($sql);
    $metrics = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $metrics[] = $row;
        }
    }
    
    return $metrics;
}

/**
 * Obtener datos para gráficos
 */
function getChartData($days = 30) {
    $mysqli = getDBConnection();
    $days = (int)$days;
    
    $data = [];
    
    // Visitas por día
    $sql = "SELECT DATE(fecha_visita) as fecha, COUNT(*) as visitas 
            FROM analytics_visitas 
            WHERE fecha_visita >= DATE_SUB(NOW(), INTERVAL $days DAY) 
            GROUP BY DATE(fecha_visita) 
            ORDER BY fecha";
    $result = $mysqli->query($sql);
    $data['visitas_por_dia'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data['visitas_por_dia'][] = $row;
        }
    }
    
    // Usuarios únicos por día
    $sql = "SELECT DATE(fecha_visita) as fecha, COUNT(DISTINCT ip_address) as usuarios 
            FROM analytics_visitas 
            WHERE fecha_visita >= DATE_SUB(NOW(), INTERVAL $days DAY) 
            GROUP BY DATE(fecha_visita) 
            ORDER BY fecha";
    $result = $mysqli->query($sql);
    $data['usuarios_por_dia'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data['usuarios_por_dia'][] = $row;
        }
    }
    
    return $data;
}

/**
 * Limpiar datos antiguos
 */
function cleanOldAnalyticsData() {
    $mysqli = getDBConnection();
    $retention_days = getAnalyticsConfig('retener_datos_dias') ?: 90;
    
    // Limpiar visitas antiguas
    $sql = "DELETE FROM analytics_visitas WHERE fecha_visita < DATE_SUB(NOW(), INTERVAL $retention_days DAY)";
    $mysqli->query($sql);
    
    // Limpiar sesiones antiguas
    $sql = "DELETE FROM analytics_sesiones WHERE fecha_inicio < DATE_SUB(NOW(), INTERVAL $retention_days DAY)";
    $mysqli->query($sql);
    
    // Limpiar eventos antiguos
    $sql = "DELETE FROM analytics_eventos WHERE fecha_evento < DATE_SUB(NOW(), INTERVAL $retention_days DAY)";
    $mysqli->query($sql);
    
    return true;
}

/**
 * Generar reporte de analytics
 */
function generateAnalyticsReport($start_date = null, $end_date = null) {
    $mysqli = getDBConnection();
    
    if (!$start_date) $start_date = date('Y-m-d', strtotime('-30 days'));
    if (!$end_date) $end_date = date('Y-m-d');
    
    $start_date = $mysqli->real_escape_string($start_date);
    $end_date = $mysqli->real_escape_string($end_date);
    
    $report = [];
    
    // Métricas generales
    $sql = "SELECT 
                COUNT(*) as visitas_totales,
                COUNT(DISTINCT ip_address) as usuarios_unicos,
                COUNT(DISTINCT session_id) as sesiones
            FROM analytics_visitas 
            WHERE DATE(fecha_visita) BETWEEN '$start_date' AND '$end_date'";
    $result = $mysqli->query($sql);
    $report['general'] = $result->fetch_assoc();
    
    // Top páginas
    $sql = "SELECT pagina, COUNT(*) as vistas 
            FROM analytics_visitas 
            WHERE DATE(fecha_visita) BETWEEN '$start_date' AND '$end_date' 
            GROUP BY pagina ORDER BY vistas DESC LIMIT 10";
    $result = $mysqli->query($sql);
    $report['top_paginas'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $report['top_paginas'][] = $row;
        }
    }
    
    // Top contenido
    $sql = "SELECT tipo_contenido, contenido_id, vistas 
            FROM analytics_contenido 
            ORDER BY vistas DESC LIMIT 10";
    $result = $mysqli->query($sql);
    $report['top_contenido'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $report['top_contenido'][] = $row;
        }
    }
    
    return $report;
}
?>
