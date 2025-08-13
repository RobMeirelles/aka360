<?php
require_once dirname(__DIR__) . '/config/database.php';

// Obtener contenido destacado para el carrusel con filtrado por fecha
function getFeaturedContent($limit = 5) {
    $mysqli = getDBConnection();
    $today = date('Y-m-d');
    
    // Consulta simplificada que funciona incluso si no hay datos relacionados
    $sql = "SELECT cd.*, 
            COALESCE(
                CASE 
                    WHEN cd.tipo = 'curso' THEN c.titulo
                    WHEN cd.tipo = 'noticia' THEN n.titulo
                    WHEN cd.tipo = 'servicio' THEN s.nombre
                END, cd.titulo
            ) as titulo_contenido,
            COALESCE(
                CASE 
                    WHEN cd.tipo = 'curso' THEN c.descripcion_corta
                    WHEN cd.tipo = 'noticia' THEN n.resumen
                    WHEN cd.tipo = 'servicio' THEN s.descripcion
                END, cd.descripcion
            ) as descripcion_contenido,
            COALESCE(
                CASE 
                    WHEN cd.tipo = 'curso' THEN c.imagen
                    WHEN cd.tipo = 'noticia' THEN n.imagen
                    WHEN cd.tipo = 'servicio' THEN s.imagen
                END, cd.imagen
            ) as imagen_contenido,
            r.nombre as nombre_relator,
            r.titulo as titulo_relator,
            r.universidad as universidad_relator,
            r.imagen as imagen_relator
            FROM contenido_destacado cd
            LEFT JOIN cursos c ON cd.tipo = 'curso' AND cd.id_contenido = c.id
            LEFT JOIN noticias n ON cd.tipo = 'noticia' AND cd.id_contenido = n.id
            LEFT JOIN servicios s ON cd.tipo = 'servicio' AND cd.id_contenido = s.id
            LEFT JOIN relatores r ON cd.tipo = 'curso' AND c.id_relator = r.id
            WHERE cd.activo = 1 
            AND (cd.fecha_inicio IS NULL OR cd.fecha_inicio <= '$today')
            AND (cd.fecha_fin IS NULL OR cd.fecha_fin >= '$today')
            ORDER BY cd.fecha_creacion DESC
            LIMIT $limit";
    
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Mapear nombres de columnas españoles a ingleses para el frontend
            $data[] = [
                'id' => $row['id'],
                'title' => $row['titulo_contenido'] ?: $row['titulo'],
                'description' => $row['descripcion_contenido'] ?: $row['descripcion'],
                'image' => $row['imagen_contenido'] ?: $row['imagen'],
                'type' => $row['tipo'],
                'content_id' => $row['id_contenido'],
                'start_date' => $row['fecha_inicio'],
                'end_date' => $row['fecha_fin'],
                'active' => $row['activo'],
                'created_at' => $row['fecha_creacion'],
                'relator_name' => $row['nombre_relator'],
                'relator_title' => $row['titulo_relator'],
                'relator_university' => $row['universidad_relator'],
                'relator_image' => $row['imagen_relator']
            ];
        }
    }
    return $data;
}

// Obtener cursos recientes
function getRecentCourses($limit = 6) {
    $mysqli = getDBConnection();
    $sql = "SELECT c.*, r.nombre as nombre_relator, r.imagen as imagen_relator 
            FROM cursos c 
            LEFT JOIN relatores r ON c.id_relator = r.id 
            WHERE c.activo = 1 
            ORDER BY c.fecha_creacion DESC 
            LIMIT $limit";
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Mapear nombres de columnas españoles a ingleses para el frontend
            $data[] = [
                'id' => $row['id'],
                'title' => $row['titulo'],
                'short_description' => $row['descripcion_corta'],
                'description' => $row['descripcion_completa'],
                'objectives' => $row['objetivos'],
                'detailed_program' => $row['programa_detallado'],
                'code' => $row['codigo'],
                'price' => $row['precio'],
                'payment_link' => $row['enlace_pago'],
                'image' => $row['imagen'],
                'relator_id' => $row['id_relator'],
                'category' => $row['categoria'],
                'active' => $row['activo'],
                'created_at' => $row['fecha_creacion'],
                'relator_name' => $row['nombre_relator'],
                'relator_image' => $row['imagen_relator']
            ];
        }
    }
    return $data;
}

