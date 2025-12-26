<?php

/**
 * Envía una respuesta JSON estandarizada
 */
function sendJsonResponse(
    int $statusCode = 200,
    mixed $data = null,
    ?string $message = null,
    ?array $errors = null
): void {
    setDefaultHeaders();
    http_response_code($statusCode);

    $isError = $statusCode >= 400;

    $response = [
        'status' => $statusCode,
        'message' => $message ?? (
            $isError
            ? getErrorText($statusCode)
            : getSuccessText($statusCode)
        ),
        'data' => $isError ? null : $data
    ];

    if ($isError && $errors !== null) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Headers globales (CORS + JSON)
 */
function setDefaultHeaders(): void
{
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: " . CORS_ORIGIN);
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Mensajes por defecto de éxito
 */
function getSuccessText(int $statusCode): string
{
    return match ($statusCode) {
        200 => 'Operación exitosa',
        201 => 'Recurso creado exitosamente',
        204 => 'Operación completada',
        default => 'Solicitud procesada correctamente',
    };
}

/**
 * Mensajes por defecto de error
 */
function getErrorText(int $statusCode): string
{
    return match ($statusCode) {
        400 => 'Petición inválida',
        401 => 'No autorizado',
        403 => 'Permisos insuficientes',
        404 => 'Recurso no encontrado',
        405 => 'Método no permitido',
        409 => 'Conflicto en la petición',
        422 => 'Error de validación',
        500 => 'Error interno del servidor',
        501 => 'Método no implementado',
        default => 'Error inesperado',
    };
}