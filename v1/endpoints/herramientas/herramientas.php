<?php

require_once __DIR__ . '/../../../config.php';
require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';
require_once __DIR__ . '/herramientas.model.php';

header("Access-Control-Allow-Origin: " . CORS_ORIGIN);
header("Content-Type: " . DEFAULT_CONTENT_TYPE);
validateMethod(['GET']);

if (!validateAuth()) exit;

$model = new HerramientasModel();

// Leer parámetros de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

try {
    $result = $model->getInventario($page, $limit);
    sendJsonResponse(200, $result, "Inventario de herramientas");
} catch (Throwable $e) {
    sendJsonResponse(500, null, $e->getMessage());
}