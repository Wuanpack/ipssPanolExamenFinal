<?php

require_once __DIR__ . '/../../../config.php';
require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';
require_once __DIR__ . '/herramientas.model.php';

/*
|----------------------------------------------------------------------
| Herramientas – Actualizar
|----------------------------------------------------------------------
| Actualiza los datos de una herramienta
| Método: PATCH
|----------------------------------------------------------------------
*/

handleRequest(['PUT'], function () {
    try {
        // Obtener ID desde query o ruta
        $id = Validator::requirePositiveInt($_GET['id'] ?? null, 'id');

        // Leer body JSON
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body) || empty($body)) {
            throw new ValidationException("Body JSON inválido o vacío");
        }

        $model = new HerramientasModel();
        $resultado = $model->updateHerramienta($id, $body);

        if (!empty($resultado['no_changes'])) {
            sendJsonResponse(200, null, $resultado['message']);
        } else {
            sendJsonResponse(200, null, $resultado['message']);
        }

    } catch (ValidationException $e) {
        sendJsonResponse(400, null, $e->getMessage());
    } catch (ConflictException $e) {
        sendJsonResponse(409, null, $e->getMessage());
    } catch (NotFoundException $e) {
        sendJsonResponse(404, null, $e->getMessage());
    } catch (Throwable $e) {
        // Mensaje más claro, sin filtrar detalles internos
        sendJsonResponse(500, null, "Error interno del servidor");
    }
});