// Obtener todos los cursos ordenados por fecha
function getAllCoursesByDate() {
    $mysqli = getDBConnection();
    $sql = "SELECT c.*, r.nombre as nombre_relator, r.imagen as imagen_relator 
            FROM cursos c 
            LEFT JOIN relatores r ON c.id_relator = r.id 
            WHERE c.activo = 1 
            ORDER BY c.tiene_fechas DESC, c.fecha_inicio ASC, c.fecha_creacion ASC";
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Mapear nombres de columnas españoles a ingleses para el frontend
            $data[] = [
                'id' => $row['id'],
                'title' => $row['titulo'],
                'short_description' => $row['descripcion_corta'],
                'description' => $row['descripcion_completa'],
                'objectives' => $row['objetivos'],
                'detailed_program' => $row['programa_detallado'],
                'code' => $row['codigo'],
                'price' => $row['precio'],
                'payment_link' => $row['enlace_pago'],
                'image' => $row['imagen'],
                'relator_id' => $row['id_relator'],
                'category' => $row['categoria'],
                'active' => $row['activo'],
                'created_at' => $row['fecha_creacion'],
                'start_date' => $row['fecha_inicio'],
                'end_date' => $row['fecha_fin'],
                'has_dates' => $row['tiene_fechas'],
                'is_quotable' => $row['es_cotizable'],
                'relator_name' => $row['nombre_relator'],
                'relator_image' => $row['imagen_relator']
            ];
        }
    }
    return $data;
}

// Obtener cursos por mes específico
function getCoursesByMonth($year, $month) {
    $mysqli = getDBConnection();
    $start_date = sprintf('%04d-%02d-01', $year, $month);
    $end_date = sprintf('%04d-%02d-%02d', $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)));
    
    $sql = "SELECT c.*, r.nombre as nombre_relator, r.imagen as imagen_relator 
            FROM cursos c 
            LEFT JOIN relatores r ON c.id_relator = r.id 
            WHERE c.activo = 1 
            AND c.tiene_fechas = TRUE
            AND (
                (c.fecha_inicio BETWEEN '$start_date' AND '$end_date')
                OR (c.fecha_fin BETWEEN '$start_date' AND '$end_date')
                OR (c.fecha_inicio <= '$start_date' AND c.fecha_fin >= '$end_date')
            )
            ORDER BY c.fecha_inicio ASC";
    
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['id'],
                'title' => $row['titulo'],
                'short_description' => $row['descripcion_corta'],
                'description' => $row['descripcion_completa'],
                'objectives' => $row['objetivos'],
                'detailed_program' => $row['programa_detallado'],
                'code' => $row['codigo'],
                'price' => $row['precio'],
                'payment_link' => $row['enlace_pago'],
                'image' => $row['imagen'],
                'relator_id' => $row['id_relator'],
                'category' => $row['categoria'],
                'active' => $row['activo'],
                'created_at' => $row['fecha_creacion'],
                'start_date' => $row['fecha_inicio'],
                'end_date' => $row['fecha_fin'],
                'has_dates' => $row['tiene_fechas'],
                'is_quotable' => $row['es_cotizable'],
                'relator_name' => $row['nombre_relator'],
                'relator_image' => $row['imagen_relator']
            ];
        }
    }
    return $data;
}

// Obtener cursos cotizables (sin fechas específicas)
function getQuotableCourses() {
    $mysqli = getDBConnection();
    $sql = "SELECT c.*, r.nombre as nombre_relator, r.imagen as imagen_relator 
            FROM cursos c 
            LEFT JOIN relatores r ON c.id_relator = r.id 
            WHERE c.activo = 1 
            AND c.es_cotizable = TRUE
            ORDER BY c.fecha_creacion DESC";
    
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['id'],
                'title' => $row['titulo'],
                'short_description' => $row['descripcion_corta'],
                'description' => $row['descripcion_completa'],
                'objectives' => $row['objetivos'],
                'detailed_program' => $row['programa_detallado'],
                'code' => $row['codigo'],
                'price' => $row['precio'],
                'payment_link' => $row['enlace_pago'],
                'image' => $row['imagen'],
                'relator_id' => $row['id_relator'],
                'category' => $row['categoria'],
                'active' => $row['activo'],
                'created_at' => $row['fecha_creacion'],
                'start_date' => $row['fecha_inicio'],
                'end_date' => $row['fecha_fin'],
                'has_dates' => $row['tiene_fechas'],
                'is_quotable' => $row['es_cotizable'],
                'relator_name' => $row['nombre_relator'],
                'relator_image' => $row['imagen_relator']
            ];
        }
    }
    return $data;
}

