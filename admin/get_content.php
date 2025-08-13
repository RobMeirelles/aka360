<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['type'])) {
    echo json_encode(['error' => 'Tipo no especificado']);
    exit;
}

$type = $_GET['type'];
$content = getAvailableContent($type);

echo json_encode($content);
?> 