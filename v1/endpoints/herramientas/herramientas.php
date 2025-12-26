<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/herramientas.model.php';

/*
|--------------------------------------------------------------------------
| Herramientas – GET
|--------------------------------------------------------------------------
| Obtiene inventario de herramientas con paginación
|--------------------------------------------------------------------------
*/

handleRequest(['GET'], function () {

    /* =========================
       PAGINACIÓN
       ========================= */
    $page = isset($_GET['page'])
        ? Validator::requirePositiveInt($_GET['page'], 'page')
        : 1;

    $limit = isset($_GET['limit'])
        ? Validator::requirePositiveInt($_GET['limit'], 'limit')
        : 50;

    /* =========================
       OBTENER INVENTARIO
       ========================= */
    $model = new HerramientasModel();
    $result = $model->getInventario($page, $limit);

    /* =========================
       RESPUESTA
       ========================= */
    sendJsonResponse(
        200,
        $result,
        "Inventario de herramientas"
    );
});