// Obtener noticias recientes
function getRecentNews($limit = 3) {
    $mysqli = getDBConnection();
    $sql = "SELECT * FROM noticias ORDER BY fecha_creacion DESC LIMIT $limit";
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Mapear nombres de columnas españoles a ingleses para el frontend
            $data[] = [
                'id' => $row['id'],
                'title' => $row['titulo'],
                'summary' => $row['resumen'],
                'content' => $row['contenido'],
                'image' => $row['imagen'],
                'author' => $row['autor'],
                'created_at' => $row['fecha_creacion'],
                'updated_at' => $row['fecha_actualizacion']
            ];
        }
    }
    return $data;
}

// Obtener todos los servicios
function getAllServices() {
    $mysqli = getDBConnection();
    $sql = "SELECT * FROM servicios WHERE activo = 1 ORDER BY fecha_creacion ASC";
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Mapear nombres de columnas españoles a ingleses para el frontend
            $data[] = [
                'id' => $row['id'],
                'name' => $row['nombre'],
                'description' => $row['descripcion'],
                'icon' => $row['icono'],
                'image' => $row['imagen'],
                'active' => $row['activo'],
                'created_at' => $row['fecha_creacion']
            ];
        }
    }
    return $data;
}

// Obtener todos los relatores
function getAllRelators() {
    $mysqli = getDBConnection();
    $sql = "SELECT * FROM relatores WHERE activo = 1 ORDER BY nombre ASC";
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Mapear nombres de columnas españoles a ingleses para el frontend
            $data[] = [
                'id' => $row['id'],
                'name' => $row['nombre'],
                'title' => $row['titulo'],
                'biography' => $row['biografia'],
                'specialization' => $row['especializacion'],
                'university' => $row['universidad'],
                'image' => $row['imagen'],
                'email' => $row['email'],
                'active' => $row['activo'],
                'created_at' => $row['fecha_creacion']
            ];
        }
    }
    return $data;
}

// Obtener curso por ID
function getCourseById($id) {
    $mysqli = getDBConnection();
    $id = $mysqli->real_escape_string($id);
    $sql = "SELECT c.*, r.nombre as nombre_relator, r.titulo as titulo_relator, 
            r.biografia as biografia_relator, r.especializacion, r.universidad, r.imagen as imagen_relator
            FROM cursos c 
            LEFT JOIN relatores r ON c.id_relator = r.id 
            WHERE c.id = $id AND c.activo = 1";
    $result = $mysqli->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        // Mapear nombres de columnas españoles a ingleses para el frontend
        return [
            'id' => $row['id'],
            'title' => $row['titulo'],
            'short_description' => $row['descripcion_corta'],
            'description' => $row['descripcion_completa'],
            'objectives' => $row['objetivos'],
            'detailed_program' => $row['programa_detallado'],
            'code' => $row['codigo'],
            'price' => $row['precio'],
            'payment_link' => $row['enlace_pago'],
            'image' => $row['imagen'],
            'relator_id' => $row['id_relator'],
            'category' => $row['categoria'],
            'active' => $row['activo'],
            'created_at' => $row['fecha_creacion'],
            'relator_name' => $row['nombre_relator'],
            'relator_title' => $row['titulo_relator'],
            'relator_biography' => $row['biografia_relator'],
            'relator_specialization' => $row['especializacion'],
            'relator_university' => $row['universidad'],
            'relator_image' => $row['imagen_relator']
        ];
    }
    return null;
}

// Obtener noticia por ID
function getNewsById($id) {
    $mysqli = getDBConnection();
    $id = $mysqli->real_escape_string($id);
    $sql = "SELECT * FROM noticias WHERE id = $id";
    $result = $mysqli->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        // Mapear nombres de columnas españoles a ingleses para el frontend
        return [
            'id' => $row['id'],
            'title' => $row['titulo'],
            'summary' => $row['resumen'],
            'content' => $row['contenido'],
            'image' => $row['imagen'],
            'author' => $row['autor'],
            'created_at' => $row['fecha_creacion'],
            'updated_at' => $row['fecha_actualizacion']
        ];
    }
    return null;
}

// Obtener servicio por ID
function getServiceById($id) {
    $mysqli = getDBConnection();
    $id = $mysqli->real_escape_string($id);
    $sql = "SELECT * FROM servicios WHERE id = $id AND activo = 1";
    $result = $mysqli->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        // Mapear nombres de columnas españoles a ingleses para el frontend
        return [
            'id' => $row['id'],
            'name' => $row['nombre'],
            'description' => $row['descripcion'],
            'icon' => $row['icono'],
            'image' => $row['imagen'],
            'active' => $row['activo'],
            'created_at' => $row['fecha_creacion']
        ];
    }
    return null;
}

