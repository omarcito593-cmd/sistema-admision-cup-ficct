-- ==========================================
-- BASE DE DATOS: bd_fitcct_postulantes
-- Sistema Web de Admision Universitaria FICCT
-- ==========================================

DROP TABLE IF EXISTS reportes CASCADE;
DROP TABLE IF EXISTS notas CASCADE;
DROP TABLE IF EXISTS asignaciones CASCADE;
DROP TABLE IF EXISTS postulante_grupo CASCADE;
DROP TABLE IF EXISTS grupos CASCADE;
DROP TABLE IF EXISTS postulantes CASCADE;
DROP TABLE IF EXISTS docentes CASCADE;
DROP TABLE IF EXISTS materias CASCADE;
DROP TABLE IF EXISTS aulas CASCADE;
DROP TABLE IF EXISTS carreras CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

-- =========================
-- TABLAS PRINCIPALES
-- =========================

CREATE TABLE roles (
    id_rol SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
);

CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Activo',
    id_rol INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

CREATE TABLE carreras (
    id_carrera SERIAL PRIMARY KEY,
    nombre_carrera VARCHAR(100) NOT NULL,
    sigla VARCHAR(10),
    cupo_maximo INT DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'Activo'
);

CREATE TABLE postulantes (
    id_postulante SERIAL PRIMARY KEY,
    ci VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo VARCHAR(20) NOT NULL,
    direccion VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    colegio_procedencia VARCHAR(150) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    titulo_bachiller VARCHAR(20) NOT NULL,
    otros TEXT,
    estado VARCHAR(30) DEFAULT 'Postulante',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_carrera INT NOT NULL,
    id_carrera_segunda_opcion INT,
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera),
    FOREIGN KEY (id_carrera_segunda_opcion) REFERENCES carreras(id_carrera)
);

CREATE TABLE aulas (
    id_aula SERIAL PRIMARY KEY,
    nombre_aula VARCHAR(50) NOT NULL,
    capacidad INT NOT NULL DEFAULT 70,
    estado VARCHAR(20) DEFAULT 'Activo'
);

CREATE TABLE grupos (
    id_grupo SERIAL PRIMARY KEY,
    nombre_grupo VARCHAR(50) NOT NULL,
    turno VARCHAR(20) NOT NULL,
    cupo_maximo INT NOT NULL DEFAULT 70,
    estado VARCHAR(20) DEFAULT 'Activo',
    id_aula INT NOT NULL,
    FOREIGN KEY (id_aula) REFERENCES aulas(id_aula)
);

CREATE TABLE materias (
    id_materia SERIAL PRIMARY KEY,
    nombre_materia VARCHAR(100) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Activo'
);

CREATE TABLE docentes (
    id_docente SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    ci VARCHAR(20) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    profesion VARCHAR(100),
    maestria VARCHAR(20),
    diplomado VARCHAR(20),
    estado VARCHAR(20) DEFAULT 'Activo'
);

CREATE TABLE postulante_grupo (
    id_postulante_grupo SERIAL PRIMARY KEY,
    id_postulante INT NOT NULL,
    id_grupo INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_postulante) REFERENCES postulantes(id_postulante),
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo)
);

CREATE TABLE asignaciones (
    id_asignacion SERIAL PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_materia INT NOT NULL,
    id_docente INT NOT NULL,
    horario VARCHAR(50),
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo),
    FOREIGN KEY (id_materia) REFERENCES materias(id_materia),
    FOREIGN KEY (id_docente) REFERENCES docentes(id_docente)
);

CREATE TABLE notas (
    id_nota SERIAL PRIMARY KEY,
    id_postulante INT NOT NULL,
    id_materia INT NOT NULL,
    examen1 NUMERIC(5,2) NOT NULL DEFAULT 0,
    examen2 NUMERIC(5,2) NOT NULL DEFAULT 0,
    examen3 NUMERIC(5,2) NOT NULL DEFAULT 0,
    promedio_final NUMERIC(5,2),
    resultado VARCHAR(20),
    FOREIGN KEY (id_postulante) REFERENCES postulantes(id_postulante),
    FOREIGN KEY (id_materia) REFERENCES materias(id_materia)
);

