<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';
require_once '../includes/analytics_functions.php';

// Requerir autenticación
requireAuth();

// Verificar permisos
if (!hasPermission('analytics_view')) {
    header('Location: index.php');
    exit;
}

// Obtener parámetros
$periodo = $_GET['periodo'] ?? '30';
$tipo_contenido = $_GET['tipo'] ?? '';

// Generar reporte
$report = generateAnalyticsReport();

// Configurar headers para descarga CSV
$filename = 'analytics_report_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Crear archivo CSV
$output = fopen('php://output', 'w');

// BOM para UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezado del reporte
fputcsv($output, ['REPORTE DE ANALYTICS - AKADEMIA 360']);
fputcsv($output, ['Fecha de generación: ' . date('d/m/Y H:i:s')]);
fputcsv($output, ['Período: Últimos ' . $periodo . ' días']);
fputcsv($output, ['']);

// Métricas generales
fputcsv($output, ['MÉTRICAS GENERALES']);
fputcsv($output, ['Métrica', 'Valor']);
fputcsv($output, ['Visitas Totales', $report['general']['visitas_totales']]);
fputcsv($output, ['Usuarios Únicos', $report['general']['usuarios_unicos']]);
fputcsv($output, ['Sesiones', $report['general']['sesiones']]);
fputcsv($output, ['']);

// Top páginas
fputcsv($output, ['PÁGINAS MÁS VISITADAS']);
fputcsv($output, ['Página', 'Vistas', 'Porcentaje']);
$total_views = array_sum(array_column($report['top_paginas'], 'vistas'));
foreach ($report['top_paginas'] as $page) {
    $percentage = $total_views > 0 ? round(($page['vistas'] / $total_views) * 100, 2) : 0;
    fputcsv($output, [$page['pagina'], $page['vistas'], $percentage . '%']);
}
fputcsv($output, ['']);

// Top contenido
fputcsv($output, ['CONTENIDO MÁS VISTO']);
fputcsv($output, ['Tipo', 'ID', 'Vistas']);
foreach ($report['top_contenido'] as $content) {
    fputcsv($output, [
        ucfirst($content['tipo_contenido']), 
        $content['contenido_id'], 
        $content['vistas']
    ]);
}
fputcsv($output, ['']);

// Obtener estadísticas adicionales
$stats = getAnalyticsStats($periodo);

// Dispositivos
fputcsv($output, ['DISTRIBUCIÓN POR DISPOSITIVOS']);
fputcsv($output, ['Dispositivo', 'Usuarios', 'Porcentaje']);
$total_devices = array_sum($stats['dispositivos']);
foreach ($stats['dispositivos'] as $device => $count) {
    $percentage = $total_devices > 0 ? round(($count / $total_devices) * 100, 2) : 0;
    fputcsv($output, [ucfirst($device), $count, $percentage . '%']);
}
fputcsv($output, ['']);

// Navegadores
fputcsv($output, ['DISTRIBUCIÓN POR NAVEGADORES']);
fputcsv($output, ['Navegador', 'Usuarios', 'Porcentaje']);
$total_browsers = array_sum(array_column($stats['navegadores'], 'total'));
foreach ($stats['navegadores'] as $browser) {
    $percentage = $total_browsers > 0 ? round(($browser['total'] / $total_browsers) * 100, 2) : 0;
    fputcsv($output, [$browser['navegador'], $browser['total'], $percentage . '%']);
}
fputcsv($output, ['']);

// Datos por día
fputcsv($output, ['VISITAS POR DÍA']);
fputcsv($output, ['Fecha', 'Visitas', 'Usuarios Únicos']);
$chart_data = getChartData($periodo);
foreach ($chart_data['visitas_por_dia'] as $index => $day_data) {
    $users = isset($chart_data['usuarios_por_dia'][$index]) ? $chart_data['usuarios_por_dia'][$index]['usuarios'] : 0;
    fputcsv($output, [$day_data['fecha'], $day_data['visitas'], $users]);
}

fclose($output);
exit;
?>