// Guardar formulario de contacto
function saveContactSubmission($data) {
    $mysqli = getDBConnection();
    $nombre = $mysqli->real_escape_string($data['name']);
    $email = $mysqli->real_escape_string($data['email']);
    $telefono = $mysqli->real_escape_string($data['phone']);
    $mensaje = $mysqli->real_escape_string($data['message']);
    $id_curso = $data['course_id'] ? $mysqli->real_escape_string($data['course_id']) : 'NULL';
    
    $sql = "INSERT INTO formularios_contacto (nombre, email, telefono, mensaje, id_curso) 
            VALUES ('$nombre', '$email', '$telefono', '$mensaje', $id_curso)";
    return $mysqli->query($sql);
}

// Send email notification
function sendEmailNotification($to, $subject, $message) {
    $headers = "From: contacto@akademia360.cl\r\n";
    $headers .= "Reply-To: contacto@akademia360.cl\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Funciones de administración para gestión del carrusel
function addFeaturedContent($data) {
    $mysqli = getDBConnection();
    $titulo = $mysqli->real_escape_string($data['title']);
    $descripcion = $mysqli->real_escape_string($data['description']);
    $imagen = $mysqli->real_escape_string($data['image']);
    $tipo = $mysqli->real_escape_string($data['type']);
    $id_contenido = $mysqli->real_escape_string($data['content_id']);
    $fecha_inicio = $mysqli->real_escape_string($data['start_date']);
    $fecha_fin = $mysqli->real_escape_string($data['end_date']);
    
    $sql = "INSERT INTO contenido_destacado (titulo, descripcion, imagen, tipo, id_contenido, fecha_inicio, fecha_fin) 
            VALUES ('$titulo', '$descripcion', '$imagen', '$tipo', $id_contenido, '$fecha_inicio', '$fecha_fin')";
    return $mysqli->query($sql);
}

function updateFeaturedContent($id, $data) {
    $mysqli = getDBConnection();
    $id = $mysqli->real_escape_string($id);
    $titulo = $mysqli->real_escape_string($data['title']);
    $descripcion = $mysqli->real_escape_string($data['description']);
    $imagen = $mysqli->real_escape_string($data['image']);
    $tipo = $mysqli->real_escape_string($data['type']);
    $id_contenido = $mysqli->real_escape_string($data['content_id']);
    $fecha_inicio = $mysqli->real_escape_string($data['start_date']);
    $fecha_fin = $mysqli->real_escape_string($data['end_date']);
    $activo = $mysqli->real_escape_string($data['is_active']);
    
    $sql = "UPDATE contenido_destacado SET titulo = '$titulo', descripcion = '$descripcion', imagen = '$imagen', tipo = '$tipo', 
            id_contenido = $id_contenido, fecha_inicio = '$fecha_inicio', fecha_fin = '$fecha_fin', activo = $activo WHERE id = $id";
    return $mysqli->query($sql);
}

function deleteFeaturedContent($id) {
    $mysqli = getDBConnection();
    $id = $mysqli->real_escape_string($id);
    $sql = "DELETE FROM contenido_destacado WHERE id = $id";
    return $mysqli->query($sql);
}

function getAllFeaturedContent() {
    $mysqli = getDBConnection();
    $sql = "SELECT * FROM contenido_destacado ORDER BY fecha_creacion DESC";
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Obtener contenido disponible para el carrusel
function getAvailableContent($type) {
    $mysqli = getDBConnection();
    
    switch($type) {
        case 'curso':
            $sql = "SELECT id, titulo as title FROM cursos WHERE activo = 1 ORDER BY titulo ASC";
            break;
        case 'noticia':
            $sql = "SELECT id, titulo as title FROM noticias ORDER BY fecha_creacion DESC";
            break;
        case 'servicio':
            $sql = "SELECT id, nombre as title FROM servicios WHERE activo = 1 ORDER BY nombre ASC";
            break;
        default:
            return [];
    }
    
    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Utility functions
function formatDate($date) {
    if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '';
    }
    return date('d/m/Y', strtotime($date));
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateUniqueCode($prefix = 'CURSO') {
    return $prefix . '-' . strtoupper(uniqid());
}
?> 