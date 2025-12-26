DROP TABLE IF EXISTS movimiento;
DROP TABLE IF EXISTS lugares;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS herramientas;
DROP TABLE IF EXISTS secuencia_movimiento;
DROP TABLE IF EXISTS tipo_movimiento;
DROP TABLE IF EXISTS movimiento;

CREATE TABLE herramientas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    n_parte VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(225) NOT NULL,
    figura INT NOT NULL,
    indice VARCHAR(10) NOT NULL,
    pagina VARCHAR(10) NOT NULL,
    cantidad INT NOT NULL,
    cantidad_disponible INT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(9) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE lugares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE tipo_movimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE movimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    n_movimiento INT NOT NULL UNIQUE,
    tipo_movimiento_id INT NOT NULL DEFAULT 1,

    herramienta_id INT NOT NULL,
    usuario_id INT NOT NULL,
    lugar_id INT NOT NULL,

    fecha_solicitud DATETIME NOT NULL,
    fecha_prestamo DATETIME NULL,
    fecha_devolucion DATETIME,
    fecha_resolucion DATETIME NULL,
    motivo_rechazo VARCHAR(255) NULL,
    cantidad INT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,

    FOREIGN KEY (tipo_movimiento_id) REFERENCES tipo_movimiento(id),
    FOREIGN KEY (herramienta_id) REFERENCES herramientas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (lugar_id) REFERENCES lugares(id)
);

-- Tabla de secuencia segura
CREATE TABLE secuencia_movimiento (
    valor INT NOT NULL
);

INSERT INTO secuencia_movimiento VALUES (0);

-- Trigger para asignar n_movimiento de forma segura
DELIMITER $$

CREATE TRIGGER trg_movimiento_n
BEFORE INSERT ON movimiento
FOR EACH ROW
BEGIN
    UPDATE secuencia_movimiento
    SET valor = LAST_INSERT_ID(valor + 1);

    SET NEW.n_movimiento = LAST_INSERT_ID();
END$$

DELIMITER ;

-- Trigger para evitar desactivar herramienta con préstamos activos
DELIMITER $$

