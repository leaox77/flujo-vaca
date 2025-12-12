CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('empleado', 'supervisor', 'rrhh') NOT NULL,
    supervisor_id INT NULL
);

CREATE TABLE flujo (
    codflujo VARCHAR(10),
    codproceso VARCHAR(10),
    cod_procesosiguiente VARCHAR(10),
    rol VARCHAR(50),
    pantalla VARCHAR(100)
);

INSERT INTO flujo VALUES
('VAC', 'P1', 'P4', 'empleado', 'solicitud'),  -- Solicitud va directo a notificación inicial
('VAC', 'P2', 'P3', 'supervisor', 'revision_supervisor'),
('VAC', 'P3', 'P6', 'rrhh', 'verificacion_rrhh'),  -- RRHH va a P6 (notificación RRHH)
('VAC', 'P4', 'P2', 'system', 'notificacion_inicial'),  -- Notificación inicial va a supervisor
('VAC', 'P5', NULL, 'empleado', 'notificacion_final'),  -- Notificación final al empleado
('VAC', 'P6', 'P5', 'system', 'procesar_rrhh');  -- Procesamiento interno RRHH

CREATE TABLE seguimiento (
    nrotramite INT AUTO_INCREMENT PRIMARY KEY,
    flujo VARCHAR(10),
    proceso VARCHAR(10),
    fechainicio DATETIME,
    fechafin DATETIME,
    usuario VARCHAR(50),
    estado VARCHAR(20) DEFAULT 'pendiente'
);

-- Agregar campo para datos específicos de vacaciones
ALTER TABLE seguimiento 
ADD COLUMN datos TEXT NULL;

CREATE TABLE vacaciones (
    id INT PRIMARY KEY,
    empleado_id INT NOT NULL,
    supervisor_id INT NULL,
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
    FOREIGN KEY (supervisor_id) REFERENCES usuarios(id)
);
