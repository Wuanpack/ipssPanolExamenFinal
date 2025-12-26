<?php

class Validator
{
    /* =========================
       QUERY PARAMS
       ========================= */
    public static function getIdFromQuery(string $key = 'id'): int
    {
        if (!isset($_GET[$key])) {
            throw new ValidationException(
                "Parámetro '$key' es obligatorio"
            );
        }

        $value = $_GET[$key];

        if (!self::isPositiveInt($value)) {
            throw new ValidationException(
                "Parámetro '$key' debe ser un entero positivo"
            );
        }

        return (int) $value;
    }


    /* =========================
       AUTH
       ========================= */
    public static function validateAuth(): void
    {
        $headers = function_exists('getallheaders') ? getallheaders() : $_SERVER;
        $auth = $headers['Authorization'] ?? $headers['HTTP_AUTHORIZATION'] ?? null;

        if (!$auth) {
            throw new AuthException("Token no enviado");
        }

        if ($auth !== 'Bearer ' . AUTH_TOKEN) {
            throw new AuthException("Token inválido");
        }
    }

    /* =========================
       HTTP METHOD
       ========================= */
    public static function validateMethod(array $allowed): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if (!in_array($method, $allowed, true)) {
            throw new MethodException();
        }
    }

    /* =========================
       JSON
       ========================= */
    public static function validateJsonInput(): array
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new BadRequestException("JSON inválido");
        }

        return $data;
    }

    /* =========================
       TIPOS BÁSICOS
       ========================= */
    public static function isPositiveInt($value): bool
    {
        return is_numeric($value)
            && intval($value) == $value
            && (int) $value > 0;
    }

    public static function isNonNegativeInt($value): bool
    {
        return is_numeric($value)
            && intval($value) == $value
            && (int) $value >= 0;
    }

    public static function requirePositiveInt($value, string $field): int
    {
        if (!self::isPositiveInt($value)) {
            throw new ValidationException(
                "El campo '$field' debe ser un entero mayor a 0"
            );
        }

        return (int) $value;
    }

    public static function requireCantidad($value, string $field = 'cantidad'): int
    {
        if (!self::isNonNegativeInt($value)) {
            throw new ValidationException(
                "El campo '$field' debe ser un entero mayor o igual a 0"
            );
        }

        return (int) $value;
    }

    public static function requireString(
        $value,
        string $field,
        int $min = 1,
        int $max = 255
    ): string {
        if (!is_string($value)) {
            throw new ValidationException(
                "El campo '$field' debe ser un string"
            );
        }

        $value = trim($value);
        $len = mb_strlen($value);

        if ($len < $min || $len > $max) {
            throw new ValidationException(
                "El campo '$field' debe tener entre $min y $max caracteres"
            );
        }

        return $value;
    }

    /* =========================
       RUT
       ========================= */
    public static function requireRut(string $rut): string
    {
        if ($rut === '') {
            throw new ValidationException("El campo 'rut' es obligatorio");
        }

        $rut = preg_replace('/[^0-9kK]/', '', $rut);

        if (strlen($rut) < 2) {
            throw new ValidationException("RUT inválido");
        }

        $dv = strtoupper(substr($rut, -1));
        $numero = substr($rut, 0, -1);

        if (!ctype_digit($numero)) {
            throw new ValidationException("RUT inválido");
        }

        $suma = 0;
        $multiplo = 2;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $multiplo;
            $multiplo = ($multiplo === 7) ? 2 : $multiplo + 1;
        }

        $resto = 11 - ($suma % 11);
        $dvCalc = match ($resto) {
            11 => '0',
            10 => 'K',
            default => (string) $resto
        };

        if ($dv !== $dvCalc) {
            throw new ValidationException("RUT inválido");
        }

        return $numero . $dv; // normalizado para BD
    }

    /* =========================
       DOMINIO PAÑOL
       ========================= */
    public static function requireNumeroParte(string $nParte): string
    {
        $nParte = trim($nParte);

        if (!preg_match('/^[A-Z0-9\-]{3,50}$/i', $nParte)) {
            throw new ValidationException("Número de parte inválido");
        }

        return strtoupper($nParte);
    }
}
