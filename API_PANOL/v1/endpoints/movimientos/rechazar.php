<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/movimientos.model.php';

/*
|--------------------------------------------------------------------------
| Movimientos – POST
|--------------------------------------------------------------------------
| Rechazar solicitud
|--------------------------------------------------------------------------
*/

handleRequest(['POST'], function () {

    /* =========================
       QUERY PARAM
       ========================= */
    $nMovimiento = Validator::getIdFromQuery('n_movimiento');

    /* =========================
       BODY JSON (opcional)
       ========================= */
    $data = Validator::validateJsonInput();

    $motivo = null;

    if (isset($data['motivo'])) {
        $motivo = Validator::requireString(
            $data['motivo'],
            'motivo',
            3,
            255
        );
    }

    /* =========================
       RECHAZAR SOLICITUD
       ========================= */
    $model = new MovimientosModel();
    $model->rechazarSolicitud($nMovimiento, $motivo);

    /* =========================
       RESPUESTA
       ========================= */
    sendJsonResponse(
        200,
        null,
        "Solicitud rechazada correctamente"
    );
});
