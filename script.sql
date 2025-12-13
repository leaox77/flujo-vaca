-- Crear base y usarla
CREATE DATABASE IF NOT EXISTS flujo_vaca CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE flujo_vaca;

-- Limpiar en orden de dependencias
DROP TABLE IF EXISTS seguimiento;
DROP TABLE IF EXISTS vacaciones;
DROP TABLE IF EXISTS flujo;
DROP TABLE IF EXISTS usuarios;

-- Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('empleado', 'supervisor', 'rrhh') NOT NULL,
    supervisor_id INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Flujo (mapa de pasos)
CREATE TABLE flujo (
    codflujo VARCHAR(10),
    codproceso VARCHAR(10),
    cod_procesosiguiente VARCHAR(10),
    rol VARCHAR(50),
    pantalla VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO flujo VALUES
('VAC', 'P1', 'P4', 'empleado', 'solicitud'),
('VAC', 'P2', 'P3', 'supervisor', 'revision_supervisor'),
('VAC', 'P3', 'P6', 'rrhh', 'verificacion_rrhh'),
('VAC', 'P4', 'P2', 'system', 'notificacion_inicial'),
('VAC', 'P5', NULL, 'empleado', 'notificacion_final'),
('VAC', 'P6', 'P5', 'system', 'procesar_rrhh');

-- Seguimiento (un registro por paso recorrido)
CREATE TABLE seguimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nrotramite INT NOT NULL,
    flujo VARCHAR(10),
    proceso VARCHAR(10),
    fechainicio DATETIME,
    fechafin DATETIME,
    usuario VARCHAR(50),
    estado VARCHAR(20) DEFAULT 'pendiente',
    datos TEXT NULL,
    INDEX idx_nrotramite (nrotramite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vacaciones
CREATE TABLE vacaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    supervisor_id INT NULL,
    rrhh_id INT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    dias_solicitados INT NOT NULL,
    motivo TEXT NULL,
    estado ENUM('pendiente', 'aprobado_supervisor', 'rechazado_supervisor',
                'aprobado_rrhh', 'rechazado_rrhh', 'finalizado') DEFAULT 'pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo_rechazo TEXT NULL,
    dias_disponibles INT DEFAULT 30,
    dias_descontar INT NULL,
    fecha_aprobacion_supervisor DATETIME NULL,
    fecha_aprobacion_rrhh DATETIME NULL,
    comentarios_supervisor TEXT NULL,
    comentarios_rrhh TEXT NULL,
    FOREIGN KEY (empleado_id) REFERENCES usuarios(id),
    FOREIGN KEY (supervisor_id) REFERENCES usuarios(id),
    FOREIGN KEY (rrhh_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seeds de usuarios (todas las contraseñas: 123456)
INSERT INTO usuarios (id, usuario, password, nombre, rol, supervisor_id) VALUES
(1, 'empleado1',  '123456', 'Juan Perez',        'empleado', 2),
(2, 'supervisor1','123456', 'Maria Gomez',       'supervisor', NULL),
(3, 'rrhh1',      '123456', 'Carlos Lopez',      'rrhh', NULL),
(4, 'empleado2',  '123456', 'Ana Torres',        'empleado', 2),
(5, 'empleado3',  '123456', 'Pedro Salas',       'empleado', 2),
(6, 'supervisor2','123456', 'Laura Rios',        'supervisor', NULL),
(7, 'empleado4',  '123456', 'Diego Castro',      'empleado', 6),
(8, 'empleado5',  '123456', 'Sofia Medina',      'empleado', 6),
(9, 'rrhh2',      '123456', 'Andrea Silva',      'rrhh', NULL),
(10,'empleado6',  '123456', 'Ricardo Flores',    'empleado', 2),
(11,'empleado7',  '123456', 'Karen Ortiz',       'empleado', 6),
(12,'empleado8',  '123456', 'Luis Herrera',      'empleado', 2),
(13,'empleado9',  '123456', 'Marta Reyes',       'empleado', 2),
(14,'empleado10', '123456', 'Javier Soto',       'empleado', 6);

-- Deja el AUTO_INCREMENT listo para nuevos usuarios
ALTER TABLE usuarios AUTO_INCREMENT = 15;
