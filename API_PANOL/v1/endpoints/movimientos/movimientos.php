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
| Crear solicitud de préstamo
|--------------------------------------------------------------------------
*/

handleRequest(['POST'], function () {

    /* =========================
       BODY JSON
       ========================= */
    $data = Validator::validateJsonInput();

    /* =========================
       VALIDACIONES
       ========================= */
    $rut      = Validator::requireRut($data['rut'] ?? '');
    $lugarId  = Validator::requirePositiveInt($data['lugar_id'] ?? null, 'lugar_id');
    $nParte   = Validator::requireNumeroParte($data['n_parte'] ?? '');
    $cantidad = Validator::requireCantidad($data['cantidad'] ?? null);

    /* =========================
       CREAR SOLICITUD
       ========================= */
    $model = new MovimientosModel();

    $nMovimiento = $model->crearSolicitud(
        $rut,
        $lugarId,
        $nParte,
        $cantidad
    );

    /* =========================
       RESPUESTA
       ========================= */
    sendJsonResponse(
        201,
        ['n_movimiento' => $nMovimiento],
        "Solicitud registrada correctamente"
    );
});
