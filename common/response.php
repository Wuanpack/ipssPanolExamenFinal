<?php
/**
 * Envía una respuesta JSON
 */
function sendJsonResponse(
    int $statusCode = 200,
    ?array $data = null,
    ?string $method = null,
    ?string $custom = null
): void {

    http_response_code($statusCode);
    header("Content-Type: application/json; charset=UTF-8");

    if ($statusCode >= 200 && $statusCode < 300) {
        echo json_encode([
            "status" => $statusCode,
            "message" => getMessage($statusCode),
            "data" => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            "status" => $statusCode,
            "error" => getErrorMessage($statusCode, $method, $custom)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    exit;
}

/**
 * Obtiene el mensaje de error según el código de estado
 */
function getErrorMessage(int $statusCode, ?string $method = null, ?string $custom = null): array {
    $errors = [
        400 => 'Petición Inválida',
        401 => 'El usuario no tiene autorización',
        403 => 'El cliente no posee los permisos necesarios',
        404 => 'No encontrado',
        405 => 'Método no permitido',
        409 => 'Conflicto en la petición',
        501 => 'Método [' . $method . '] no implementado'
    ];
    
    $message = $errors[$statusCode] ?? 'Error desconocido';
    
    if ($custom === null) {
        return ['ERROR [' . $statusCode . ']' => $message];
    } else {
        return ['ERROR [' . $statusCode . ']' => $message . ' | ' . $custom];
    }
}

function getMessage(int $statusCode): array {
    $messages = [
        200 => 'Operación exitosa',
        201 => 'Recurso creado exitosamente',
        204 => 'Recurso eliminado (desactivado) exitosamente'
    ];
    
    $message = $messages[$statusCode] ?? 'Operación completada';
    
    return ['MENSAJE [' . $statusCode . ']' => $message];
}
    $method = $_SERVER["REQUEST_METHOD"];
    $id = getIdFromQuery();

?>