<?php

require_once __DIR__ . '/../../../config.php';
require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';
require_once __DIR__ . '/herramientas.model.php';

/*
|----------------------------------------------------------------------
| Herramientas – Desactivar
|----------------------------------------------------------------------
| Desactiva una herramienta (borrado lógico)
| Método: DELETE
|----------------------------------------------------------------------
*/

handleRequest(['DELETE'], function () {
    try {
        // Validar ID
        $id = Validator::requirePositiveInt($_GET['id'] ?? null, 'id');

        $model = new HerramientasModel();
        $model->setEstadoHerramienta($id, 0); // 0 = desactivar

        sendJsonResponse(200, null, "Herramienta desactivada correctamente");

    } catch (ValidationException $e) {
        sendJsonResponse(400, null, $e->getMessage());
    } catch (ConflictException $e) {
        // Manejo de conflictos claros
        sendJsonResponse(409, null, $e->getMessage());
    } catch (NotFoundException $e) {
        sendJsonResponse(404, null, $e->getMessage());
    } catch (Throwable $e) {
        // Mensaje genérico, sin filtrar detalles internos
        sendJsonResponse(500, null, "Error interno del servidor");
    }
});