CREATE TRIGGER trg_bloquear_desactivar_herramienta
BEFORE UPDATE ON herramientas
FOR EACH ROW
BEGIN
    IF OLD.activo = TRUE AND NEW.activo = FALSE THEN
        IF EXISTS (
            SELECT 1
            FROM movimiento
            WHERE herramienta_id = OLD.id
              AND fecha_devolucion IS NULL
              AND activo = TRUE
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede desactivar la herramienta: tiene préstamos activos';
        END IF;
    END IF;
END$$

DELIMITER ;


INSERT INTO herramientas(n_parte, nombre, figura, indice, pagina, cantidad, cantidad_disponible, activo) VALUES
('21C6016G01', 'CLAMP, Alignment IGV', 56, '1/1', '2-261', 1, 1, TRUE),
('21C6011G01', 'SUPPORT, Stator Half', 57, '19/1', '2-271', 2, 2, TRUE),
('21C506G001', 'PUSHER, Hydraulic, Turbine Rotor Assembly', 61, '13/1', '2-292', 2, 2, TRUE),
('21C532', 'PULLER, Hydraulic, Turbine Rotor Assembly', 61, '15/1', '2-292', 2, 2, TRUE),
('21C505', 'PUSHER, Bearing and Seal Runner', 61, '12/1', '2-292', 1, 1, TRUE),
('21C531', 'WRENCH, Spanner, Locknut', 63, '20/1', '2-301', 1, 1, TRUE),
('21C559G001', 'GAGE, Inspection, Fuel Nozzle and Combustion Liner Clearance', 61, '16/1', '2-292', 2, 2, TRUE),
('21C3135G01', 'PLIERS, Piston, VEN Actuator', 69, '14/1', '2-317', 1, 1, TRUE),
('21C2546P011', 'WRENCH, Special, Input Shaft, MFC', 70, '1/1', '2-325', 4, 4, TRUE),
('21C518-11', 'CAP, Pusher', 63, '18/1', '2-301', 2, 2, TRUE),
('21C6092G01', 'WRENCH, Spanner, VEN Power Unit', 71, '13', '2-331', 2, 2, TRUE),
('21C577G002', 'PUSHER, Mechanical', 55, '1', '2-259', 1, 1, TRUE),
('21C2689G02', 'PULLER, Hydraulic, Turbine Rotor Bearing and Seal Runner', 86, '1', '2-358', 1, 1, TRUE);

INSERT INTO usuarios (rut, nombre, apellido_paterno, apellido_materno, activo) VALUES
('123456785', 'Juan', 'Perez', 'Gonzalez', TRUE),
('111111111', 'Maria', 'Lopez', 'Fernandez', TRUE),
('222222222', 'Carlos', 'Sanchez', 'Ramirez', TRUE),
('876543214', 'Ana', 'Torres', 'Diaz', TRUE),
('135724680', 'Luis', 'Martinez', 'Vargas', TRUE),
('24681357K', 'Pedro', 'Rojas', 'Muñoz', TRUE),
('98765433',  'Camila', 'Silva', 'Contreras', TRUE),
('765432106', 'Jorge', 'Pinto', 'Araya', TRUE),
('192837469', 'Daniela', 'Fuentes', 'Navarro', TRUE),
('159357287', 'Felipe', 'Morales', 'Castro', TRUE);

INSERT INTO lugares(nombre, activo) VALUES
('Linea F5', TRUE),
('E-301', TRUE),
('E-425', TRUE),
('Banco de Pruebas', TRUE),
('Punto Fijo', TRUE);

INSERT INTO tipo_movimiento (id, nombre) VALUES
(1, 'Solicitado'),
(2, 'Prestado'),
(3, 'Rechazado'),
(4, 'Devolución');

INSERT INTO movimiento (
    tipo_movimiento_id,
    herramienta_id,
    usuario_id,
    lugar_id,
    fecha_solicitud,
    fecha_prestamo,
    fecha_devolucion,
    fecha_resolucion,
    motivo_rechazo,
    cantidad,
    activo
) VALUES

-- Movimiento 1: Solicitud normal
(1, 1, 1, 1, NOW(), NULL, NULL, NULL, NULL, 1, TRUE),

-- Movimiento 2: Prestado y aún no devuelto
(2, 2, 2, 2, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 2 DAY, NULL, NULL, NULL, 1, TRUE),

-- Movimiento 3: Prestado y devuelto
(2, 3, 3, 3, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 7 DAY, NULL, 1, TRUE),

-- Movimiento 4: Solicitud rechazada
(3, 4, 4, 4, NOW() - INTERVAL 5 DAY, NULL, NULL, NOW() - INTERVAL 4 DAY, 'Cantidad insuficiente', 1, TRUE),

-- Movimiento 5: Devolución procesada
(4, 5, 5, 1, NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 6 DAY, NOW() - INTERVAL 6 DAY, NULL, 1, TRUE),

-- Movimiento 6: Solicitud pendiente
(1, 6, 6, 2, NOW(), NULL, NULL, NULL, NULL, 2, TRUE),

-- Movimiento 7: Prestado
(2, 7, 7, 3, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 1 DAY, NULL, NULL, NULL, 1, TRUE),

-- Movimiento 8: Solicitud rechazada
(3, 8, 8, 4, NOW() - INTERVAL 4 DAY, NULL, NULL, NOW() - INTERVAL 3 DAY, 'Usuario no autorizado', 1, TRUE),

-- Movimiento 9: Devolución procesada
(4, 9, 9, 5, NOW() - INTERVAL 6 DAY, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY, NULL, 2, TRUE),

-- Movimiento 10: Prestado
(2, 10, 10, 1, NOW() - INTERVAL 1 DAY, NOW(), NULL, NULL, NULL, 1, TRUE);
