DROP DATABASE IF EXISTS db_sistema_web;

CREATE DATABASE db_sistema_web
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_sistema_web;

-- -------------------------------------------------------------
--  Tabla principal: estudiantes
-- -------------------------------------------------------------
CREATE TABLE estudiantes (
    id_estudiante        INT UNSIGNED      NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria autoincremental',
    cedula               CHAR(10)          NOT NULL                COMMENT 'Cédula ecuatoriana (10 dígitos)',
    nombres              VARCHAR(80)       NOT NULL,
    apellidos            VARCHAR(80)       NOT NULL,
    correo               VARCHAR(120)      NOT NULL,
    telefono             VARCHAR(10)       NOT NULL                COMMENT 'Celular 09XXXXXXXX',
    fecha_nacimiento     DATE              NOT NULL,
    carrera              VARCHAR(100)      NOT NULL,
    semestre             TINYINT UNSIGNED  NOT NULL                COMMENT '1 a 10',
    estado               ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    -- Campos de auditoría
    fecha_creacion       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion  DATETIME          NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    ip_registro          VARCHAR(45)       NULL                    COMMENT 'IPv4/IPv6 del cliente que registró',

    CONSTRAINT pk_estudiantes        PRIMARY KEY (id_estudiante),
    CONSTRAINT uq_estudiantes_cedula UNIQUE (cedula),
    CONSTRAINT uq_estudiantes_correo UNIQUE (correo),
    CONSTRAINT ck_estudiantes_semestre CHECK (semestre BETWEEN 1 AND 10)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabla principal del sistema web de registro de estudiantes';

CREATE INDEX idx_estudiantes_apellidos ON estudiantes (apellidos);
CREATE INDEX idx_estudiantes_carrera   ON estudiantes (carrera);
