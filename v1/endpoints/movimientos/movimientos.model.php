<?php

class MovimientosModel
{
    private function getConnection(): array
    {
        $con = new Conexion();
        return [$con, $con->getConnection()];
    }

    public function crearSolicitud(
        string $rut,
        int $lugarId,
        string $nParte,
        int $cantidad
    ): int {
        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            /* =========================
               USUARIO
               ========================= */
            $stmt = $conn->prepare(
                "SELECT id FROM usuarios WHERE rut = ? AND activo = 1"
            );
            $stmt->bind_param("s", $rut);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();

            if (!$usuario) {
                throw new Exception("Usuario no existe o está inactivo");
            }

            /* =========================
               HERRAMIENTA
               ========================= */
            $stmt = $conn->prepare(
                "SELECT id, cantidad 
                 FROM herramientas 
                 WHERE n_parte = ? AND activo = 1"
            );
            $stmt->bind_param("s", $nParte);
            $stmt->execute();
            $herramienta = $stmt->get_result()->fetch_assoc();

            if (!$herramienta) {
                throw new Exception("Herramienta no existe o está inactiva");
            }

            if ($cantidad > (int)$herramienta['cantidad']) {
                throw new Exception("Cantidad solicitada supera el stock disponible");
            }

            /* =========================
               INSERT MOVIMIENTO (SOLICITADO)
               ========================= */
            $sql = "
                INSERT INTO movimiento (
                    n_movimiento,
                    tipo_movimiento_id,
                    herramienta_id,
                    usuario_id,
                    lugar_id,
                    fecha_prestamo,
                    cantidad,
                    activo
                )
                VALUES (
                    (SELECT IFNULL(MAX(n_movimiento), 0) + 1 FROM movimiento),
                    (SELECT id FROM tipo_movimiento WHERE nombre = 'Solicitado'),
                    ?, ?, ?, NOW(), ?, 1
                )
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "iiii",
                $herramienta['id'],
                $usuario['id'],
                $lugarId,
                $cantidad
            );
            $stmt->execute();

            /* Obtener n_movimiento generado */
            $result = $conn->query(
                "SELECT MAX(n_movimiento) AS n_movimiento FROM movimiento"
            )->fetch_assoc();

            $conn->commit();
            return (int)$result['n_movimiento'];

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }
}