CREATE TABLE reportes (
    id_reporte SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_reporte VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- =========================
-- TRIGGERS
-- =========================

CREATE OR REPLACE FUNCTION calcular_promedio_resultado()
RETURNS TRIGGER AS $$
BEGIN
    NEW.promedio_final := ROUND(
        ((NEW.examen1 * 0.30) + (NEW.examen2 * 0.30) + (NEW.examen3 * 0.40)), 
        2
    );

    IF NEW.promedio_final >= 60 THEN
        NEW.resultado := 'APROBADO';
    ELSE
        NEW.resultado := 'REPROBADO';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_calcular_promedio_resultado
BEFORE INSERT OR UPDATE ON notas
FOR EACH ROW
EXECUTE FUNCTION calcular_promedio_resultado();


CREATE OR REPLACE FUNCTION validar_rango_notas()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.examen1 < 0 OR NEW.examen1 > 100 THEN
        RAISE EXCEPTION 'La nota del examen 1 debe estar entre 0 y 100';
    END IF;

    IF NEW.examen2 < 0 OR NEW.examen2 > 100 THEN
        RAISE EXCEPTION 'La nota del examen 2 debe estar entre 0 y 100';
    END IF;

    IF NEW.examen3 < 0 OR NEW.examen3 > 100 THEN
        RAISE EXCEPTION 'La nota del examen 3 debe estar entre 0 y 100';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validar_rango_notas
BEFORE INSERT OR UPDATE ON notas
FOR EACH ROW
EXECUTE FUNCTION validar_rango_notas();


CREATE OR REPLACE FUNCTION validar_cupo_grupo()
RETURNS TRIGGER AS $$
DECLARE
    total_estudiantes INT;
    cupo INT;
BEGIN
    SELECT COUNT(*) INTO total_estudiantes
    FROM postulante_grupo
    WHERE id_grupo = NEW.id_grupo;

    SELECT cupo_maximo INTO cupo
    FROM grupos
    WHERE id_grupo = NEW.id_grupo;

    IF total_estudiantes >= cupo THEN
        RAISE EXCEPTION 'El grupo ya alcanzó el cupo máximo permitido';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validar_cupo_grupo
BEFORE INSERT ON postulante_grupo
FOR EACH ROW
EXECUTE FUNCTION validar_cupo_grupo();

-- =========================
-- DATOS INICIALES
-- =========================

INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Usuario con acceso completo al sistema'),
('Secretaria', 'Usuario encargado del registro de postulantes'),
('Docente', 'Usuario encargado de revisar notas y grupos');

INSERT INTO usuarios (nombre, usuario, contrasena, estado, id_rol) VALUES
('Administrador del Sistema', 'admin', '123456', 'Activo', 1),
('Secretaria Académica', 'secretaria', '123456', 'Activo', 2),
('Docente de Prueba', 'docente', '123456', 'Activo', 3);

INSERT INTO carreras (nombre_carrera, sigla, cupo_maximo, estado) VALUES
('Ingeniería de Sistemas', 'SIS', 120, 'Activo'),
('Ingeniería Informática', 'INF', 100, 'Activo'),
('Ingeniería en Redes y Telecomunicaciones', 'RYT', 80, 'Activo'),
('Ingeniería en Ciencias de la Computación', 'ICC', 80, 'Activo');

INSERT INTO aulas (nombre_aula, capacidad, estado) VALUES
('Aula 1', 70, 'Activo'),
('Aula 2', 70, 'Activo'),
('Aula 3', 70, 'Activo'),
('Aula 4', 70, 'Activo');

INSERT INTO materias (nombre_materia, estado) VALUES
('Computación', 'Activo'),
('Matemática', 'Activo'),
('Física', 'Activo'),
('Inglés', 'Activo');

INSERT INTO docentes 
(nombre, apellido, ci, telefono, correo, profesion, maestria, diplomado, estado) 
VALUES
('Juan', 'Pérez', '1234567', '70000001', 'juan.perez@fitcct.edu.bo', 'Ingeniero de Sistemas', 'Si', 'Si', 'Activo'),
('María', 'Gómez', '7654321', '70000002', 'maria.gomez@fitcct.edu.bo', 'Licenciada en Matemáticas', 'Si', 'Si', 'Activo'),
('Carlos', 'Rojas', '4567891', '70000003', 'carlos.rojas@fitcct.edu.bo', 'Ingeniero Informático', 'Si', 'Si', 'Activo');

INSERT INTO grupos (nombre_grupo, turno, id_aula, cupo_maximo, estado) VALUES
('Grupo A', 'Mañana', 1, 70, 'Activo'),
('Grupo B', 'Tarde', 2, 70, 'Activo'),
('Grupo C', 'Noche', 3, 70, 'Activo');

INSERT INTO postulantes
(ci, nombre, apellido, fecha_nacimiento, sexo, direccion, telefono, correo, colegio_procedencia, ciudad, titulo_bachiller, otros, estado, id_carrera, id_carrera_segunda_opcion)
VALUES
('1001001', 'Ana', 'Rojas', '2006-04-12', 'Femenino', 'Av. Santos Dumont', '70011100', 'ana.rojas@gmail.com', 'Colegio Nacional Bolivia', 'Santa Cruz', 'Si', 'Presentó fotocopia de CI', 'Postulante', 1, 2),
('1001002', 'Luis', 'Vargas', '2005-09-20', 'Masculino', 'Av. Virgen de Cotoca', '70022200', 'luis.vargas@gmail.com', 'Colegio La Salle', 'Santa Cruz', 'Si', 'Documentos completos', 'Postulante', 2, 1),
('1001003', 'Carla', 'Suárez', '2006-01-15', 'Femenino', 'Barrio Equipetrol', '70033300', 'carla.suarez@gmail.com', 'Colegio Alemán', 'Santa Cruz', 'Si', 'Pendiente certificado adicional', 'Postulante', 1, 3);

INSERT INTO postulante_grupo (id_postulante, id_grupo) VALUES
(1, 1),
(2, 1),
(3, 2);

INSERT INTO asignaciones (id_grupo, id_materia, id_docente, horario) VALUES
(1, 1, 1, 'Lunes 08:00 - 10:00'),
(1, 2, 2, 'Martes 08:00 - 10:00'),
(1, 3, 3, 'Miércoles 08:00 - 10:00'),
(2, 4, 1, 'Jueves 14:00 - 16:00');

INSERT INTO notas (id_postulante, id_materia, examen1, examen2, examen3) VALUES
(1, 1, 70, 80, 75),
(1, 2, 65, 60, 70),
(2, 1, 50, 55, 58),
(3, 3, 80, 85, 90);

INSERT INTO reportes (id_usuario, tipo_reporte, descripcion) VALUES
(1, 'Lista general de postulantes', 'Reporte de todos los postulantes registrados'),
(1, 'Postulantes aprobados', 'Reporte de postulantes con promedio mayor o igual a 60'),
(1, 'Postulantes reprobados', 'Reporte de postulantes con promedio menor a 60');