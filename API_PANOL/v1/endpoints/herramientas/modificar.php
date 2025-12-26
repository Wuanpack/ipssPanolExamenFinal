<?php

require_once __DIR__ . '/../../../config.php';
require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';
require_once __DIR__ . '/herramientas.model.php';

/*
|--------------------------------------------------------------------------
| Herramientas – Actualizar
|--------------------------------------------------------------------------
| Actualiza los datos de una herramienta
| Método: PUT
|--------------------------------------------------------------------------
*/

handleRequest(['PUT'], function () {
    try {
        // =========================
        // QUERY PARAM
        // =========================
        $id = Validator::requirePositiveInt($_GET['id'] ?? null, 'id');

        // =========================
        // BODY JSON
        // =========================
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body) || empty($body)) {
            throw new ValidationException("Body JSON inválido o vacío");
        }

        // =========================
        // ACTUALIZAR HERRAMIENTA
        // =========================
        $model = new HerramientasModel();
        $resultado = $model->updateHerramienta($id, $body);

        // =========================
        // RESPUESTA (Swagger)
        // =========================
        sendJsonResponse(
            200,
            [
                'message' => $resultado['message'],
                'no_changes' => $resultado['no_changes']
            ],
            $resultado['message']
        );

    } catch (ValidationException $e) {
        sendJsonResponse(400, null, $e->getMessage());

    } catch (ConflictException $e) {
        sendJsonResponse(409, null, $e->getMessage());

    } catch (NotFoundException $e) {
        sendJsonResponse(404, null, $e->getMessage());

    } catch (Throwable $e) {
        sendJsonResponse(500, null, "Error interno del servidor");
    }
});
