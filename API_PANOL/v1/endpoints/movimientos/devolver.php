<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/movimientos.model.php';

/*
|----------------------------------------------------------------------
| Movimientos – POST
|----------------------------------------------------------------------
| Devolver préstamo
|----------------------------------------------------------------------
*/

handleRequest(['POST'], function () {

    // =========================
    // QUERY PARAM
    // =========================
    $nMovimiento = Validator::getIdFromQuery('n_movimiento');

    // =========================
    // DEVOLVER PRÉSTAMO
    // =========================
    $model = new MovimientosModel();
    $model->devolverPrestamo($nMovimiento);

    // =========================
    // RESPUESTA
    // =========================
    sendJsonResponse(
        200,
        null,
        "Préstamo devuelto correctamente"
    );
});
