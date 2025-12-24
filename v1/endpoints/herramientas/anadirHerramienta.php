<?php

require_once __DIR__ . '/../../../config.php';
require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';
require_once __DIR__ . '/herramientas.model.php';

define('ALLOWED_METHODS', ['POST']);

header("Access-Control-Allow-Origin: " . CORS_ORIGIN);
header("Access-Control-Allow-Methods: " . implode(', ', ALLOWED_METHODS));
header("Content-Type: " . DEFAULT_CONTENT_TYPE);

validateMethod(ALLOWED_METHODS);

if (!validateAuth()) exit;

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $model = new HerramientasModel();
    $id = $model->crearHerramienta($data);

    sendJsonResponse(201, ['id' => $id], "Herramienta creada correctamente");

} catch (Throwable $e) {
    sendJsonResponse(400, null, $e->getMessage());
}