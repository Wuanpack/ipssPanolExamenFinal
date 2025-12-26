<?php

class HerramientasModel
{
    private function getConnection(): array
    {
        $con = new Conexion();
        return [$con, $con->getConnection()];
    }

    public function getHerramientaById(int $id): ?array
    {
        [$con, $conn] = $this->getConnection();

        try {
            $stmt = $conn->prepare(
                "SELECT id, n_parte, nombre, figura, indice, pagina, cantidad, cantidad_disponible, activo
                 FROM herramientas
                 WHERE id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $herr = $stmt->get_result()->fetch_assoc();
            return $herr ?: null;
        } finally {
            $con->closeConnection();
        }
    }

    public function getInventario(int $page = 1, int $limit = 50): array
    {
        [$con, $conn] = $this->getConnection();

        try {
            $page = max(1, $page);
            $limit = max(1, min($limit, 100));
            $offset = ($page - 1) * $limit;

            $stmtTotal = $conn->prepare(
                "SELECT COUNT(*) AS total FROM herramientas WHERE activo = 1"
            );
            $stmtTotal->execute();
            $total = (int) $stmtTotal->get_result()->fetch_assoc()['total'];

            $stmt = $conn->prepare(
                "SELECT id, n_parte, nombre, figura, indice, pagina, cantidad, cantidad_disponible, activo
                 FROM herramientas
                 WHERE activo = 1
                 ORDER BY id ASC
                 LIMIT ? OFFSET ?"
            );
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            $herramientas = [];
            while ($row = $result->fetch_assoc()) {
                $herramientas[] = $row;
            }

            return [
                'herramientas' => $herramientas,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit)
                ]
            ];
        } finally {
            $con->closeConnection();
        }
    }

    public function crearHerramienta(array $data): int
    {
        if (empty($data)) {
            throw new ValidationException("Body JSON inválido o vacío");
        }

        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            $camposObligatorios = [
                'n_parte',
                'nombre',
                'figura',
                'indice',
                'pagina',
                'cantidad',
                'cantidad_disponible'
            ];

            foreach ($camposObligatorios as $campo) {
                if (!isset($data[$campo]) || $data[$campo] === '') {
                    throw new ValidationException("Falta o es inválido el campo obligatorio: $campo");
                }
            }

            $data['n_parte'] = Validator::requireString($data['n_parte'], 'n_parte', 3, 50);
            $data['nombre'] = Validator::requireString($data['nombre'], 'nombre');
            $data['figura'] = Validator::requireCantidad($data['figura'], 'figura');
            $data['indice'] = Validator::requireString($data['indice'], 'indice');
            $data['pagina'] = Validator::requireString($data['pagina'], 'pagina');
            $data['cantidad'] = Validator::requireCantidad($data['cantidad'], 'cantidad');
            $data['cantidad_disponible'] = Validator::requireCantidad($data['cantidad_disponible'], 'cantidad_disponible');

            if ($data['cantidad_disponible'] > $data['cantidad']) {
                throw new ValidationException("La cantidad disponible no puede ser mayor a la cantidad total");
            }

            // Verificar duplicados
            $stmt = $conn->prepare("SELECT id FROM herramientas WHERE n_parte = ? AND activo = 1");
            $stmt->bind_param("s", $data['n_parte']);
            $stmt->execute();

            if ($stmt->get_result()->fetch_assoc()) {
                throw new ConflictException("El número de parte ya existe en otra herramienta");
            }

            // Insertar
            $stmt = $conn->prepare(
                "INSERT INTO herramientas (n_parte, nombre, figura, indice, pagina, cantidad, cantidad_disponible, activo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $stmt->bind_param(
                "ssssiii",
                $data['n_parte'],
                $data['nombre'],
                $data['figura'],
                $data['indice'],
                $data['pagina'],
                $data['cantidad'],
                $data['cantidad_disponible']
            );

            if (!$stmt->execute()) {
                throw new BadRequestException("Error al crear herramienta");
            }

            $id = $stmt->insert_id;
            $conn->commit();
            return $id;

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }

    public function updateHerramienta(int $id, array $data): array
    {
        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            $actual = $this->getHerramientaById($id);
            if (!$actual) {
                throw new NotFoundException("Herramienta no encontrada");
            }

            $camposPermitidos = ['n_parte', 'nombre', 'figura', 'indice', 'pagina', 'cantidad', 'cantidad_disponible'];
            $set = [];
            $params = [];
            $types = '';

            foreach ($camposPermitidos as $campo) {
                if (!array_key_exists($campo, $data)) {
                    continue;
                }

                $valor = $data[$campo];

                // Validaciones
                if (in_array($campo, ['cantidad', 'cantidad_disponible', 'figura'], true)) {
                    $valor = Validator::requireCantidad($valor, $campo);
                    if ($campo === 'cantidad_disponible') {
                        $cantidad = $data['cantidad'] ?? $actual['cantidad'];
                        if ($valor > $cantidad) {
                            throw new BadRequestException("La cantidad disponible no puede ser mayor a la cantidad total");
                        }
                    }
                    if ($valor != $actual[$campo]) {
                        $set[] = "$campo = ?";
                        $params[] = $valor;
                        $types .= 'i';
                    }
                } else {
                    $valor = Validator::requireString($valor, $campo, 1, 255);
                    if ($campo === 'n_parte' && $valor !== $actual['n_parte']) {
                        // Validar unicidad
                        $stmt = $conn->prepare("SELECT id FROM herramientas WHERE n_parte = ? AND id != ? AND activo = 1");
                        $stmt->bind_param("si", $valor, $id);
                        $stmt->execute();
                        if ($stmt->get_result()->fetch_assoc()) {
                            throw new ConflictException("El número de parte ya existe en otra herramienta");
                        }
                    }
                    if ($valor !== $actual[$campo]) {
                        $set[] = "$campo = ?";
                        $params[] = $valor;
                        $types .= 's';
                    }
                }
            }

            if (empty($set)) {
                // No hay cambios, devolvemos info pero con status 200
                return ['message' => 'No se realizaron cambios', 'no_changes' => true];
            }

            $sql = "UPDATE herramientas SET " . implode(', ', $set) . " WHERE id = ?";
            $params[] = $id;
            $types .= 'i';

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if (!$stmt->execute()) {
                throw new BadRequestException("Error al actualizar herramienta");
            }

            $conn->commit();

            return ['message' => 'Herramienta actualizada correctamente', 'no_changes' => false];
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }


    public function setEstadoHerramienta(int $id, int $activo): void
    {
        if (!in_array($activo, [0, 1], true)) {
            throw new ValidationException("Estado inválido");
        }

        [$con, $conn] = $this->getConnection();

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "SELECT activo FROM herramientas WHERE id = ? FOR UPDATE"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $herr = $stmt->get_result()->fetch_assoc();

            if (!$herr) {
                throw new NotFoundException("Herramienta no existe");
            }
            if ((int) $herr['activo'] === $activo) {
                throw new BadRequestException("La herramienta ya se encuentra en ese estado");
            }

            if ($activo === 0) {
                $stmt = $conn->prepare(
                    "SELECT COUNT(*) AS total
                     FROM movimiento
                     WHERE herramienta_id = ?
                       AND tipo_movimiento_id = 2
                       AND activo = 1"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();

                if ((int) $row['total'] > 0) {
                    throw new BadRequestException("No se puede desactivar la herramienta con préstamos activos");
                }
            }

            $stmt = $conn->prepare(
                "UPDATE herramientas SET activo = ? WHERE id = ?"
            );
            $stmt->bind_param("ii", $activo, $id);
            $stmt->execute();

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $con->closeConnection();
        }
    }
}
