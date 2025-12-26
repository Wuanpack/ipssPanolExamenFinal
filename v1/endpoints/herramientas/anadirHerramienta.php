<?php

require_once __DIR__ . '/../../../config.php';

require_once BASE_PATH . '/common/database.php';
require_once BASE_PATH . '/common/exceptions.php';
require_once BASE_PATH . '/common/validators.php';
require_once BASE_PATH . '/common/response.php';

require_once __DIR__ . '/herramientas.model.php';

/*
|--------------------------------------------------------------------------
| Herramientas – Crear
|--------------------------------------------------------------------------
| Crea una nueva herramienta
| Método: POST
| Body JSON requerido
|--------------------------------------------------------------------------
*/

handleRequest(['POST'], function () {

    /* =========================
       JSON BODY
       ========================= */
    $data = Validator::validateJsonInput();

    /* =========================
       VALIDACIONES DE CAMPOS
       ========================= */
    $payload = [
        'n_parte' => Validator::requireNumeroParte(
            Validator::requireString($data['n_parte'] ?? null, 'n_parte', 3, 50)
        ),

        'nombre' => Validator::requireString(
            $data['nombre'] ?? null,
            'nombre',
            3,
            255
        ),

        'figura' => Validator::requirePositiveInt(
            $data['figura'] ?? null,
            'figura'
        ),

        'indice' => Validator::requireString(
            $data['indice'] ?? null,
            'indice',
            1,
            50
        ),

        'pagina' => Validator::requireString(
            $data['pagina'] ?? null,
            'pagina',
            1,
            50
        ),

        'cantidad' => Validator::requireCantidad(
            $data['cantidad'] ?? null,
            'cantidad'
        ),

        'cantidad_disponible' => Validator::requireCantidad(
            $data['cantidad_disponible'] ?? null,
            'cantidad_disponible'
        ),
    ];

    /* =========================
       VALIDACIÓN DE NEGOCIO
       ========================= */
    if ($payload['cantidad_disponible'] > $payload['cantidad']) {
        throw new ValidationException(
            "La cantidad disponible no puede ser mayor a la cantidad total"
        );
    }

    /* =========================
       CREAR HERRAMIENTA
       ========================= */
    $model = new HerramientasModel();
    $id = $model->crearHerramienta($payload);

    /* =========================
       RESPUESTA
       ========================= */
    sendJsonResponse(
        201,
        ['id' => $id],
        "Herramienta creada correctamente"
    );
});
