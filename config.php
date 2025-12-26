<?php
define('BASE_PATH', __DIR__);
define('AUTH_TOKEN', 'ipss.2025.T3');

/* CORS / API defaults */
define('CORS_ORIGIN', '*');
define('DEFAULT_CONTENT_TYPE', 'application/json; charset=UTF-8');

/**
 * Manejo general de request
 */
function handleRequest(array $allowedMethods, callable $callback): void
{
    try {
        Validator::validateMethod($allowedMethods);
        Validator::validateAuth();
        $callback();

    } catch (ApiException $e) {
        // Todas nuestras excepciones personalizadas
        sendJsonResponse(
            $e->getStatusCode(),
            $e->getData(), // <-- usa getData() en lugar de $errors
            $e->getMessage()
        );

    } catch (Throwable $e) {
        // Error inesperado / 500
        error_log($e->getMessage());
        sendJsonResponse(
            500,
            null,
            'Error interno del servidor'
        );
    }
}
