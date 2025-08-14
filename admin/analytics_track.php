<?php
/**
 * Endpoint para recibir eventos de analytics desde el frontend
 */

require_once '../config/database.php';
require_once '../includes/analytics_functions.php';

// Permitir CORS para el frontend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Obtener datos JSON del request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Validar datos requeridos
if (!isset($data['tipo_evento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required field: tipo_evento']);
    exit;
}

try {
    // Registrar el evento
    $result = trackEvent(
        $data['tipo_evento'],
        $data['categoria'] ?? '',
        $data['accion'] ?? '',
        $data['etiqueta'] ?? '',
        $data['valor'] ?? ''
    );
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Event tracked successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to track event']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
