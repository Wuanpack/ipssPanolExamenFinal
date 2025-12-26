<?php

class MovimientosModel
{
    private function getConnection(): array
    {
        $con = new Conexion();
        return [$con, $con->getConnection()];
    }

    /* ===========================
       CREAR SOLICITUD
       =========================== */
    public function crearSolicitud(string $rut, int $lugarId, string $nParte, int $cantidad): int
    {
        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            $usuario = $this->validarUsuarioActivo($conn, $rut);
            $herramienta = $this->validarHerramientaActiva($conn, $nParte);
            $lugar = $this->validarLugarActivo($conn, $lugarId);

            $rs = $conn->query("SELECT IFNULL(MAX(n_movimiento), 0) + 1 AS next_n FROM movimiento");
            $nMovimiento = (int) $rs->fetch_assoc()['next_n'];

            $stmt = $conn->prepare(
                "INSERT INTO movimiento (
                    n_movimiento,
                    tipo_movimiento_id,
                    herramienta_id,
                    usuario_id,
                    lugar_id,
                    fecha_solicitud,
                    cantidad,
                    activo
                ) VALUES (?, 1, ?, ?, ?, NOW(), ?, 1)"
            );

            $stmt->bind_param(
                "iiiii",
                $nMovimiento,
                $herramienta['id'],
                $usuario['id'],
                $lugarId,
                $cantidad
            );

            if (!$stmt->execute()) {
                throw new ConflictException("No se pudo crear la solicitud");
            }

            $conn->commit();
            return $nMovimiento;

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }

    /* ===========================
       ACEPTAR SOLICITUD
       =========================== */
    public function aceptarSolicitud($nMovimiento): void
    {
        if (!Validator::isPositiveInt($nMovimiento)) {
            throw new ValidationException("Parámetro 'id' inválido o no enviado");
        }

        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "SELECT
                    m.cantidad,
                    m.tipo_movimiento_id,
                    h.id AS herramienta_id,
                    h.cantidad_disponible
                 FROM movimiento m
                 JOIN herramientas h ON h.id = m.herramienta_id
                 WHERE m.n_movimiento = ?
                   AND m.activo = 1
                 FOR UPDATE"
            );
            $stmt->bind_param("i", $nMovimiento);
            $stmt->execute();
            $mov = $stmt->get_result()->fetch_assoc();

            if (!$mov) {
                throw new NotFoundException("Solicitud no encontrada");
            }

            if ((int)$mov['tipo_movimiento_id'] !== 1) {
                throw new ConflictException("La solicitud no está en estado 'Solicitado'");
            }

            if ($mov['cantidad'] > $mov['cantidad_disponible']) {
                throw new ConflictException("Stock disponible insuficiente");
            }

            $stmt = $conn->prepare(
                "UPDATE herramientas
                 SET cantidad_disponible = cantidad_disponible - ?
                 WHERE id = ?"
            );
            $stmt->bind_param("ii", $mov['cantidad'], $mov['herramienta_id']);
            $stmt->execute();

            $stmt = $conn->prepare(
                "UPDATE movimiento
                 SET tipo_movimiento_id = 2,
                     fecha_prestamo = NOW(),
                     fecha_resolucion = NOW()
                 WHERE n_movimiento = ?"
            );
            $stmt->bind_param("i", $nMovimiento);
            $stmt->execute();

            $conn->commit();

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }

    /* ===========================
       RECHAZAR SOLICITUD
       =========================== */
    public function rechazarSolicitud($nMovimiento, ?string $motivo): void
    {
        if (!Validator::isPositiveInt($nMovimiento)) {
            throw new ValidationException("Parámetro 'id' inválido o no enviado");
        }

        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "SELECT tipo_movimiento_id
                 FROM movimiento
                 WHERE n_movimiento = ?
                   AND activo = 1
                 FOR UPDATE"
            );
            $stmt->bind_param("i", $nMovimiento);
            $stmt->execute();
            $mov = $stmt->get_result()->fetch_assoc();

            if (!$mov) {
                throw new NotFoundException("Solicitud no encontrada");
            }

            if ((int)$mov['tipo_movimiento_id'] !== 1) {
                throw new ConflictException("Solo se pueden rechazar solicitudes en estado 'Solicitado'");
            }

            $motivo = $motivo ?? '';
            $stmt = $conn->prepare(
                "UPDATE movimiento
                 SET tipo_movimiento_id = 3,
                     motivo_rechazo = ?,
                     fecha_resolucion = NOW(),
                     activo = 0
                 WHERE n_movimiento = ?"
            );
            $stmt->bind_param("si", $motivo, $nMovimiento);
            $stmt->execute();

            $conn->commit();

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }

    /* ===========================
       DEVOLVER PRÉSTAMO
       =========================== */
    public function devolverPrestamo($nMovimiento): void
    {
        if (!Validator::isPositiveInt($nMovimiento)) {
            throw new ValidationException("Parámetro 'id' inválido o no enviado");
        }

        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "SELECT
                    m.cantidad,
                    m.tipo_movimiento_id,
                    h.id AS herramienta_id
                 FROM movimiento m
                 JOIN herramientas h ON h.id = m.herramienta_id
                 WHERE m.n_movimiento = ?
                   AND m.activo = 1
                 FOR UPDATE"
            );
            $stmt->bind_param("i", $nMovimiento);
            $stmt->execute();
            $mov = $stmt->get_result()->fetch_assoc();

            if (!$mov) {
                throw new NotFoundException("Préstamo no encontrado");
            }

            if ((int)$mov['tipo_movimiento_id'] !== 2) {
                throw new ConflictException("Solo se pueden devolver préstamos activos");
            }

            $stmt = $conn->prepare(
                "UPDATE herramientas
                 SET cantidad_disponible = cantidad_disponible + ?
                 WHERE id = ?"
            );
            $stmt->bind_param("ii", $mov['cantidad'], $mov['herramienta_id']);
            $stmt->execute();

            $stmt = $conn->prepare(
                "UPDATE movimiento
                 SET tipo_movimiento_id = 4,
                     fecha_devolucion = NOW(),
                     fecha_resolucion = NOW(),
                     activo = 0
                 WHERE n_movimiento = ?"
            );
            $stmt->bind_param("i", $nMovimiento);
            $stmt->execute();

            $conn->commit();

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }

    /* ===========================
       MÉTODOS PRIVADOS DE VALIDACIÓN
       =========================== */
    private function validarUsuarioActivo($conn, string $rut): array
    {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE rut = ? AND activo = 1");
        $stmt->bind_param("s", $rut);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        if (!$usuario) {
            throw new NotFoundException("Usuario no existe o está inactivo");
        }
        return $usuario;
    }

    private function validarHerramientaActiva($conn, string $nParte): array
    {
        $stmt = $conn->prepare("SELECT id FROM herramientas WHERE n_parte = ? AND activo = 1");
        $stmt->bind_param("s", $nParte);
        $stmt->execute();
        $herramienta = $stmt->get_result()->fetch_assoc();
        if (!$herramienta) {
            throw new NotFoundException("Herramienta no existe o está inactiva");
        }
        return $herramienta;
    }

    private function validarLugarActivo($conn, int $lugarId): array
    {
        $stmt = $conn->prepare("SELECT id FROM lugares WHERE id = ? AND activo = 1");
        $stmt->bind_param("i", $lugarId);
        $stmt->execute();
        $lugar = $stmt->get_result()->fetch_assoc();
        if (!$lugar) {
            throw new NotFoundException("Lugar no existe o está inactivo");
        }
        return $lugar;
    }
}
