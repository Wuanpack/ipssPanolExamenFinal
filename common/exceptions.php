<?php

abstract class ApiException extends Exception
{
    abstract public function getStatusCode(): int;
    public function getData(): mixed { return null; }
}

class ValidationException extends ApiException
{
    public array $errors;

    public function __construct(string|array $errors)
    {
        parent::__construct("Error de validación");
        $this->errors = is_array($errors) ? $errors : [$errors];
    }

    public function getStatusCode(): int { return 400; }

    public function getData(): array { return $this->errors; }
}

class AuthException extends ApiException
{
    public function __construct(string $message = "No autorizado")
    {
        parent::__construct($message);
    }
    public function getStatusCode(): int { return 401; }
}

class MethodException extends ApiException
{
    public function __construct(string $message = "Método no permitido")
    {
        parent::__construct($message);
    }
    public function getStatusCode(): int { return 405; }
}

class BadRequestException extends ApiException
{
    public function __construct(string $message = "Solicitud inválida")
    {
        parent::__construct($message);
    }
    public function getStatusCode(): int { return 400; }
}

class NotFoundException extends ApiException
{
    public function __construct(string $message = "Recurso no encontrado")
    {
        parent::__construct($message);
    }
    public function getStatusCode(): int { return 404; }
}

class ConflictException extends ApiException
{
    public function __construct(string $message = "Conflicto de estado")
    {
        parent::__construct($message);
    }
    public function getStatusCode(): int { return 409; }
}
