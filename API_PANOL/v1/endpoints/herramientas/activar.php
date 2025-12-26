<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/herramientas.model.php';

/*
|--------------------------------------------------------------------------
| Herramientas – Activar
|--------------------------------------------------------------------------
| Activa una herramienta por ID (query param ?id=)
| Método: PATCH
| Requiere autenticación
|--------------------------------------------------------------------------
*/

handleRequest(['PATCH'], function () {

    /* =========================
       OBTENER Y VALIDAR ID
       ========================= */
    $id = Validator::getIdFromQuery('id');

    /* =========================
       LÓGICA DE NEGOCIO
       ========================= */
    $model = new HerramientasModel();
    $model->setEstadoHerramienta($id, 1); // 1 = activar

    /* =========================
       RESPUESTA
       ========================= */
    sendJsonResponse(
        200,
        null,
        "Herramienta activada correctamente"
    );
});
