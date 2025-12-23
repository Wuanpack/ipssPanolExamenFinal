<?php

/* =========================
   QUERY PARAMS
   ========================= */

function getIdFromQuery(string $paramName = 'id'): ?string
{
    return $_GET[$paramName] ?? null;
}

/* =========================
   AUTH
   ========================= */

function validateAuth(): bool
{
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? null;

    if (!$auth) {
        sendJsonResponse(401, null, "Token no enviado");
        return false;
    }

    if ($auth !== 'Bearer ' . AUTH_TOKEN) {
        sendJsonResponse(403, null, "Token inválido");
        return false;
    }

    return true;
}

/* =========================
   JSON
   ========================= */

function validateJsonInput(): array
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        sendJsonResponse(400, null, "JSON inválido");
        exit;
    }

    return $data;
}

/* =========================
   TIPOS BÁSICOS
   ========================= */

function validatePositiveInt($value): bool
{
    return is_numeric($value) && (int)$value > 0;
}

function validateString(string $value, int $min = 1, int $max = 255): bool
{
    $len = mb_strlen(trim($value));
    return $len >= $min && $len <= $max;
}

/* =========================
   RUT
   ========================= */

function validateRut(string $rut): bool
{
    $rut = preg_replace('/[^0-9kK]/', '', $rut);

    if (strlen($rut) < 2) {
        return false;
    }

    $dv = strtoupper(substr($rut, -1));
    $numero = substr($rut, 0, -1);

    if (!ctype_digit($numero)) {
        return false;
    }

    $suma = 0;
    $multiplo = 2;

    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += $numero[$i] * $multiplo;
        $multiplo = ($multiplo === 7) ? 2 : $multiplo + 1;
    }

    $resto = 11 - ($suma % 11);

    $dvCalculado = match ($resto) {
        11 => '0',
        10 => 'K',
        default => (string)$resto
    };

    return $dv === $dvCalculado;
}

/* =========================
   DOMINIO PAÑOL
   ========================= */

function validateNumeroParte(string $nParte): bool
{
    return preg_match('/^[A-Z0-9\-]{3,50}$/i', $nParte) === 1;
}

function validateCantidadSolicitada(int $cantidad, int $disponible): bool
{
    return $cantidad > 0 && $cantidad <= $disponible;
}

/* =========================
   HTTP
   ========================= */

function validateMethod(array $allowed): void
{
    $method = $_SERVER['REQUEST_METHOD'];

    if (!in_array($method, $allowed, true)) {
        sendJsonResponse(405, null, "Método no permitido");
        exit;
    }
}