<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/dashboard.model.php';

/*
|--------------------------------------------------------------------------
| Dashboard – GET
|--------------------------------------------------------------------------
| Obtiene:
| - KPIs generales del pañol
| - Movimientos recientes
|--------------------------------------------------------------------------
*/

handleRequest(['GET'], function () {

    $model = new DashboardModel();
    $data = $model->getDashboardData();

    sendJsonResponse(
        200,
        $data,
        "Dashboard obtenido correctamente"
    );
});