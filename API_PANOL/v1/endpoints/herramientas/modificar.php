<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/herramientas.model.php';

/*
|--------------------------------------------------------------------------
| Herramientas – PUT
|--------------------------------------------------------------------------
| Modifica una herramienta existente
|--------------------------------------------------------------------------
*/

handleRequest(['PUT'], function() {
    $id = Validator::getIdFromQuery('id');
    $data = Validator::validateJsonInput();

    $herrModel = new HerramientasModel();
    $result = $herrModel->updateHerramienta($id, $data);

    sendJsonResponse(200, $result, 'OK');
});
