<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/herramientas.model.php';

/*
|--------------------------------------------------------------------------
| Herramientas – Desactivar
|--------------------------------------------------------------------------
| Desactiva una herramienta (borrado lógico)
| Método: DELETE
|--------------------------------------------------------------------------
*/

handleRequest(['DELETE'], function () {

    /* =========================
       OBTENER Y VALIDAR ID
       ========================= */
    $id = Validator::requirePositiveInt(
        $_GET['id'] ?? null,
        'id'
    );

    /* =========================
       DESACTIVAR HERRAMIENTA
       ========================= */
    $model = new HerramientasModel();
    $model->setEstadoHerramienta($id, 0); // 0 = desactivar

    /* =========================
       RESPUESTA
       ========================= */
    sendJsonResponse(
        200,
        null,
        "Herramienta desactivada correctamente"
    );
});
